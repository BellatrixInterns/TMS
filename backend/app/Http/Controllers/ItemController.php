<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    private function rules(): array
    {
        return [
            'item_name'   => ['bail', 'required', 'string', 'max:150', 'regex:/^[A-Za-z0-9_]+$/'],
            'category_id' => 'required|integer|exists:item_categories,id',
            'brand_id'    => 'required|integer|exists:brands,id',
            'uom_id'      => 'required|integer|exists:uoms,id',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    private function messages(): array
    {
        return [
            'item_name.required'   => 'Item Name is required.',
            'item_name.regex'      => 'Item Name can only contain letters, numbers and underscore (_). Spaces and other symbols are not allowed.',
            'category_id.required' => 'Please select a Category.',
            'category_id.exists'   => 'Selected Category does not exist.',
            'brand_id.required'    => 'Please select a Brand.',
            'brand_id.exists'      => 'Selected Brand does not exist.',
            'uom_id.required'      => 'Please select a UOM.',
            'uom_id.exists'        => 'Selected UOM does not exist.',
        ];
    }

    private function load($id)
    {
        return Item::with(['category', 'brand', 'uom'])->find($id);
    }

    public function index()
    {
        $items = Item::with(['category', 'brand', 'uom'])->orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $items->map(fn ($i) => $i->toApi())->values()]);
    }

    public function store(Request $request)
    {
        $data = $this->validate422($request, $this->rules(), $this->messages());

        // Insert with a temporary code, then set ITM-<id>. The id is unique, so two
        // simultaneous saves can never get the same code.
        $id = DB::transaction(function () use ($data) {
            $item = Item::create($data + ['item_code' => 'TMP-' . Str::random(20)]);
            $item->update(['item_code' => sprintf('ITM-%06d', $item->id)]);
            return $item->id;
        });

        $item = $this->load($id);
        return response()->json([
            'success' => true,
            'message' => "Item saved successfully. Item Code: {$item->item_code}",
            'data'    => $item->toApi(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $item = Item::find($id);
        if (!$item) return $this->notFound('Item');

        $data = $this->validate422($request, $this->rules(), $this->messages());
        $item->update($data);

        return response()->json(['success' => true, 'message' => 'Item updated successfully.', 'data' => $this->load($id)->toApi()]);
    }

    public function destroy($id)
    {
        $item = Item::find($id);
        if (!$item) return $this->notFound('Item');

        try {
            $item->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json(['success' => false, 'message' => 'Cannot delete: this Item is used by one or more purchase orders.'], 409);
            }
            throw $e;
        }
        return response()->json(['success' => true, 'message' => 'Item deleted successfully.']);
    }
}