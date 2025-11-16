<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ProductService;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\BulkImportProductsRequest;
use App\Jobs\BulkImportProducts;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="iPhone 15"),
 *     @OA\Property(property="description", type="string", example="Latest iPhone model"),
 *     @OA\Property(property="base_price", type="number", format="float", example=999.99),
 *     @OA\Property(property="category", type="string", example="Electronics"),
 *     @OA\Property(property="sku", type="string", example="IPH15-128"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="vendor_id", type="integer", example=1),
 *     @OA\Property(property="variants", type="array", @OA\Items(ref="#/components/schemas/ProductVariant")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ProductVariant",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="attributes", type="object", example={"color": "Black", "storage": "128GB"}),
 *     @OA\Property(property="price_modifier", type="number", format="float", example=0),
 *     @OA\Property(property="sku", type="string", example="IPH15-128-BLK"),
 *     @OA\Property(property="inventory", ref="#/components/schemas/Inventory")
 * )
 *
 * @OA\Schema(
 *     schema="Inventory",
 *     type="object",
 *     @OA\Property(property="quantity", type="integer", example=50),
 *     @OA\Property(property="low_stock_threshold", type="integer", example=10)
 * )
 */
class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Get products list",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        if ($query) {
            $products = $this->productService->searchProducts($query);
        } else {
            $products = $this->productService->getProductsByVendor(auth('api')->user()->id);
        }
        return response()->json($products->load('variants.inventory')->paginate(15));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","base_price","sku","variants"},
     *             @OA\Property(property="name", type="string", example="iPhone 15"),
     *             @OA\Property(property="description", type="string", example="Latest iPhone model"),
     *             @OA\Property(property="base_price", type="number", format="float", example=999.99),
     *             @OA\Property(property="category", type="string", example="Electronics"),
     *             @OA\Property(property="sku", type="string", example="IPH15-128"),
     *             @OA\Property(property="variants", type="array", @OA\Items(
     *                 type="object",
     *                 required={"attributes","sku","quantity"},
     *                 @OA\Property(property="attributes", type="object", example={"color": "Black", "storage": "128GB"}),
     *                 @OA\Property(property="price_modifier", type="number", format="float", example=0),
     *                 @OA\Property(property="sku", type="string", example="IPH15-128-BLK"),
     *                 @OA\Property(property="quantity", type="integer", example=50),
     *                 @OA\Property(property="low_stock_threshold", type="integer", example=10)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
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

        return response()->json($product->load('variants.inventory'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     summary="Get product by ID",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show($id)
    {
        $product = $this->productService->find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($product->load('variants.inventory'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{id}",
     *     summary="Update product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="base_price", type="number", format="float", example=1099.99),
     *             @OA\Property(property="category", type="string", example="Electronics"),
     *             @OA\Property(property="sku", type="string", example="IPH15PRO-128"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product updated")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
     public function update(UpdateProductRequest $request, $id)
     {
         $updated = $this->productService->updateProduct($id, $request->all());
        if (!$updated) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json(['message' => 'Product updated']);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     summary="Delete product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy($id)
    {
        $deleted = $this->productService->deleteProduct($id);
        if (!$deleted) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json(['message' => 'Product deleted']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products/bulk-import",
     *     summary="Bulk import products from CSV",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="CSV file containing product data"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk import queued",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bulk import has been queued for processing")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function bulkImport(BulkImportProductsRequest $request)
    {
        $file = $request->file('file');

        // Parse CSV
        $data = [];
        $handle = fopen($file->getPathname(), 'r');

        // Skip header row
        $header = fgetcsv($handle);

        if (!$header) {
            return response()->json(['error' => 'Invalid CSV file or empty file'], 422);
        }

        // Validate header
        $requiredHeaders = ['name', 'sku', 'base_price'];
        $missingHeaders = array_diff($requiredHeaders, $header);

        if (!empty($missingHeaders)) {
            return response()->json([
                'error' => 'Missing required CSV headers: ' . implode(', ', $missingHeaders)
            ], 422);
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($row) !== count($header)) {
                return response()->json([
                    'error' => "Row {$rowNumber}: Column count mismatch. Expected " . count($header) . " columns, got " . count($row)
                ], 422);
            }

            // Combine header with row data
            $rowData = array_combine($header, $row);

            // Validate required fields
            if (empty(trim($rowData['name'])) || empty(trim($rowData['sku'])) || !is_numeric($rowData['base_price'])) {
                return response()->json([
                    'error' => "Row {$rowNumber}: Missing or invalid required fields (name, sku, base_price)"
                ], 422);
            }

            $data[] = $rowData;
        }

        fclose($handle);

        if (empty($data)) {
            return response()->json(['error' => 'No valid data found in CSV file'], 422);
        }

        // Dispatch job
        BulkImportProducts::dispatch($data, auth('api')->user()->id);

        return response()->json([
            'message' => 'Bulk import has been queued for processing. You will receive a notification when completed.',
            'total_rows' => count($data)
        ]);
    }
}
