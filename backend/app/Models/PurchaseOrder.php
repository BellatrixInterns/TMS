<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    public $timestamps = false;
    protected $fillable = ['po_number', 'supplier_id', 'order_date', 'location_id', 'deliver_to', 'remarks', 'total_amount'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function items()    { return $this->hasMany(PurchaseOrderItem::class); }

    public function toApi(): array
    {
        return [
            'id'            => $this->id,
            'po_number'     => $this->po_number,
            'order_date'    => $this->order_date,
            'deliver_to'    => $this->deliver_to,
            'total_amount'  => $this->total_amount,
            'supplier_name' => $this->supplier->name,
            'location_name' => $this->location->name,
            'items'         => $this->items->map(fn ($l) => [
                'item_code' => $l->item->item_code,
                'item_name' => $l->item->item_name,
                'uom_name'  => $l->item->uom->name,
                'quantity'  => $l->quantity,
                'rate'      => $l->rate,
                'amount'    => $l->amount,
            ])->values(),
        ];
    }
}