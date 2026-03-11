<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Warehouse::class, 'warehouse');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->filled('trashed')) {
            $this->authorize('viewTrash', Warehouse::class);
        }

        $warehouses = Warehouse::query()
            // 1. Đếm số dòng trong bảng stocks (-> stocks_count)
            ->withCount('stocks')

            // 2. Tính tổng cột quantity (-> stocks_sum_quantity)
            ->withSum('stocks', 'quantity')

            ->filter($request->all())
            ->paginate($request->input('per_page', 10));

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseRequest $request)
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
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
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

    public function restore($id)
    {
        $warehouse = Warehouse::withTrashed()->findOrFail($id);
        $this->authorize('restore', $warehouse);
        $warehouse->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse restored successfully.',
            'restored_id' => $warehouse->id
        ]);
    }
}
