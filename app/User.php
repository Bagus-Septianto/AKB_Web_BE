<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'karyawan'; //model ini pake tabel karyawan
    //protected $primaryKey = 'ID_KARYAWAN'; //TODO idk
    //protected $primaryKey = 'id'; //TODO idk
    public $timestamps = false; //timestamp disable kalo pake tabel karyawan, kalo user dicomment aja ini

    protected $fillable = [
        'NAMA_KARYAWAN', 'NO_TELP_KARYAWAN', 'JENIS_KELAMIN_KARYAWAN', 
        'TANGGAL_LAHIR_KARYAWAN', 'EMAIL_KARYAWAN',
        'TANGGAL_MASUK_KARYAWAN', 'TANGGAL_KELUAR_KARYAWAN', 'ID_ROLE',
        'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function AauthAccessToken(){
        return $this->hasMany('\App\OauthAccessToken');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    // public function getCreateAtAttribute() {
    //     if(!is_null($this->attributes['created_at'])){
    //         return Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
    //     }
    // } //convert atribut created_at ke Y-m-d H:i:s

    // public function getUpdatedAtAttribute() {
    //     if(!is_null($this->attributes['updated_at'])){
    //         return Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
    //     }
    // } //convert atribut updated_at ke Y-m-d H:i:s
}
