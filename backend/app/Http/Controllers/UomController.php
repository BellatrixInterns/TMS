<?php

namespace App\Http\Controllers;

use App\Models\Uom;

class UomController extends MasterController
{
    protected string $model = Uom::class;
    protected string $label = 'UOM';

    protected function rules($ignoreId = null): array
    {
        return ['name' => $this->nameRules('uoms', $ignoreId)];
    }

    protected function messages(): array
    {
        return $this->nameMessages($this->label);
    }
}