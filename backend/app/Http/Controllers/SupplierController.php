<?php

namespace App\Http\Controllers;

use App\Models\Supplier;

class SupplierController extends MasterController
{
    protected string $model = Supplier::class;
    protected string $label = 'Supplier';
    protected string $usedBy = 'purchase orders';

    protected function rules($ignoreId = null): array
    {
        return [
            'name'      => $this->nameRules('suppliers', $ignoreId, 150),
            'phone'     => 'required|string|max:30',
            'email'     => 'required|email|max:100',
            'address'   => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }

    protected function messages(): array
    {
        return $this->nameMessages($this->label) + [
            'phone.required'   => 'Phone Number is required.',
            'email.required'   => 'A valid Email Address is required.',
            'email.email'      => 'A valid Email Address is required.',
            'address.required' => 'Address is required.',
        ];
    }
}