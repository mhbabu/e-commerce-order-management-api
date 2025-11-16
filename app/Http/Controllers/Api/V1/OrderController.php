<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateOrderAction;
use App\Services\OrderService;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderStatusRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected CreateOrderAction $createOrderAction;

    public function __construct(OrderService $orderService, CreateOrderAction $createOrderAction)
    {
        $this->orderService = $orderService;
        $this->createOrderAction = $createOrderAction;
    }

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
         return jsonResponse('Orders retrieved', true, $orders->load('orderItems.productVariant.product', 'user'));
     }

    public function store(StoreOrderRequest $request)
     {
         $order = $this->createOrderAction->execute($request->only(['shipping_address', 'billing_address']), $request->items, auth('api')->user()->id);

         return jsonResponse('Order created', true, $order->load('orderItems.productVariant'), 201);
     }

    public function show($id)
     {
         $order = $this->orderService->find($id);
         if (!$order) {
             return jsonResponse('Order not found', false, null, 404);
         }
         return jsonResponse('Order retrieved', true, $order->load('orderItems.productVariant.product', 'invoice'));
     }

    public function updateStatus(UpdateOrderStatusRequest $request, $id)
     {
         $updated = $this->orderService->updateOrderStatus($id, $request->status);
         if (!$updated) {
             return jsonResponse('Order not found', false, null, 404);
         }

         return jsonResponse('Order status updated', true);
     }

    public function cancel($id)
     {
         $cancelled = $this->orderService->cancelOrder($id);
         if (!$cancelled) {
             return jsonResponse('Order not found or cannot be cancelled', false, null, 404);
         }

         return jsonResponse('Order cancelled', true);
     }
}
