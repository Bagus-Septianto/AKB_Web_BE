<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_CUSTOMER'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'NAMA_CUSTOMER', 'JENIS_KELAMIN_CUSTOMER',
        'NO_TELP_CUSTOMER', 'EMAIL_CUSTOMER'
    ];
}
