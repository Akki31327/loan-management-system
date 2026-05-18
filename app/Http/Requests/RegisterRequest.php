<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email',

            'mobile' => 'required|digits:10|unique:users,mobile',

            'password' => 'required|min:6|confirmed',

            'role' => 'nullable|in:admin,field_agent'
        ];
    }
}