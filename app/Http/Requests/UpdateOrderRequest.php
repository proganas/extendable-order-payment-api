<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255|not_regex:/<\s*script\b/i',
            'price' => 'nullable|numeric|min:0.01',
            'quantity' => 'nullable|numeric|min:1',
        ];
    }
}
