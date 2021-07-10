<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'ID_ROLE';

    protected $fillable = [
        'NAMA_ROLE'
    ];
}
