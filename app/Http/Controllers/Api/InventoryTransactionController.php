<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryTransactionRequest;
use App\Http\Resources\InventoryTransactionResource;
use App\Models\InventoryTransaction;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTransactionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InventoryTransaction::class, 'inventory_transaction');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = InventoryTransaction::with(['user', 'warehouse', 'productBatch.product'])
            ->filter($request->all())
            // update*
            ->paginate($request->input('per_page', 10));

        return InventoryTransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryTransactionRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = 1;

        return DB::transaction(function () use ($data) {
            $stock = Stock::where('warehouse_id', $data['warehouse_id'])
                ->where('product_batch_id', $data['product_batch_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                if ($data['type'] === 'OUT') {
                    throw ValidationException::withMessages([
                        'product_batch_id' => ['This warehouse does not have this product batch available.']
                    ]);
                }

                $stock = new Stock([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_batch_id' => $data['product_batch_id'],
                    'quantity' => 0
                ]);
            }

            match ($data['type']) {
                'IN' => $stock->quantity += $data['quantity'],
                'OUT' => $this->handleOut($stock, $data['quantity']), // Hàm phụ check đủ hàng
                'ADJUST' => $stock->quantity = $data['quantity'],
            };

            $stock->save();

            $transaction = InventoryTransaction::create($data);

            return new InventoryTransactionResource($transaction->load(['user', 'warehouse', 'productBatch.product']));
        });
    }

    private function handleOut($stock, $quantity)
    {
        if ($stock->quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ["Insufficient stock: available {$stock->quantity}, requested {$quantity}."]
            ]);
        }
        $stock->quantity -= $quantity;
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryTransaction $inventoryTransaction)
    {
        $inventoryTransaction->load(['user', 'warehouse', 'productBatch.product']);

        return new InventoryTransactionResource($inventoryTransaction);
    }
}
