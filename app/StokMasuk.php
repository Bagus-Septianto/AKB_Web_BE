<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StokMasuk extends Model
{
    protected $table = 'stok_masuk'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_STOK_MASUK'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'ID_STOK_MASUK', 'ID_BAHAN', 'JUMLAH_STOK_MASUK',
        'TANGGAL_STOK_MASUK', 'HARGA_STOK_MASUK'
    ];
}
