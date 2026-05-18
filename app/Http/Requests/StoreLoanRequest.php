<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'loan_no' => 'required|string|unique:loans,loan_no',

            'customer_name' => 'required|string|max:255',

            'mobile' => 'required|digits:10',

            'address' => 'required|string',

            'loan_amount' => 'required|numeric|min:1',

            'emi_amount' => 'required|numeric|min:1'
        ];
    }
}