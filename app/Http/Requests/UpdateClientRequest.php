<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('clients')->ignore($this->client->id)
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'client_type' => 'required|in:individual,company',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|integer|min:1|max:365',
            'is_active' => 'boolean',
        ];
    }
}