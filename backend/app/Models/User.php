<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';
    public $timestamps = false;
    protected $fillable = ['username', 'password_hash', 'full_name'];
    protected $hidden = ['password_hash'];
}