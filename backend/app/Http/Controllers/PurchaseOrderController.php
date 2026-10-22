<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    private const WITH = ['supplier', 'location', 'items.item.uom'];

    // Indian financial year (Apr to Mar): Oct 2026 -> "26-27", Feb 2027 -> "26-27"
    private function fiscalYearLabel(string $date): string
    {
        $d = Carbon::parse($date);
        $start = $d->month >= 4 ? $d->year : $d->year - 1;
        return sprintf('%02d-%02d', $start % 100, ($start + 1) % 100);
    }

    public function index()
    {
        $orders = PurchaseOrder::with(self::WITH)->orderByDesc('id')->get();
        return response()->json(['success' => true, 'data' => $orders->map(fn ($o) => $o->toApi())->values()]);
    }

    public function store(Request $request)
    {
        $data = $this->validate422($request, [
            'supplier_id'      => ['required', Rule::exists('suppliers', 'id')->where('is_active', 1)],
            'location_id'      => ['required', Rule::exists('locations', 'id')->where('is_active', 1)],
            'order_date'       => 'required|date_format:Y-m-d',
            'deliver_to'       => 'required|string|max:255',
            'remarks'          => 'nullable|string|max:255',
            'items'            => 'required|array|min:1',
            'items.*.item_id'  => ['required', Rule::exists('items', 'id')->where('is_active', 1)],
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.rate'     => 'required|numeric|min:0',
        ], [
            'supplier_id.required'      => 'Please select a Supplier.',
            'supplier_id.exists'        => 'Selected Supplier does not exist or is inactive.',
            'location_id.required'      => 'Please select a Location.',
            'location_id.exists'        => 'Selected Location does not exist or is inactive.',
            'order_date.required'       => 'Order Date is required.',
            'deliver_to.required'       => 'Deliver To is required.',
            'items.required'            => 'Add at least one item to the order.',
            'items.min'                 => 'Add at least one item to the order.',
            'items.*.item_id.required'  => 'Invalid item in order line.',
            'items.*.item_id.exists'    => 'Selected item does not exist or is inactive.',
            'items.*.quantity.required' => 'Quantity must be greater than 0 for every item.',
            'items.*.quantity.gt'       => 'Quantity must be greater than 0 for every item.',
            'items.*.rate.required'     => 'Rate must be 0 or more for every item.',
            'items.*.rate.min'          => 'Rate must be 0 or more for every item.',
        ]);

        // Amounts are calculated on the server, never trusted from the browser.
        $lines = [];
        $total = 0;
        foreach ($data['items'] as $line) {
            $amount = round($line['quantity'] * $line['rate'], 2);
            $total += $amount;
            $lines[] = [
                'item_id'  => $line['item_id'],
                'quantity' => $line['quantity'],
                'rate'     => $line['rate'],
                'amount'   => $amount,
            ];
        }

        $id = DB::transaction(function () use ($data, $lines, $total) {
            $po = PurchaseOrder::create([
                'po_number'    => 'TMP-' . Str::random(20),
                'supplier_id'  => $data['supplier_id'],
                'location_id'  => $data['location_id'],
                'order_date'   => $data['order_date'],
                'deliver_to'   => $data['deliver_to'],
                'remarks'      => $data['remarks'] ?? null,
                'total_amount' => round($total, 2),
            ]);
            $po->update(['po_number' => sprintf('PO/%s/%04d', $this->fiscalYearLabel($data['order_date']), $po->id)]);
            $po->items()->createMany($lines);
            return $po->id;
        });

        $po = PurchaseOrder::with(self::WITH)->find($id);
        return response()->json([
            'success' => true,
            'message' => "Purchase Order saved successfully. Reference: {$po->po_number}",
            'data'    => $po->toApi(),
        ], 201);
    }
}