<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        try {
            // $userId = $request->user()->id;
            $userId = 1;

            $order = $this->orderService->createOrder($request->validated(), $userId);

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
        $order->load(['creator', 'items']);
        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
