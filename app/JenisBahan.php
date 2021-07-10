<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JenisBahan extends Model
{
    protected $table = 'bahan'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_BAHAN'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'ID_BAHAN', 'NAMA_BAHAN', 'JUMLAH_BAHAN',
        'UNIT_BAHAN', 'PERHITUNGAN', 'SERVING_SIZE'
    ];
}
