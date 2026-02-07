<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::query()
            // 1. Đếm số dòng trong bảng stocks (-> stocks_count)
            ->withCount('stocks')

            // 2. Tính tổng cột quantity (-> stocks_sum_quantity)
            ->withSum('stocks', 'quantity')

            ->filter($request)
            ->paginate($request->get('per-page', 10));

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        $warehouse = Warehouse::create($request->validated());

        return (new WarehouseResource($warehouse))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse->loadCount('stocks');
        $warehouse->loadSum('stocks', 'quantity');

        $warehouse->load(['stocks.productBatch.product']);

        return new WarehouseResource($warehouse);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return new WarehouseResource($warehouse);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $hasInventory = $warehouse->stocks()
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasInventory) {
            return response()->json([
                'message' => 'This warehouse cannot be deleted because it still has inventory. Please transfer or remove all items before deleting it.',
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'message' => 'The warehouse has been deleted successfully.'
        ]);
    }
}
