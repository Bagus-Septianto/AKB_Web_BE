<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pesanan extends Model
{
    protected $table = 'pesanan'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_PESANAN'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'ID_PESANAN', 'ID_PEMBAYARAN', 'ID_RESERVASI',
        'TOTAL_MENU', 'TOTAL_ITEM', 'TANGGAL_PESANAN'
    ];
}
