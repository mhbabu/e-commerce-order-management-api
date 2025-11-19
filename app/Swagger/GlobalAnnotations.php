<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="E-commerce Order Management API",
 *     version="1.0.0",
 *     description="API for managing orders, products, and users in an e-commerce system"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin","vendor","customer"}, example="customer"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
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
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="shipping_address", type="string", example="123 Main St, City, State 12345"),
 *     @OA\Property(property="billing_address", type="string", nullable=true, example="123 Main St, City, State 12345"),
 *     @OA\Property(property="status", type="string", enum={"pending","processing","shipped","delivered","cancelled"}, example="pending"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=1099.99),
 *     @OA\Property(property="order_items", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="product_variant_id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=549.99),
 *     @OA\Property(property="total_price", type="number", format="float", example=1099.98),
 *     @OA\Property(property="product_variant", ref="#/components/schemas/ProductVariant")
 * )
 */
class GlobalAnnotations
{
    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"admin","vendor","customer"}, example="customer")
     *         )
     *     ),
     * @OA\Response(
      *         response=201,
      *         description="User registered successfully",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="User registered successfully"),
      *             @OA\Property(property="user", ref="#/components/schemas/User"),
      *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
      *         )
      *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function register() {}

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     * @OA\Response(
      *         response=200,
      *         description="Login successful",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="User logged in successfully"),
      *             @OA\Property(property="user", ref="#/components/schemas/User"),
      *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
      *         )
      *     ),
     *     @OA\Response(
      *         response=401,
      *         description="Invalid credentials",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Invalid credentials"),
      *             @OA\Property(property="user", type="array", @OA\Items(type="string"))
      *         )
      *     )
     * )
     */
    public function login() {}

    /**
     * @OA\Post(
     *     path="/api/v1/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     * @OA\Response(
      *         response=200,
      *         description="Token refreshed",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
      *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
      *         )
      *     ),
     *     @OA\Response(
      *         response=401,
      *         description="Unauthorized",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Token not provided")
      *         )
      *     )
     * )
     */
    public function refresh() {}

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     * @OA\Response(
      *         response=200,
      *         description="Successfully logged out",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Logged out successfully")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout() {}

    /**
     * @OA\Get(
     *     path="/api/v1/me",
     *     summary="Get current user info",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     * @OA\Response(
      *         response=200,
      *         description="User information",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="User data retrieved"),
      *             @OA\Property(property="user", ref="#/components/schemas/User")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function me() {}

    /**
     * @OA\Get(
      *     path="/api/v1/products",
      *     summary="Get products list",
      *     tags={"Products"},
      *     security={{"bearerAuth":{}}},
      *     @OA\Parameter(
      *         name="page",
      *         in="query",
      *         description="Page number",
      *         required=false,
      *         @OA\Schema(type="integer", default=1)
      *     ),
      *     @OA\Parameter(
      *         name="per_page",
      *         in="query",
      *         description="Items per page",
      *         required=false,
      *         @OA\Schema(type="integer", default=15)
      *     ),
      *     @OA\Parameter(
      *         name="search",
      *         in="query",
      *         description="Search query",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Products list",
      *         @OA\JsonContent(
      *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
      *             @OA\Property(property="current_page", type="integer"),
      *             @OA\Property(property="per_page", type="integer"),
      *             @OA\Property(property="total", type="integer")
      *         )
      *     ),
      *     @OA\Response(response=401, description="Unauthorized")
      * )
     */
    public function productsIndex() {}

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
     * @OA\Response(
      *         response=201,
      *         description="Product created",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Product created"),
      *             @OA\Property(property="data", ref="#/components/schemas/Product")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function productsStore() {}

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
     * @OA\Response(
      *         response=200,
      *         description="Product details",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Product retrieved"),
      *             @OA\Property(property="data", ref="#/components/schemas/Product")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(
      *         response=404,
      *         description="Product not found",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Product not found")
      *         )
      *     )
     * )
     */
    public function productsShow() {}

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
     * @OA\Response(
      *         response=200,
      *         description="Product updated",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Product updated successfully"),
      *             @OA\Property(property="data", ref="#/components/schemas/Product")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(
      *         response=403,
      *         description="Forbidden",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="You have no permission to edit this item")
      *         )
      *     ),
     *     @OA\Response(
      *         response=404,
      *         description="Product not found",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Product not found")
      *         )
      *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function productsUpdate() {}

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
     * @OA\Response(
      *         response=200,
      *         description="Product deleted",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Product deleted")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(
      *         response=403,
      *         description="Forbidden",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="You have no permission to delete this item")
      *         )
      *     ),
     *     @OA\Response(
      *         response=404,
      *         description="Product not found",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Product not found")
      *         )
      *     )
     * )
     */
    public function productsDestroy() {}

    /**
     * @OA\Post(
     *     path="/api/v1/products/bulk-import",
     *     summary="Bulk import products from CSV",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
      *         required=true,
      *         @OA\MediaType(
      *             mediaType="multipart/form-data",
      *             @OA\Schema(
      *                 required={"file","vendor_id"},
      *                 @OA\Property(
      *                     property="file",
      *                     type="string",
      *                     format="binary",
      *                     description="CSV file containing product data"
      *                 ),
      *                 @OA\Property(
      *                     property="vendor_id",
      *                     type="integer",
      *                     example=1,
      *                     description="Vendor ID"
      *                 )
      *             )
      *         )
      *     ),
     * @OA\Response(
      *         response=200,
      *         description="Bulk import successful",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Product imported successfully"),
      *             @OA\Property(property="data", type="object")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(
      *         response=500,
      *         description="Import failed",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Error message")
      *         )
      *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function productsBulkImport() {}

    /**
     * @OA\Get(
      *     path="/api/v1/orders",
      *     summary="Get orders list",
      *     tags={"Orders"},
      *     security={{"bearerAuth":{}}},
      *     @OA\Parameter(
      *         name="page",
      *         in="query",
      *         description="Page number",
      *         required=false,
      *         @OA\Schema(type="integer", default=1)
      *     ),
      *     @OA\Parameter(
      *         name="per_page",
      *         in="query",
      *         description="Items per page",
      *         required=false,
      *         @OA\Schema(type="integer", default=15)
      *     ),
      *     @OA\Parameter(
      *         name="search",
      *         in="query",
      *         description="Search query",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="status",
      *         in="query",
      *         description="Filter by order status",
      *         required=false,
      *         @OA\Schema(type="string", enum={"pending","processing","shipped","delivered","cancelled"})
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Orders list",
      *         @OA\JsonContent(
      *             @OA\Property(property="message", type="string", example="Orders retrieved successfully"),
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")),
      *             @OA\Property(property="current_page", type="integer"),
      *             @OA\Property(property="per_page", type="integer"),
      *             @OA\Property(property="total", type="integer")
      *         )
      *     ),
      *     @OA\Response(response=401, description="Unauthorized")
      * )
     */
    public function ordersIndex() {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shipping_address","items"},
     *             @OA\Property(property="shipping_address", type="string", example="123 Main St, City, State 12345"),
     *             @OA\Property(property="billing_address", type="string", example="123 Main St, City, State 12345"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 type="object",
     *                 required={"product_variant_id","quantity"},
     *                 @OA\Property(property="product_variant_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=2)
     *             ))
     *         )
     *     ),
     * @OA\Response(
      *         response=201,
      *         description="Order created",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Order created"),
      *             @OA\Property(property="data", ref="#/components/schemas/Order")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function ordersStore() {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     summary="Get order by ID",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     * @OA\Response(
      *         response=200,
      *         description="Order details",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Order retrieved"),
      *             @OA\Property(property="data", ref="#/components/schemas/Order")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(
      *         response=404,
      *         description="Order not found",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Order not found")
      *         )
      *     )
     * )
     */
    public function ordersShow() {}

    /**
     * @OA\Patch(
     *     path="/api/v1/orders/{id}/status",
     *     summary="Update order status",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending","processing","shipped","delivered","cancelled"}, example="processing")
     *         )
     *     ),
     * @OA\Response(
      *         response=200,
      *         description="Order status updated or already set",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Order status updated")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(
      *         response=404,
      *         description="Order not found",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Order not found")
      *         )
      *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function ordersUpdateStatus() {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders/{id}/cancel",
     *     summary="Cancel order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     * @OA\Response(
      *         response=200,
      *         description="Order cancelled or already cancelled",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Order cancelled")
      *         )
      *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(
      *         response=400,
      *         description="Order cannot be cancelled",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Order cannot be cancelled")
      *         )
      *     ),
     *     @OA\Response(
      *         response=404,
      *         description="Order not found",
      *         @OA\JsonContent(
      *             @OA\Property(property="status", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Order not found")
      *         )
      *     )
     * )
     */
    public function ordersCancel() {}
}