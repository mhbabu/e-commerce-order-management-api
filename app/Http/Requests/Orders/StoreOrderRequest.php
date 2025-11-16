<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

     /**
     * Get the custom error messages for the validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shipping_address.required' => 'The shipping address field is required.',
            'shipping_address.string' => 'The shipping address field must be a string.',
            'billing_address.string' => 'The billing address field must be a string.',
            'items.required' => 'The items field is required.',
            'items.array' => 'The items field must be an array.',
            'items.*.product_variant_id.required' => 'The product variant id field is required for each item.',
            'items.*.product_variant_id.exists' => 'The selected product variant id is invalid for each item.',
            'items.*.quantity.required' => 'The quantity field is required for each item.',
            'items.*.quantity.integer' => 'The quantity field must be an integer for each item.',
            'items.*.quantity.min' => 'The quantity field must be at least 1 for each item.',
        ];
    }

     /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $response = response()->json([
            'status'  => false,
            'message' => $errors->first(),
            'errors'  => $errors
        ], 422);

        throw new HttpResponseException($response);
    }
}