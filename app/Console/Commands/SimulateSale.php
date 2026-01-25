<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Account;
use App\Models\Productbooking;
use App\Models\ProductBookingItem;
use App\Models\ReceiptsVoucher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SimulateSale extends Command
{
    protected $signature = 'simulate:sale';
    protected $description = 'Simulate sale posting with customer balance and two receipts';

    public function handle()
    {
        DB::transaction(function() {
            // Create or get a test customer (use actual columns)
            $customer = Customer::firstOrCreate(
                ['customer_id' => 'SIM_TEST_CUSTOMER'],
                ['customer_name' => 'SIM TEST CUSTOMER', 'mobile' => '000', 'opening_balance' => 0]
            );

            // Ensure customer has a ledger with closing_balance 1000
            $last = CustomerLedger::where('customer_id', $customer->id)->latest('id')->first();
            if (! $last || ($last->closing_balance ?? 0) != 1000) {
                CustomerLedger::create([
                    'customer_id' => $customer->id,
                    'admin_or_user_id' => 1,
                    'previous_balance' => 0,
                    'opening_balance' => 0,
                    'closing_balance' => 1000,
                ]);
            }

            // Create a booking (1200 total_net)
            $booking = new Productbooking();
            $booking->invoice_no = 'SIM-INV-' . time();
            $booking->customer_id = $customer->id;
            $booking->sub_total2 = 1200;
            $booking->sub_total1 = 1200;
            $booking->discount_amount = 0;
            $booking->discount_percent = 0;
            $booking->previous_balance = 1000;
            $booking->total_balance = 2200; // previous + sale
            $booking->status = 'pending';
            $booking->save();

            // Create or get an AccountHead to attach test accounts
            $cashHead = \App\Models\AccountHead::firstOrCreate(['name' => 'Cash/Bank']);

            // Create four test accounts (if missing) to receive receipts
            $acc1 = Account::firstOrCreate(
                ['title' => 'SIM_ACC1'],
                ['head_id' => $cashHead->id, 'type' => 'debit', 'opening_balance' => 0, 'account_code' => 'SIM001']
            );
            $acc2 = Account::firstOrCreate(
                ['title' => 'SIM_ACC2'],
                ['head_id' => $cashHead->id, 'type' => 'debit', 'opening_balance' => 0, 'account_code' => 'SIM002']
            );
            $acc3 = Account::firstOrCreate(
                ['title' => 'SIM_ACC3'],
                ['head_id' => $cashHead->id, 'type' => 'debit', 'opening_balance' => 0, 'account_code' => 'SIM003']
            );
            $acc4 = Account::firstOrCreate(
                ['title' => 'SIM_ACC4'],
                ['head_id' => $cashHead->id, 'type' => 'debit', 'opening_balance' => 0, 'account_code' => 'SIM004']
            );

            // Zero these accounts now to run a clean test
            $acc1->opening_balance = 0; $acc1->save();
            $acc2->opening_balance = 0; $acc2->save();
            $acc3->opening_balance = 0; $acc3->save();
            $acc4->opening_balance = 0; $acc4->save();

            // Create a combined receipt with four rows of 300 each (total 1200)
            ReceiptsVoucher::create([
                'rvid' => ReceiptsVoucher::generateRVID(auth()->id() ?? null),
                'receipt_date' => Carbon::today(),
                'entry_date' => Carbon::now(),
                'type' => 'SALE_RECEIPT',
                'party_id' => $customer->id,
                'tel' => $booking->tel,
                'remarks' => 'SIM test - 4 accounts 300 each',
                'reference_no' => $booking->invoice_no,
                'row_account_head' => 'Cash/Bank',
                'row_account_id' => json_encode([$acc1->id, $acc2->id, $acc3->id, $acc4->id]),
                'amount' => json_encode([300,300,300,300]),
                'total_amount' => 1200,
                'processed' => false,
            ]);

            // Ensure an authenticated user exists for auth()->id() in controller
            $user = User::first();
            if (! $user) {
                $user = User::create(['name' => 'sim', 'email' => 'sim@example.com', 'password' => bcrypt('password')]);
            }
            Auth::loginUsingId($user->id);

            // Now call the ajaxPost logic via artisan tinker-like call: call controller method directly
            $controller = app()->make(\App\Http\Controllers\SaleController::class);
            $request = new \Illuminate\Http\Request();
            // provide a non-empty array for warehouse_id to satisfy validation
            $request->merge(['booking_id' => $booking->id, 'warehouse_id' => [1]]);

            $resp = $controller->ajaxPost($request);

            $this->info('Response: ' . json_encode($resp->getData()));

            // Show resulting balances
            $custLedger = CustomerLedger::where('customer_id', $customer->id)->latest('id')->first();
            $this->info('Customer closing_balance: ' . ($custLedger->closing_balance ?? 'n/a'));

            $acc1fresh = Account::find($acc1->id);
            $acc2fresh = Account::find($acc2->id);
            $acc3fresh = Account::find($acc3->id);
            $acc4fresh = Account::find($acc4->id);
            $this->info('Account 1 (' . $acc1fresh->title . ') opening_balance: ' . $acc1fresh->opening_balance);
            $this->info('Account 2 (' . $acc2fresh->title . ') opening_balance: ' . $acc2fresh->opening_balance);
            $this->info('Account 3 (' . $acc3fresh->title . ') opening_balance: ' . $acc3fresh->opening_balance);
            $this->info('Account 4 (' . $acc4fresh->title . ') opening_balance: ' . $acc4fresh->opening_balance);

            // Reset all four accounts to zero (clean state)
            $acc1fresh->opening_balance = 0; $acc1fresh->save();
            $acc2fresh->opening_balance = 0; $acc2fresh->save();
            $acc3fresh->opening_balance = 0; $acc3fresh->save();
            $acc4fresh->opening_balance = 0; $acc4fresh->save();

            $this->info('Reset balances to zero. Current values:');
            $this->info('Account 1 (' . $acc1fresh->title . ') opening_balance: ' . $acc1fresh->opening_balance);
            $this->info('Account 2 (' . $acc2fresh->title . ') opening_balance: ' . $acc2fresh->opening_balance);
            $this->info('Account 3 (' . $acc3fresh->title . ') opening_balance: ' . $acc3fresh->opening_balance);
            $this->info('Account 4 (' . $acc4fresh->title . ') opening_balance: ' . $acc4fresh->opening_balance);
        });
    }
}
