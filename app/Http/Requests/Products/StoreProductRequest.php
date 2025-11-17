<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
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
            'name'                           => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'base_price'                     => 'required|numeric|min:0',
            'category'                       => 'nullable|string',
            'sku'                            => 'required|string|unique:products',
            'variants'                       => 'required|array',
            'variants.*.attributes'          => 'required|array',
            'variants.*.price_modifier'      => 'numeric|min:0',
            'variants.*.sku'                 => 'required|string|unique:product_variants',
            'variants.*.quantity'            => 'required|integer|min:0',
            'variants.*.low_stock_threshold' => 'integer|min:0',
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
            'name.required'                          => 'The name field is required.',
            'name.string'                            => 'The name field must be a string.',
            'name.max'                               => 'The name field must not exceed 255 characters.',
            'description.string'                     => 'The description field must be a string.',
            'base_price.required'                    => 'The base price field is required.',
            'base_price.numeric'                     => 'The base price field must be a number.',
            'base_price.min'                         => 'The base price field must be at least 0.',
            'category.string'                        => 'The category field must be a string.',
            'sku.required'                           => 'The sku field is required.',
            'sku.string'                             => 'The sku field must be a string.',
            'sku.unique'                             => 'The sku has already been taken.',
            'variants.required'                      => 'The variants field is required.',
            'variants.array'                         => 'The variants field must be an array.',
            'variants.*.attributes.required'         => 'The attributes field is required for each variant.',
            'variants.*.attributes.array'            => 'The attributes field must be an array for each variant.',
            'variants.*.price_modifier.numeric'      => 'The price modifier field must be a number for each variant.',
            'variants.*.price_modifier.min'          => 'The price modifier field must be at least 0 for each variant.',
            'variants.*.sku.required'                => 'The sku field is required for each variant.',
            'variants.*.sku.string'                  => 'The sku field must be a string for each variant.',
            'variants.*.sku.unique'                  => 'The sku has already been taken for each variant.',
            'variants.*.quantity.required'           => 'The quantity field is required for each variant.',
            'variants.*.quantity.integer'            => 'The quantity field must be an integer for each variant.',
            'variants.*.quantity.min'                => 'The quantity field must be at least 0 for each variant.',
            'variants.*.low_stock_threshold.integer' => 'The low stock threshold field must be an integer for each variant.',
            'variants.*.low_stock_threshold.min'     => 'The low stock threshold field must be at least 0 for each variant.',
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
        $errors   = $validator->errors();
        $response = response()->json(['status' => false, 'message' => $errors->first(), 'errors' => $errors], 422);
        throw new HttpResponseException($response);
    }
}