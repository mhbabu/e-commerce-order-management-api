<?php

/**
 * @OA\Get(
 *     path="/api/v1/orders",
 *     summary="Get orders list",
 *     tags={"Orders"},
 *     security={{"bearerAuth":{}}},
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
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Orders retrieved"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 *
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
 *     @OA\Response(
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
 *
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
 *     @OA\Response(
 *         response=200,
 *         description="Order details",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Order retrieved"),
 *             @OA\Property(property="data", ref="#/components/schemas/Order")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=404, description="Order not found")
 * )
 *
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
 *     @OA\Response(
 *         response=200,
 *         description="Order status updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Order status updated")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=404, description="Order not found"),
 *     @OA\Response(response=422, description="Validation errors")
 * )
 *
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
 *     @OA\Response(
 *         response=200,
 *         description="Order cancelled",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Order cancelled")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=404, description="Order not found or cannot be cancelled")
 * )
 */