<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_PEMBAYARAN'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'ID_PEMBAYARAN', 'id', 'ID_DETIL_KARTU',
        'NOMOR_MEJA', 'TANGGAL_PEMBAYARAN', 'NOMOR_PEMBAYARAN',
        'JENIS_PEMBAYARAN', 'KODE_VERIFIKASI', 'SUBTOTAL',
        'PAJAK', 'TOTAL'
    ];
}
