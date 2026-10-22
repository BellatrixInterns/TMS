<?php

namespace App\Http\Controllers;

use App\Models\Location;

class LocationController extends MasterController
{
    protected string $model = Location::class;
    protected string $label = 'Location';
    protected string $usedBy = 'purchase orders';

    protected function rules($ignoreId = null): array
    {
        return [
            'name'      => $this->nameRules('locations', $ignoreId),
            'address'   => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }

    protected function messages(): array
    {
        return $this->nameMessages($this->label) + ['address.required' => 'Address is required.'];
    }
}