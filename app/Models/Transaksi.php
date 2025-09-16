<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'TRANSAKSI';

    protected $primaryKey = 'TRANS_NO';

    public $timestamps = false; // karena tabel tidak punya created_at & updated_at

    public $incrementing = false; // kalau bukan auto increment

    protected $connection = 'sqlsrv';

    protected $keyType = 'string';

    protected $fillable = [
        'TRANS_NO',
        'TRANS_DATE',
        'TRANS_TOTAL_TRANSACTION',
        'TRANS_TOTAL_DISC_GMC',
        'TRANS_DISC_GMC_PERSEN',
        'TRANS_DISC_GMC_NOMINAL',
        'TRANS_TOTAL_BAYAR',
        'TRANS_PEMBULAT',
        'TRANS_BAYAR_CASH',
        'TRANS_BAYAR_CARD',
        'TRANS_IS_ACTIVE',
        'DATE_CREATE',
        'DATE_MODIFY',
        'TRANS_IS_PENDING',
        'TRANS_TOTAL_PPN',
        'TRANS_IS_JURNAL',
        'TRANS_DISC_CARD',
        'TRANSAKSI_ID',
        'AUT$UNIT_ID',
        'BEGINNING_BALANCE_ID',
        'OP_CREATE',
        'MEMBER_ID',
        'TRANS_KUPON_UNDIAN',
        'TRANS_POIN_PAS',
        'TRANS_GUDANG_ID',
        'trans_poin_member',
        'trans_kupon_member',
        'TRANS_SISA_GALON',
        'REFERENSI',
        'TRANS_ONGKIR',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'MEMBER_ID', 'MEMBER_ID');
    }
}
