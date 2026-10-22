<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];
}