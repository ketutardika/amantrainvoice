<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:bank_transfer,cash,credit_card,debit_card,gopay,ovo,dana,shopeepay,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'invoice_id.required' => 'Please select an invoice.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be greater than 0.',
            'payment_date.required' => 'Payment date is required.',
            'payment_method.required' => 'Please select payment method.',
            'attachment.mimes' => 'Attachment must be PDF, JPG, JPEG, or PNG file.',
            'attachment.max' => 'Attachment size cannot exceed 2MB.',
        ];
    }
}