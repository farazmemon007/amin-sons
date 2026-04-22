<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Fix booking counters based on existing BINV records
     */
    public function up(): void
    {
        // For each branch, find the highest BINV number and set counter accordingly
        $branches = DB::table('branches')->get();
        
        foreach($branches as $branch) {
            // Get all BINV records for this branch
            $binvRecords = DB::table('productbookings')
                ->where('branch_id', $branch->id)
                ->where('invoice_no', 'like', 'BINV-%')
                ->orderBy('id', 'desc')
                ->first();
            
            if($binvRecords) {
                // Extract the number from BINV-0001 format
                $invoiceNo = $binvRecords->invoice_no;
                if(preg_match('/BINV-(\d+)/', $invoiceNo, $matches)) {
                    $currentNum = (int)$matches[1];
                    // Set counter to this number so next one will be currentNum + 1
                    DB::table('branches')->where('id', $branch->id)->update(['booking_counter' => $currentNum]);
                }
            } else {
                // No BINV records yet, set to 0
                DB::table('branches')->where('id', $branch->id)->update(['booking_counter' => 0]);
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
    }
};
