<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    protected $table = 'reservasi'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_RESERVASI'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'ID_RESERVASI', 'ID_MEJA', 'ID_CUSTOMER', 'ID_PESANAN',
        'TANGGAL_RESERVASI', 'JAM_RESERVASI', 'STATUS_RESERVASI'
    ];
}
