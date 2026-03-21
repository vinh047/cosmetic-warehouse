<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Order::class);

        $orders = Order::with('creator')
            ->filter($request->all())
            ->paginate($request->input('per_page', 10));

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        Gate::authorize('create', Order::class);

        try {
            $userId = $request->user()->id;

            $order = $this->orderService->createOrder($request->validated(), $userId);
            OrderCreated::dispatch($order);
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data'    => new OrderResource($order)
            ], 201);
        } catch (Exception $e) {
            // [Tip]: Luôn ghi Log lại các lỗi giao dịch để dễ debug trên Production
            Log::error('Order Creation Failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'request' => $request->all()
            ]);

            // Bắt các lỗi văng ra từ Service (Hết hàng, Lỗi đồng bộ...)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['creator', 'items']);
        return new OrderResource($order);
    }

    public function updateStatus(Request $request, Order $order)
    {
        Gate::authorize('updateStatus', $order);

        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)]
        ]);

        try {
            $userId = $request->user()->id;

            $updatedOrder = $this->orderService->updateStatus($order, $validated['status'], $userId);

            return response()->json([
                'success' => true,
                'message' => "Order status updated to {$validated['status']}.",
                'data'    => new OrderResource($updatedOrder)
            ]);
        } catch (Exception $e) {
            Log::error("Order Status Update Failed (Order ID: {$order->id}): " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
