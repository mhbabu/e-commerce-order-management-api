<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
         $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'sometimes|required|numeric|min:0',
            'category'    => 'nullable|string',
            'sku'         => 'sometimes|required|string|unique:products,sku,' . $productId,
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'The name field is required.',
            'name.string'          => 'The name must be a string.',
            'name.max'             => 'The name must not exceed 255 characters.',
            'description.string'   => 'The description must be a string.',
            'base_price.required'  => 'The base price is required.',
            'base_price.numeric'   => 'The base price must be numeric.',
            'base_price.min'       => 'The base price cannot be negative.',
            'category.string'      => 'The category must be a string.',
            'sku.required'         => 'The sku field is required.',
            'sku.string'           => 'The sku must be a string.',
            'sku.unique'           => 'The sku has already been taken.',
            'is_active.boolean'    => 'The is_active field must be true or false.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors()
        ], 422));
    }
}
