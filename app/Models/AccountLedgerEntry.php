<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLedgerEntry extends Model
{
    use HasFactory;

    protected $table = 'account_ledger_entries';

    protected $fillable = [
        'account_id',
        'branch_id',
        'voucher_type',
        'voucher_no',
        'voucher_id',
        'entry_no',
        'transaction_date',
        'description',
        'debit',
        'credit',
        'running_balance',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit'           => 'float',
        'credit'          => 'float',
        'running_balance' => 'float',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate next entry_no for an account.
     * Series logic:
     *   - Account head name contains "Bank"  → BR-1, BR-2 ...
     *   - Account head name contains "Cash"  → CR-1, CR-2 ...
     *   - Opening balance entry              → OB-1
     *   - Everything else                   → JV-1, JV-2 ...
     */
    public static function generateEntryNo(int $accountId, string $voucherType = 'manual'): string
    {
        // Get the account with its head
        $account = Account::with('head')->find($accountId);
        $headName = strtolower($account?->head?->name ?? '');

        // Determine prefix
        if ($voucherType === 'opening_balance') {
            $prefix = 'OB';
        } elseif (str_contains($headName, 'bank')) {
            $prefix = 'BR';
        } elseif (str_contains($headName, 'cash')) {
            $prefix = 'CR';
        } else {
            $prefix = 'JV';
        }

        // Get last sequential number for this account with this prefix
        $lastEntry = self::where('account_id', $accountId)
            ->where('entry_no', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->value('entry_no');

        $lastNum = 0;
        if ($lastEntry) {
            $parts = explode('-', $lastEntry);
            $lastNum = (int) end($parts);
        }

        return $prefix . '-' . ($lastNum + 1);
    }
}
