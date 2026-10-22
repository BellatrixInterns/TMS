<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;
    protected $fillable = ['item_code', 'item_name', 'category_id', 'brand_id', 'uom_id', 'is_active'];

    public function category() { return $this->belongsTo(Category::class); }
    public function brand()    { return $this->belongsTo(Brand::class); }
    public function uom()      { return $this->belongsTo(Uom::class); }

    public function toApi(): array
    {
        return [
            'id'            => $this->id,
            'item_code'     => $this->item_code,
            'item_name'     => $this->item_name,
            'is_active'     => (int) $this->is_active,
            'category_id'   => $this->category_id,
            'category_name' => $this->category->name,
            'brand_id'      => $this->brand_id,
            'brand_name'    => $this->brand->name,
            'uom_id'        => $this->uom_id,
            'uom_name'      => $this->uom->name,
        ];
    }
}