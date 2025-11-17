<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ProductService;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\BulkImportProductsRequest;
use App\Http\Resources\ProductResource;
use App\Jobs\BulkImportProducts;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

   public function index(Request $request)
   {
       $query = $request->get('query');
       if ($query) {
           $products = $this->productService->searchProducts($query);
       } else {
           $products = $this->productService->getProductsByVendor(auth('api')->user()->id);
       }
       return jsonResponseWithPagination('Products retrieved', true, ProductResource::collection($products->load('variants.inventory')->paginate(15)));
   }

    public function store(StoreProductRequest $request)
     {
         $product = $this->productService->createProduct($request->only(['name', 'description', 'base_price', 'category', 'sku']), auth('api')->user()->id);

        foreach ($request->variants as $variantData) {
            $this->productService->createVariant($product->id, [
                'attributes' => $variantData['attributes'],
                'price_modifier' => $variantData['price_modifier'] ?? 0,
                'sku' => $variantData['sku'],
            ], [
                'quantity' => $variantData['quantity'],
                'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 10,
            ]);
        }

        return jsonResponse('Product created', true, new ProductResource($product->load('variants.inventory')), 201);
    }

    public function show($id)
    {
        $product = $this->productService->find($id);
        if (!$product) {
            return jsonResponse('Product not found', false, null, 404);
        }
        return jsonResponse('Product retrieved', true, new ProductResource($product->load('variants.inventory')));
    }

    public function update(UpdateProductRequest $request, $id)
     {
         $updated = $this->productService->updateProduct($id, $request->all());
        if (!$updated) {
            return jsonResponse('Product not found', false, null, 404);
        }

        return jsonResponse('Product updated', true);
    }

    public function destroy($id)
    {
        $deleted = $this->productService->deleteProduct($id);
        if (!$deleted) {
            return jsonResponse('Product not found', false, null, 404);
        }

        return jsonResponse('Product deleted', true);
    }

    public function bulkImport(BulkImportProductsRequest $request)
    {
        $file = $request->file('file');

        // Parse CSV
        $data = [];
        $handle = fopen($file->getPathname(), 'r');

        // Skip header row
        $header = fgetcsv($handle);

        if (!$header) {
            return jsonResponse('Invalid CSV file or empty file', false, null, 422);
        }

        // Validate header
        $requiredHeaders = ['name', 'sku', 'base_price'];
        $missingHeaders = array_diff($requiredHeaders, $header);

        if (!empty($missingHeaders)) {
            return jsonResponse('Missing required CSV headers: ' . implode(', ', $missingHeaders), false, null, 422);
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($row) !== count($header)) {
                return jsonResponse("Row {$rowNumber}: Column count mismatch. Expected " . count($header) . " columns, got " . count($row), false, null, 422);
            }

            // Combine header with row data
            $rowData = array_combine($header, $row);

            // Validate required fields
            if (empty(trim($rowData['name'])) || empty(trim($rowData['sku'])) || !is_numeric($rowData['base_price'])) {
                return jsonResponse("Row {$rowNumber}: Missing or invalid required fields (name, sku, base_price)", false, null, 422);
            }

            $data[] = $rowData;
        }

        fclose($handle);

        if (empty($data)) {
            return jsonResponse('No valid data found in CSV file', false, null, 422);
        }

        // Dispatch job
        BulkImportProducts::dispatch($data, auth('api')->user()->id);

        return jsonResponse('Bulk import has been queued for processing. You will receive a notification when completed.', true, ['total_rows' => count($data)]);
    }
}
