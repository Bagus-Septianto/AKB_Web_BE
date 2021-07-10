<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StokKeluar extends Model
{
    protected $table = 'stok_keluar'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_STOK_KELUAR'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'ID_STOK_KELUAR', 'ID_BAHAN', 'JUMLAH_STOK_KELUAR',
        'TANGGAL_STOK_KELUAR', 'STATUS_STOK_DIBUANG'
    ];
}
