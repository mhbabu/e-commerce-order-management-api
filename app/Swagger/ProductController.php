<?php

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
 *
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
 *
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
 *
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
 *
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
 *
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
