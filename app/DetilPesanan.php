<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DetilPesanan extends Model
{
    protected $table = 'detil_pesanan'; //model ini pake tabel detil_pesanan
    protected $primaryKey = 'ID_DETIL_PESANAN';
    public $timestamps = false;
    protected $fillable = [
        'ID_DETIL_PESANAN', 'ID_PESANAN', 'ID_MENU',
        'JUMLAH_PESANAN', 'HARGA_PESANAN', 'STATUS_PESANAN'
    ];
}
