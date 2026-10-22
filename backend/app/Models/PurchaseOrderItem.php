<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['purchase_order_id', 'item_id', 'quantity', 'rate', 'amount'];

    public function item() { return $this->belongsTo(Item::class); }
}