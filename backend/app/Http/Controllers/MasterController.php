<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

abstract class MasterController extends Controller
{
    protected string $model;
    protected string $label;
    protected string $usedBy = 'items';

    abstract protected function rules($ignoreId = null): array;

    protected function messages(): array
    {
        return [];
    }

    public function index()
    {
        return response()->json(['success' => true, 'data' => ($this->model)::orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validate422($request, $this->rules(), $this->messages());
        $row = ($this->model)::create($data);
        return response()->json([
            'success' => true,
            'message' => "{$this->label} saved successfully.",
            'data'    => $row->fresh(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $row = ($this->model)::find($id);
        if (!$row) return $this->notFound($this->label);

        $data = $this->validate422($request, $this->rules($id), $this->messages());
        $row->update($data);
        return response()->json([
            'success' => true,
            'message' => "{$this->label} updated successfully.",
            'data'    => $row->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $row = ($this->model)::find($id);
        if (!$row) return $this->notFound($this->label);

        try {
            $row->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete: this {$this->label} is used by one or more {$this->usedBy}.",
                ], 409);
            }
            throw $e;
        }
        return response()->json(['success' => true, 'message' => "{$this->label} deleted successfully."]);
    }
}