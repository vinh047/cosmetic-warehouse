<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

// Dùng FromQuery thay vì FromCollection để Laravel tự động chia nhỏ (chunk) dữ liệu
// Dù có 100.000 dòng thì RAM cũng không bị đầy.
class InventoryTransactionExport implements FromQuery, WithHeadings, WithMapping
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * Truy vấn lấy dữ liệu (Chưa lấy ngay, thư viện sẽ tự động lấy từng cụm nhỏ)
     */
    public function query()
    {
        return DB::table('inventory_transactions')
            ->join('users', 'inventory_transactions.user_id', '=', 'users.id')
            ->join('warehouses', 'inventory_transactions.warehouse_id', '=', 'warehouses.id')
            ->join('product_batches', 'inventory_transactions.product_batch_id', '=', 'product_batches.id')
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->whereMonth('inventory_transactions.created_at', $this->month)
            ->whereYear('inventory_transactions.created_at', $this->year)
            ->select(
                'inventory_transactions.id',
                'inventory_transactions.created_at',
                'inventory_transactions.type',
                'products.name as product_name',
                'products.sku',
                'product_batches.batch_code',
                'warehouses.name as warehouse_name',
                'inventory_transactions.quantity',
                'users.name as user_name',
                'inventory_transactions.reference_type'
            )
            ->orderBy('inventory_transactions.created_at', 'desc');
    }

    /**
     * Map dữ liệu cho từng cột trong Excel
     */
    public function map($transaction): array
    {
        $typeLabel = match($transaction->type) {
            'IN' => 'Nhập kho',
            'OUT' => 'Xuất kho',
            'ADJUST' => 'Điều chỉnh',
            default => $transaction->type
        };

        return [
            $transaction->id,
            $transaction->created_at,
            $typeLabel,
            $transaction->product_name,
            $transaction->sku,
            $transaction->batch_code,
            $transaction->warehouse_name,
            $transaction->quantity,
            $transaction->user_name,
            $transaction->reference_type,
        ];
    }

    /**
     * Tiêu đề các cột
     */
    public function headings(): array
    {
        return [
            'Mã GD',
            'Ngày giờ',
            'Loại GD',
            'Tên sản phẩm',
            'SKU',
            'Mã lô',
            'Kho hàng',
            'Số lượng',
            'Người thực hiện',
            'Tham chiếu (Đơn hàng)',
        ];
    }
}