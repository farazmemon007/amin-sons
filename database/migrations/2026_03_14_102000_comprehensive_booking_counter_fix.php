<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations - Comprehensive booking counter + duplicate fix
     */
    public function up(): void
    {
        // Step 1: Fix duplicate invoice_no entries by keeping only the latest one per invoice_no
        $duplicateInvoices = DB::table('productbookings')
            ->select('invoice_no', DB::raw('COUNT(*) as cnt'))
            ->groupBy('invoice_no')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->pluck('invoice_no');
        
        if($duplicateInvoices->count() > 0) {
            Log::warning('Found duplicate invoice_no entries', ['count' => $duplicateInvoices->count(), 'invoices' => $duplicateInvoices->all()]);
            
            foreach($duplicateInvoices as $invoice_no) {
                // Keep the latest one, delete the others
                $records = DB::table('productbookings')
                    ->where('invoice_no', $invoice_no)
                    ->orderBy('id', 'desc')
                    ->get();
                
                if($records->count() > 1) {
                    // Keep the first (latest), delete the rest
                    $latestId = $records[0]->id;
                    DB::table('productbookings')
                        ->where('invoice_no', $invoice_no)
                        ->where('id', '!=', $latestId)
                        ->delete();
                    
                    Log::info('Resolved duplicate invoice_no', ['invoice_no' => $invoice_no, 'kept_id' => $latestId, 'deleted_count' => $records->count() - 1]);
                }
            }
        }
        
        // Step 2: Rebuild booking counters based on actual data per branch
        $branches = DB::table('branches')->get();
        
        foreach($branches as $branch) {
            // Find the maximum BINV number for this branch
            $maxBinvRecord = DB::table('productbookings')
                ->where('branch_id', $branch->id)
                ->where('invoice_no', 'like', 'BINV-%')
                ->orderBy('id', 'desc')
                ->first();
            
            if($maxBinvRecord) {
                // Extract number from BINV-0001 format
                if(preg_match('/BINV-(\d+)/', $maxBinvRecord->invoice_no, $matches)) {
                    $maxNum = (int)$matches[1];
                    DB::table('branches')
                        ->where('id', $branch->id)
                        ->update(['booking_counter' => $maxNum]);
                    
                    Log::info('Synced booking_counter for branch', ['branch_id' => $branch->id, 'max_binv' => $maxBinvRecord->invoice_no, 'counter' => $maxNum]);
                }
            } else {
                // No BINV records for this branch, set counter to 0
                DB::table('branches')
                    ->where('id', $branch->id)
                    ->update(['booking_counter' => 0]);
                
                Log::info('No BINV records for branch, set counter to 0', ['branch_id' => $branch->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all counters to 0
        DB::table('branches')->update(['booking_counter' => 0]);
        Log::info('Reset all booking_counter values to 0');
    }
};
