<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'payment_mode' => strtolower(trim($this->payment_mode))
        ]);
    }

    public function rules(): array
    {
        return [

            'loan_id' => 'required|exists:loans,id',

            'amount_paid' => 'required|numeric|min:1',

            'payment_mode' => 'required|in:cash,upi,bank_transfer',

            'location' => 'nullable|string|max:255',

            'remarks' => 'nullable|string|max:500'
        ];
    }
}