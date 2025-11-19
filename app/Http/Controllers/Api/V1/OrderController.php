<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateOrderAction;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderStatusRequest;
use App\Http\Resources\Product\OrderResource;
use App\Services\Product\OrderService;
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
        $filteringData = [
            'page'       => $request->input('page', 1),
            'per_page'   => $request->input('per_page', 15),
            'search'     => $request->input('search'),
            'search_by'  => $request->input('search_by', ['order_number']),
            'status'     => $request->input('status'),
            'sort_by'    => $request->input('sort_by', 'id'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        $currentUser  = auth('api')->user();
        $orders       = $this->orderService->getOrders($filteringData, $currentUser->id, $currentUser->role);
        $orderList    = OrderResource::collection($orders)->response()->getData(true);

        return jsonResponseWithPagination('Orders retrieved successfully', true, $orderList);
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
