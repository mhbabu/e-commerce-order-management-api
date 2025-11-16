<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateOrderAction;
use App\Services\OrderService;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderStatusRequest;
use Illuminate\Http\Request;

/**
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
class OrderController extends Controller
{
    protected OrderService $orderService;
    protected CreateOrderAction $createOrderAction;

    public function __construct(OrderService $orderService, CreateOrderAction $createOrderAction)
    {
        $this->orderService = $orderService;
        $this->createOrderAction = $createOrderAction;
    }

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
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        if ($user->role === 'customer') {
            $orders = $this->orderService->getOrdersByUser($user->id);
        } elseif ($user->role === 'vendor') {
            $orders = $this->orderService->getOrdersByVendor($user->id);
        } else {
            $orders = $this->orderService->getOrdersByStatus($request->get('status', 'pending'));
        }
        return response()->json($orders->load('orderItems.productVariant.product', 'user'));
    }

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
     *     @OA\Response(
     *         response=201,
     *         description="Order created",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
     public function store(StoreOrderRequest $request)
     {
         $order = $this->createOrderAction->execute($request->only(['shipping_address', 'billing_address']), $request->items, auth('api')->user()->id);

        return response()->json($order->load('orderItems.productVariant'), 201);
    }

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
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show($id)
    {
        $order = $this->orderService->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        return response()->json($order->load('orderItems.productVariant.product', 'invoice'));
    }

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
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order status updated")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
     public function updateStatus(UpdateOrderStatusRequest $request, $id)
     {
         $updated = $this->orderService->updateOrderStatus($id, $request->status);
        if (!$updated) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json(['message' => 'Order status updated']);
    }

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
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order cancelled")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Order not found or cannot be cancelled")
     * )
     */
    public function cancel($id)
    {
        $cancelled = $this->orderService->cancelOrder($id);
        if (!$cancelled) {
            return response()->json(['error' => 'Order not found or cannot be cancelled'], 404);
        }

        return response()->json(['message' => 'Order cancelled']);
    }
}
