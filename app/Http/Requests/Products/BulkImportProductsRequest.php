<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BulkImportProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() && in_array(auth('api')->user()->role, ['vendor', 'admin']);
    }

    public function rules(): array
    {
        $rules = [
            'file' => 'required|file|mimes:csv|max:10240', // max 10MB
        ];

        // Additional validation based on user role
        if (auth('api')->user()->role === 'admin') {
            // Admin: Require vendor_id and check it exists in the users table
            $rules['vendor_id'] = 'required|exists:users,id'; // Assuming `users` table contains vendors as well
        } elseif (auth('api')->user()->role === 'vendor') {
            // Vendor: Automatically append the current vendor_id for vendors
            $rules['vendor_id'] = 'required|in:' . auth('api')->user()->id;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'file.required'      => 'A CSV file is required.',
            'file.file'          => 'The uploaded file must be a valid file.',
            'file.mimes'         => 'The file must be a CSV file.',
            'file.max'           => 'The file size must not exceed 10MB.',
            'vendor_id.required' => 'Vendor ID is required.',
            'vendor_id.exists'   => 'The specified Vendor ID does not exist in the users table.',
            'vendor_id.in'       => 'The Vendor ID must match the authenticated vendor.',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (!$this->hasFile('file')) return;

            $file = $this->file('file');
            $handle = fopen($file->getRealPath(), 'r');

            if (!$handle) {
                $validator->errors()->add('file', 'Failed to read the CSV file.');
                return;
            }

            // Read header
            $headerLine = fgets($handle);
            $csvHeaders = array_map('trim', str_getcsv($headerLine));

            $requiredHeaders = [
                'product_name',
                'description',
                'base_price',
                'category',
                'product_sku',
                'variant_color',
                'variant_storage',
                'price_modifier',
                'variant_sku',
                'quantity',
                'low_stock_threshold',
            ];

            // Check headers
            foreach ($requiredHeaders as $header) {
                if (!in_array($header, $csvHeaders)) {
                    $validator->errors()->add('file', "CSV missing required header: {$header}");
                }
            }

            // Check if CSV has at least one data row
            $firstDataRow = fgetcsv($handle);
            if ($firstDataRow === false) {
                $validator->errors()->add('file', 'CSV file must contain at least one data row.');
                fclose($handle);
                return;
            }

            // Optional: Check each row column count
            $lineNumber = 2; // Since first row is header
            do {
                if (count($firstDataRow) !== count($csvHeaders)) {
                    $validator->errors()->add('file', "Row {$lineNumber} does not match header column count.");
                }
                $lineNumber++;
            } while (($firstDataRow = fgetcsv($handle)) !== false);

            fclose($handle);
        });
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
