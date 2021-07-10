<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Meja extends Model
{
    protected $table = 'meja'; //model ini pake tabel karyawan
    protected $primaryKey = 'ID_MEJA'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini
    protected $fillable = [
        'NOMOR_MEJA', 'STATUS_MEJA'
    ];

    // public function getCreatedAtAtrribute() {
    //     if(!is_null($this->attributes['created_at'])){
    //         return Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
    //     }
    // } //convert attribut created_at ke format Y-m-d H:i:s

    // public function getUpdatedAtAtrribute() {
    //     if(!is_null($this->attributes['updated_at'])){
    //         return Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
    //     }
    // } //convert attribut created_at ke format Y-m-d H:i:s
}
