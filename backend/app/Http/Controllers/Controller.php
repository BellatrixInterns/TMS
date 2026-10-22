<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

abstract class Controller
{
    // Validate input. On failure, return our standard JSON error with the first message.
    protected function validate422($request, array $rules, array $messages = []): array
    {
        $v = Validator::make($request->all(), $rules, $messages);
        if ($v->fails()) {
            throw new HttpResponseException(
                response()->json(['success' => false, 'message' => $v->errors()->first()], 422)
            );
        }
        return $v->validated();
    }

    protected function notFound(string $what)
    {
        return response()->json(['success' => false, 'message' => "$what not found."], 404);
    }

    // Letters, numbers, underscore only. Unique in the given table.
    protected function nameRules(string $table, $ignoreId = null, int $max = 100): array
    {
        return ['bail', 'required', 'string', "max:$max", 'regex:/^[A-Za-z0-9_]+$/', Rule::unique($table, 'name')->ignore($ignoreId)];
    }

    protected function nameMessages(string $label): array
    {
        return [
            'name.required' => "$label Name is required.",
            'name.regex'    => "$label Name can only contain letters, numbers and underscore (_). Spaces and other symbols are not allowed.",
            'name.unique'   => "$label \":input\" already exists.",
        ];
    }
}