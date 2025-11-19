<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\BulkImportProductsRequest;
use App\Http\Resources\Product\ProductResource;
use App\Services\Product\ProductService;
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
        // Read all request data
        $filteringData = [
            'page'       => $request->input('page', 1),
            'per_page'   => $request->input('per_page', 15),
            'search'     => $request->input('search'),
        ];
        $products      = $this->productService->getProductsList($filteringData);
        $productList   = ProductResource::collection($products)->response()->getData(true);

        return jsonResponseWithPagination('Products retrieved successfully', true,  $productList);
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
}
