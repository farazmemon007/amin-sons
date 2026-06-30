<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'journal_vouchers';

    protected $guarded = [];

    protected $casts = [
        'voucher_date' => 'date',
        'entry_date'   => 'date',
        'amount'       => 'decimal:2',
    ];

    /* =========================================================
       GENERATE JVID
    ========================================================= */
    public static function generateJVID(): string
    {
        $lastId = (int) self::withTrashed()->max('id');
        $seq    = $lastId + 1;
        return 'JVID-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /* =========================================================
       RELATIONS — Debit Party
    ========================================================= */
    public function debitVendor()
    {
        return $this->belongsTo(Vendor::class, 'debit_party_id');
    }

    public function debitCustomer()
    {
        return $this->belongsTo(Customer::class, 'debit_party_id');
    }

    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_party_id');
    }

    /* =========================================================
       RELATIONS — Credit Party
    ========================================================= */
    public function creditVendor()
    {
        return $this->belongsTo(Vendor::class, 'credit_party_id');
    }

    public function creditCustomer()
    {
        return $this->belongsTo(Customer::class, 'credit_party_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_party_id');
    }

    /* =========================================================
       RELATIONS — Meta
    ========================================================= */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* =========================================================
       HELPERS
    ========================================================= */

    /**
     * Resolve a human-readable party name from type+id.
     */
    public static function resolvePartyName(string $type, int|string|null $id): string
    {
        if (!$id) return '—';

        return match ($type) {
            'vendor'   => Vendor::find($id)?->name ?? '—',
            'customer' => Customer::find($id)?->customer_name ?? '—',
            'account'  => Account::find($id)?->title ?? '—',
            default    => '—',
        };
    }
}
