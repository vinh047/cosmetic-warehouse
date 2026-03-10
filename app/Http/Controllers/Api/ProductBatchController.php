<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductBatchRequest;
use App\Http\Resources\ProductBatchResource;
use App\Models\ProductBatch;
use Illuminate\Http\Request;

class ProductBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $productBatches = ProductBatch::query()
            ->withSum('stocks', 'quantity')
            ->filter($request->all())
            ->paginate($request->input('per_page', 10));
        return ProductBatchResource::collection($productBatches);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductBatchRequest $request)
    {
        $batches = ProductBatch::create($request->validated());

        return (new ProductBatchResource($batches))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductBatch $productBatch)
    {
        $productBatch->load(['product'])->loadSum('stocks', 'quantity');
        return new ProductBatchResource($productBatch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductBatchRequest $request, ProductBatch $productBatch)
    {
        $productBatch->update($request->validated());

        return new ProductBatchResource($productBatch);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductBatch $productBatch)
    {
        // Kiểm tra tồn kho
        if ($productBatch->stocks()->exists()) {
            return response()->json([
                'message' => 'This batch cannot be deleted because it still has stock in warehouses.'
            ], 422);
        }

        // Kiểm tra lịch sử giao dịch kho
        if ($productBatch->inventoryTransactions()->exists()) {
            return response()->json([
                'message' => 'This batch cannot be deleted because it has inventory transaction history.'
            ], 422);
        }

        // Kiểm tra đơn hàng
        if ($productBatch->orderItems()->exists()) {
            return response()->json([
                'message' => 'This batch cannot be deleted because it has been used in orders.'
            ], 422);
        }

        $productBatch->delete();

        return response()->json([
            'message' => 'The product batch has been deleted successfully.'
        ]);
    }
}
