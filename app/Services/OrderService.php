<?php


namespace App\Services;

use App\Enums\InventoryReferenceType;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $channel = $data['channel'];
            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $data['customer_name'],
                'channel'       => $channel,
                'status'        => OrderStatus::PENDING,
                'total_price' => 0, // cập nhật sau
            ]);

            $totalPrice = 0;

            foreach ($data['items'] as $item) {

                // Xử lý trường hợp đơn offline.
                if ($channel === OrderChannel::OFFLINE->value) {
                    $stock = Stock::with(['productBatch.product'])
                        ->where('warehouse_id', $data['warehouse_id'])
                        ->where('product_batch_id', $item['product_batch_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$stock || $stock->quantity < $item['quantity']) {
                        throw new Exception("Insufficient stock for batch ID {$item['product_batch_id']} at the current warehouse.");
                    }

                    $stock->decrement('quantity', $item['quantity']);
                    $this->recordTransactionAndItem($order, $stock, $item['quantity'], $userId);
                    $totalPrice += ($stock->productBatch->product->price * $item['quantity']);
                }
                // Xử lý trường hợp đơn online.
                else {
                    $productId = $item['product_id'];
                    $qtyNeeded = $item['quantity'];

                    // Tìm 1 kho có tổng tồn kho đủ đáp ứng số lượng
                    $eligibleWarehouse = Stock::select('warehouse_id', DB::raw('SUM(quantity) as total_qty'))
                        ->join('product_batches', 'stocks.product_batch_id', '=', 'product_batches.id')
                        ->where('product_batches.product_id', $productId)
                        ->groupBy('warehouse_id')
                        ->having('total_qty', '>=', $qtyNeeded)
                        ->first();

                    if (!$eligibleWarehouse) {
                        $productName = Product::find($productId)->name ?? "ID $productId";
                        throw new Exception("Product {$productName} is currently out of stock or cannot be fulfilled from a single warehouse.");
                    }

                    $warehouseId = $eligibleWarehouse->warehouse_id;

                    // Lấy các Lô hàng ở Kho đó, xếp theo Hạn sử dụng (FEFO) và Lock
                    $stocksToDeduct = Stock::with(['productBatch.product'])
                        ->join('product_batches', 'stocks.product_batch_id', '=', 'product_batches.id')
                        ->where('stocks.warehouse_id', $warehouseId)
                        ->where('product_batches.product_id', $productId)
                        ->where('stocks.quantity', '>', 0)
                        ->orderBy('product_batches.expiry_date', 'asc')
                        ->select('stocks.*') // Chỉ select data của bảng stocks
                        ->lockForUpdate()
                        ->get();

                    // Thuật toán trừ dần (Deduct Sequentially)
                    foreach ($stocksToDeduct as $stock) {
                        if ($qtyNeeded <= 0) break;

                        $takeQty = min($stock->quantity, $qtyNeeded);

                        $stock->decrement('quantity', $takeQty);
                        $this->recordTransactionAndItem($order, $stock, $takeQty, $userId);

                        $totalPrice += ($stock->productBatch->product->price * $takeQty);
                        $qtyNeeded -= $takeQty;
                    }

                    if ($qtyNeeded > 0) {
                        $productName = Product::find($productId)->name ?? "ID $productId";
                        throw new Exception("Checkout failed: The inventory for product {$productName} changed while processing your order. Please try again.");
                    }
                }
            }

            // Cập nhật tổng giá trị đơn hàng
            $order->update(['total_price' => $totalPrice]);

            return $order->load('items.productBatch.product');
        });
    }

    /**
     * Helper Method: Tách logic ghi Log và tạo OrderItem
     */
    private function recordTransactionAndItem(Order $order, Stock $stock, int $quantity, int $userId): void
    {
        // Ghi log Inventory (MorphMap tự động)
        $order->inventoryTransactions()->create([
            'product_batch_id' => $stock->product_batch_id,
            'warehouse_id'     => $stock->warehouse_id,
            'quantity'         => $quantity,
            'type'             => 'OUT',
            'user_id'          => $userId
        ]);

        // Tạo Order Item
        $order->items()->create([
            'product_batch_id' => $stock->product_batch_id,
            'quantity'         => $quantity,
            'price'            => $stock->productBatch->product->price,
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng và xử lý các nghiệp vụ liên quan (Hoàn kho)
     *
     * @param Order $order
     * @param string $newStatus
     * @param int $userId
     * @return Order
     * @throws Exception
     */
    public function updateStatus(Order $order, string $newStatus, int $userId): Order
    {
        if (in_array($order->status->value, [OrderStatus::CANCELLED->value, OrderStatus::COMPLETED->value])) {
            throw new Exception("Cannot change the status of a completed or cancelled order.");
        }

        if ($order->status->value === $newStatus) {
            return $order;
        }

        return DB::transaction(function () use ($order, $newStatus, $userId) {

            // Cập nhật trạng thái mới
            $order->update(['status' => $newStatus]);

            if ($newStatus === OrderStatus::CANCELLED->value) {
                $this->handleOrderCancellation($order, $userId);
            }

            // update*
            // Nếu là COMPLETED thì có thể kích hoạt luồng Gửi Email Cảm ơn, Cộng điểm tích lũy... 

            return $order->refresh();
        });
    }

    /**
     *  Xử lý Hoàn trả Tồn kho khi Đơn bị Hủy
     */
    private function handleOrderCancellation(Order $order, int $userId): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {

            // Lấy alias chuẩn từ cấu hình MorphMap ('order')
            $morphType = $order->getMorphClass();

            // 1. TRUY VẾT BIÊN LAI XUẤT KHO
            $outTransaction = InventoryTransaction::where('reference_type', $morphType)
                ->where('reference_id', $order->id)
                ->where('product_batch_id', $item->product_batch_id)
                ->where('type', 'OUT')
                ->first();

            if (!$outTransaction) {
                // Nếu không tìm thấy, chứng tỏ lúc tạo đơn bị lỗi không trừ kho, hoặc dữ liệu rác
                throw new Exception("Initial stock deduction record not found for product batch ID {$item->product_batch_id}.");
            }

            $warehouseId = $outTransaction->warehouse_id;

            // Cộng trả lại Tồn kho
            Stock::where('warehouse_id', $warehouseId)
                ->where('product_batch_id', $item->product_batch_id)
                ->increment('quantity', $item->quantity);

            // Ghi log Nhập kho (Hoàn trả)
            InventoryTransaction::create([
                'user_id'          => $userId,
                'product_batch_id' => $item->product_batch_id,
                'warehouse_id'     => $warehouseId,
                'type'             => 'IN',
                'quantity'         => $item->quantity,
                'reference_type'   => Order::class,
                'reference_id'     => $order->id,
            ]);
        }
    }
}
