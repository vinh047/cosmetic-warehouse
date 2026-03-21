<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InventoryTransactionExport implements 
    FromQuery, 
    WithHeadings, 
    WithMapping, 
    ShouldAutoSize, 
    WithStyles, 
    WithColumnFormatting,
    WithEvents
{
    protected $month;
    protected $year;

    // Thêm biến tĩnh để tự động cộng dồn bằng PHP (Tránh lỗi 0 đồng của hàm SUM trong Excel)
    private $totalQuantity = 0;
    private $totalValue = 0;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * 1. LẤY DỮ LIỆU TỪ DB (Chunking)
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
                'products.price', // Vẫn lấy price để tính Tổng giá trị
                'users.name as user_name',
                'inventory_transactions.reference_type',
                'inventory_transactions.reference_id'
            )
            ->orderBy('inventory_transactions.created_at', 'desc');
    }

    /**
     * 2. MAP DỮ LIỆU TỪNG DÒNG VÀ CỘNG DỒN
     */
    public function map($transaction): array
    {
        $typeLabel = '';
        $quantity = (int) $transaction->quantity;
        
        switch ($transaction->type) {
            case 'IN':
                $typeLabel = 'Nhập kho';
                $quantity = abs($quantity);
                break;
            case 'OUT':
                $typeLabel = 'Xuất kho';
                $quantity = -abs($quantity);
                break;
            case 'ADJUST':
                $typeLabel = 'Điều chỉnh';
                break;
        }

        // Tính tổng giá trị (Số lượng tuyệt đối * Giá hiện tại)
        $totalPrice = abs($transaction->quantity) * (float) $transaction->price;

        // CỘNG DỒN GIÁ TRỊ VÀO BIẾN CỦA CLASS
        $this->totalQuantity += $quantity;
        $this->totalValue += $totalPrice;

        return [
            $transaction->id,
            Date::dateTimeToExcel(Carbon::parse($transaction->created_at)),
            $typeLabel,
            $transaction->product_name,
            $transaction->sku,
            $transaction->batch_code,
            $transaction->warehouse_name,
            $quantity,                           // Cột H: Số lượng
            $totalPrice,                         // Cột I: Tổng giá trị (Đã bỏ Đơn giá)
            $transaction->user_name,             // Cột J
            $transaction->reference_type ? ($transaction->reference_type . ' #' . $transaction->reference_id) : 'N/A', // Cột K
        ];
    }

    /**
     * 3. KHAI BÁO TIÊU ĐỀ
     */
    public function headings(): array
    {
        return [
            ['BÁO CÁO LỊCH SỬ GIAO DỊCH KHO'],
            ['Kỳ báo cáo: Tháng ' . $this->month . ' Năm ' . $this->year],
            ['Ngày trích xuất: ' . now()->format('d/m/Y H:i:s')],
            [], // Dòng trống
            [
                'Mã GD',
                'Ngày giờ',
                'Loại GD',
                'Tên sản phẩm',
                'SKU',
                'Mã lô',
                'Kho hàng',
                'Số lượng',
                'Tổng giá trị (VNĐ)', 
                'Người thực hiện',
                'Tham chiếu',
            ] 
        ];
    }

    /**
     * 4. ĐỊNH DẠNG FORMAT CỘT
     */
    public function columnFormats(): array
    {
        return [
            'B' => 'dd/mm/yyyy hh:mm',
            'H' => '#,##0',                      // Số lượng
            'I' => '#,##0',                      // Tổng giá trị VNĐ
        ];
    }

    /**
     * 5. LÀM ĐẸP TIÊU ĐỀ
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF107C41']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A5:K5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF107C41']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->freezePane('A6');
        $sheet->getStyle('A6:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C6:C' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * 6. SỰ KIỆN: THÊM DÒNG TỔNG CỘNG Ở CUỐI CÙNG
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $totalRow = $highestRow + 1;

                // 1. Ghi chữ "TỔNG CỘNG:" và gộp các ô
                $sheet->setCellValue('A' . $totalRow, 'TỔNG CỘNG:');
                $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // 2. Điền TRỰC TIẾP con số từ PHP (Không dùng SUM nữa để tránh lỗi Protected View)
                $sheet->setCellValue('H' . $totalRow, $this->totalQuantity);
                $sheet->setCellValue('I' . $totalRow, $this->totalValue);

                // 3. Làm nổi bật dòng Tổng Cộng
                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFB30000'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFFFF2CC'], 
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}