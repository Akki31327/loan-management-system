<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'loan_id' => 'required|exists:loans,id',

            'amount_paid' => 'required|numeric|min:1',

            'payment_mode' => 'required|in:cash,upi,card',

            'location' => 'nullable|string|max:255',

            'remarks' => 'nullable|string|max:500'
        ];
    }
}