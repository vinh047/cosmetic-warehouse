<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #4a5568; background-color: #f7fafc; margin: 0; padding: 20px 0; }
        .email-wrapper { max-width: 800px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .email-header { background-color: #2d3748; color: #ffffff; padding: 20px 30px; text-align: center; }
        .email-header h2 { margin: 0; font-size: 20px; font-weight: 600; }
        .email-body { padding: 30px; }
        .alert-section { margin-bottom: 30px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .alert-heading { padding: 12px 20px; margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; }
        .heading-danger { background-color: #fff5f5; color: #c53030; border-bottom: 1px solid #feb2b2; }
        .heading-warning { background-color: #fffff0; color: #b7791f; border-bottom: 1px solid #fef08a; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #f8fafc; color: #4a5568; font-size: 13px; font-weight: 600; text-transform: uppercase; padding: 12px 15px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        
        .product-name { font-weight: 600; color: #2d3748; margin-bottom: 4px; display: block; }
        .sku-text { font-size: 12px; color: #718096; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-align: center; }
        .badge-danger { background-color: #fed7d7; color: #9b2c2c; }
        .badge-warning { background-color: #fefcbf; color: #975a16; }
        .badge-neutral { background-color: #edf2f7; color: #4a5568; }

        .time-left { font-size: 12px; font-weight: bold; margin-top: 4px; display: block; }
        .time-danger { color: #e53e3e; }
        .time-expired { color: #822727; font-weight: 900; }
        
        .footer { background-color: #f7fafc; padding: 20px 30px; font-size: 12px; color: #a0aec0; text-align: center; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- HEADER -->
        <div class="email-header">
            <h2>Báo Cáo Tình Trạng Kho Mỹ Phẩm</h2>
            <span style="font-size: 13px; opacity: 0.8;">Cập nhật ngày: {{ now()->format('d/m/Y') }}</span>
        </div>

        <div class="email-body">
            <p style="margin-top: 0;">Kính gửi Ban Quản lý,</p>
            <p>Hệ thống tự động ghi nhận các mã sản phẩm cần được ưu tiên xử lý (nhập hàng hoặc đẩy bán) như sau:</p>

            <!-- BẢNG 1: LOW STOCK -->
            @if(count($lowStocks) > 0)
            <div class="alert-section">
                <div class="alert-heading heading-danger">
                    🔴 Sắp hết hàng (Low Stock Alert)
                </div>
                <table>
                    <thead>
                        <tr>
                            <th width="45%">Sản phẩm & SKU</th>
                            <th width="25%" style="text-align: center;">Tồn kho thực tế</th>
                            <th width="30%">Mức cảnh báo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStocks as $item)
                        <tr>
                            <td>
                                <span class="product-name">{{ $item->name }}</span>
                                <span class="sku-text">SKU: {{ $item->sku }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-danger">{{ $item->total_qty }} SP</span>
                            </td>
                            <td>
                                <span class="badge badge-neutral">Dưới {{ $item->stock_threshold }} SP</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- BẢNG 2: EXPIRING SOON -->
            @if(count($expiringBatches) > 0)
            <div class="alert-section">
                <div class="alert-heading heading-warning">
                    🟠 Sắp hết hạn (Expiring Soon Alert)
                </div>
                <table>
                    <thead>
                        <tr>
                            <th width="35%">Sản phẩm & SKU</th>
                            <th width="20%">Mã Lô</th>
                            <th width="25%">Ngày hết hạn</th>
                            <th width="20%" style="text-align: center;">Tồn kho lô</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringBatches as $item)
                        @php
                            // Logic tính số ngày còn lại (hoặc đã quá hạn)
                            $daysLeft = round(now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($item->expiry_date)->startOfDay(), false));
                        @endphp
                        <tr>
                            <td>
                                <span class="product-name">{{ $item->name }}</span>
                                <span class="sku-text">SKU: {{ $item->sku }}</span><br>
                                <span class="sku-text" style="color:#b7791f"><i>Cài đặt báo trước: {{ $item->expiry_threshold_days }} ngày</i></span>
                            </td>
                            <td><strong>{{ $item->batch_code }}</strong></td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') }}
                                
                                <!-- Hiển thị đếm ngược số ngày -->
                                @if($daysLeft > 0)
                                    <span class="time-left time-danger">(Còn {{ $daysLeft }} ngày)</span>
                                @elseif($daysLeft == 0)
                                    <span class="time-left time-expired">(HẾT HẠN HÔM NAY)</span>
                                @else
                                    <span class="time-left time-expired">(ĐÃ QUÁ HẠN {{ abs($daysLeft) }} NGÀY)</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-warning">{{ $item->quantity }} SP</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            
            <p style="margin-bottom: 0;">Vui lòng đăng nhập vào hệ thống phần mềm để xem chi tiết và lên kế hoạch xử lý kịp thời.</p>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p style="margin: 0;">Email này được gửi tự động từ Hệ thống Quản lý Kho Mỹ Phẩm.<br>
            Bạn nhận được email này vì tài khoản của bạn đang được cấp quyền Quản trị viên (Admin/Manager).<br>
            <i>Để tránh làm phiền, các thông báo này sẽ chỉ được lặp lại sau 24h (với tồn kho) và 7 ngày (với hạn sử dụng).</i></p>
        </div>
    </div>
</body>
</html>