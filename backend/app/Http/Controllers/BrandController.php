<?php

namespace App\Http\Controllers;

use App\Models\Brand;

class BrandController extends MasterController
{
    protected string $model = Brand::class;
    protected string $label = 'Brand';

    protected function rules($ignoreId = null): array
    {
        return ['name' => $this->nameRules('brands', $ignoreId)];
    }

    protected function messages(): array
    {
        return $this->nameMessages($this->label);
    }
}