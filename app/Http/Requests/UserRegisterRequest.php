<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class UserRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|not_regex:/<\s*script\b/i',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ];
    }
}
