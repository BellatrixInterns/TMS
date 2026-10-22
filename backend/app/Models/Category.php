<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'item_categories';
    public $timestamps = false;
    protected $fillable = ['name', 'is_active'];
}