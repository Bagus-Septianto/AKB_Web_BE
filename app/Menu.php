<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Menu extends Model
{
    protected $table = 'menu'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_MENU'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'ID_MENU', 'ID_BAHAN', 'NAMA_MENU', 'JENIS_MENU', 
        'DESKRIPSI_MENU', 'UNIT_MENU', 'HARGA_MENU', 'gambar'
    ];
}
