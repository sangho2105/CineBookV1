<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cho phép tất cả người dùng đăng ký
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'age' => 'nullable|integer|min:1|max:120',
            'preferred_language' => 'nullable|string|max:50',
            'preferred_city' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email format is invalid.',
            'email.unique' => 'This email has already been taken.',
            'phone.required' => 'The phone number is required.',
            'phone.unique' => 'This phone number has already been taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
