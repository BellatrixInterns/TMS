<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends MasterController
{
    protected string $model = Category::class;
    protected string $label = 'Category';

    protected function rules($ignoreId = null): array
    {
        return [
            'name'      => $this->nameRules('item_categories', $ignoreId),
            'is_active' => 'sometimes|boolean',
        ];
    }

    protected function messages(): array
    {
        return $this->nameMessages($this->label);
    }
}