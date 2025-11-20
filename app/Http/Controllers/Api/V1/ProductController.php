<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\BulkImportProductsRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\Product\ProductElasticSearchService;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;
    protected ProductElasticSearchService $productElasticSearchService;

    public function __construct(
        ProductService $productService,
        ProductElasticSearchService $productElasticSearchService
    ) {
        $this->productService = $productService;
        $this->productElasticSearchService = $productElasticSearchService;

    }

    public function index(Request $request)
    {
        // Read all request data
        $filteringData = [
            'page'       => $request->input('page', 1),
            'per_page'   => $request->input('per_page', 15),
            'search'     => $request->input('search'),
            'category'   => $request->input('category'),
            'vendor_id'  => $request->input('vendor_id'),
        ];

        $products = $this->productService->getProductsList($filteringData);

        $productList = ProductResource::collection($products)->response()->getData(true);

        return jsonResponseWithPagination('Products retrieved successfully', true, $productList);
    }

    public function store(StoreProductRequest $request)
    {
        $data    = $request->all(); // grab all request data including variants
        $product = $this->productService->createProductWithVariants($data, auth('api')->id());
        return jsonResponse('Product created', true, new ProductResource($product->load('variants.inventory')), 201);
    }

    public function show($id)
    {
        $product = $this->productService->findProduct($id);
        if (!$product) {
            return jsonResponse('Product not found', false, null, 404);
        }
        return jsonResponse('Product retrieved', true, new ProductResource($product->load('variants.inventory')));
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $result = $this->productService->updateProductWithVariants($id, $request->all());

        if ($result === 'unauthorized') {
            return jsonResponse('You have no permission to edit this item', false, null, 403);
        }

        if ($result === null) {
            return jsonResponse('Product not found', false, null, 404);
        }

        return jsonResponse('Product updated successfully', true, new ProductResource($result));
    }

    public function destroy($id)
    {
        $result = $this->productService->deleteProduct($id);

        if ($result === 'unauthorized') {
            return jsonResponse('You have no permission to delete this item', false, null, 403);
        }

        if ($result === null) {
            return jsonResponse('Product not found', false, null, 404);
        }

        return jsonResponse('Product deleted', true);
    }

    public function bulkImport(BulkImportProductsRequest $request)
    {
        $file     = $request->file('file');
        $vendorId = $request->vendor_id;

        try {
            $data = $this->productService->importProductsFromCsv($file, $vendorId);
            return jsonResponse('Product imorted successfully', true, $data);
        } catch (\Exception $e) {
            return jsonResponse($e->getMessage(), false, null, 500);
        }
    }

    public function search(Request $request)
    {
        $page    = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);
        $query   = $request->input('search', '');
        $filters = [ 'category' => $request->input('category'), 'vendor_id' => $request->input('vendor_id')];

        // Call ElasticSearch service
        $result = $this->productElasticSearchService->searchProducts($query, $filters, $page, $perPage);

        return response()->json([
            'message' => 'Products retrieved successfully',
            'status' => true,
            'data' => ProductResource::collection(collect($result['data'])),
            'pagination' => [
                'total' => $result['total'],
                'current_page' => $page,
                'per_page' => $perPage,
            ]
        ], 200);
    }
}
