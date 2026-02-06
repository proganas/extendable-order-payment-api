<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|not_regex:/<\s*script\b/i',
            'price' => 'required|numeric|min:0.01',
            'quantity' => 'required|numeric|min:1',
        ];
    }
}
