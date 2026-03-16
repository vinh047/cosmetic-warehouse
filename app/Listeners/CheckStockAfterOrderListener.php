<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\InventoryAlertService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

// Dùng ShouldQueue để việc tính toán kho đưa vào Background Job, 
// không làm chậm thời gian phản hồi API của khách hàng.
class CheckStockAfterOrderListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $alertService;

    public function __construct(InventoryAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Lấy danh sách Product ID từ các Order Items (thông qua product_batch_id)
        $productIds = DB::table('order_items')
            ->join('product_batches', 'order_items.product_batch_id', '=', 'product_batches.id')
            ->where('order_items.order_id', $order->id)
            ->pluck('product_batches.product_id')
            ->toArray();

        // Gọi Service kiểm tra real-time
        if (!empty($productIds)) {
            $this->alertService->checkRealtimeLowStock($productIds);
        }
    }
}