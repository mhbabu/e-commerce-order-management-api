<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare incoming data before validation.
     */
    protected function prepareForValidation()
    {
        $user = $this->user();

        // If the user is a customer, auto-set customer_id = their own id
        if ($user->role === 'customer') {
            $this->merge([
                'customer_id' => $user->id,
            ]);
        } else {
            // Admin/vendor must select customer_id manually
            $this->merge([
                'customer_id' => $this->input('customer_id'),
            ]);
        }
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'shipping_address'           => 'required|string',
            'billing_address'            => 'nullable|string',

            // Order items
            'items'                      => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
        ];

        // Admin OR vendor -> customer_id is required
        if ($user->role !== 'customer') {
            $rules['customer_id'] = 'required|exists:users,id';
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        $user = $this->user();
        $role = ucfirst($user->role);   // admin → Admin

        return [
            // Dynamic role-based message
            'customer_id.required'                => "As a {$role}, you must select a customer to create this order.",
            'customer_id.exists'                  => 'The selected customer does not exist.',
            'shipping_address.required'           => 'Shipping address is required.',
            'shipping_address.string'             => 'Shipping address must be a valid string.',
            'billing_address.string'              => 'Billing address must be a valid string.',
            'items.required'                      => 'Order must contain at least one item.',
            'items.array'                         => 'Items must be in array format.',
            'items.min'                           => 'At least one item is required.',
            'items.*.product_variant_id.required' => 'Product variant ID is required.',
            'items.*.product_variant_id.exists'   => 'Invalid product variant selected.',
            'items.*.quantity.required'           => 'Quantity is required.',
            'items.*.quantity.integer'            => 'Quantity must be an integer.',
            'items.*.quantity.min'                => 'Quantity must be at least 1.',
        ];
    }

    /**
     * Validation failure handler.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
