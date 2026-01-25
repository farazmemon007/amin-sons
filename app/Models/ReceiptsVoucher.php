<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptsVoucher extends Model
{
    use HasFactory;

    // 🔥 EXACT TABLE NAME (MUST MATCH DB)
    protected $table = 'receipts_vouchers';

    protected $guarded = [];

    protected $casts = [
        'processed' => 'boolean',
    ];

    protected $fillable = [
        'rvid', 'receipt_date', 'entry_date', 'type', 'party_id', 'tel', 'remarks', 'reference_no', 'booking_id', 'row_account_head', 'row_account_id', 'amount', 'total_amount', 'processed'
    ];

    /* ===========================
       GENERATE RVID (CORRECT)
    =========================== */
    public static function generateRVID()
    {
        // Default generator: include optional user id to make RVIDs unique per user
        $args = func_get_args();
        $userId = $args[0] ?? null;

        $uidPart = $userId ? (int)$userId : 0;
        $prefix = 'rvid00' . $uidPart . '-';

        // Use the last numeric id as sequence to avoid parsing mixed-format rvids
        $lastId = (int) self::max('id');
        $seq = $lastId + 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /* ===========================
       RELATIONS
    =========================== */

    public function Vendor()
    {
        return $this->belongsTo(Vendor::class, 'party_id', 'id');
    }

    public function AccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'row_account_head', 'id');
    }

    public function Account()
    {
        return $this->belongsTo(Account::class, 'row_account_id', 'id');
    }
}
