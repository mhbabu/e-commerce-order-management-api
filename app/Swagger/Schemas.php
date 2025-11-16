<?php

/**
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