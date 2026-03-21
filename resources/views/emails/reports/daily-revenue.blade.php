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
        .heading-primary { background-color: #ebf8ff; color: #2b6cb0; border-bottom: 1px solid #bee3f8; }
        .heading-neutral { background-color: #f8fafc; color: #4a5568; border-bottom: 1px solid #e2e8f0; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #f8fafc; color: #4a5568; font-size: 13px; font-weight: 600; text-transform: uppercase; padding: 12px 15px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        
        .product-name { font-weight: 600; color: #2d3748; margin-bottom: 4px; display: block; }
        .sku-text { font-size: 12px; color: #718096; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-align: center; }
        .badge-neutral { background-color: #edf2f7; color: #4a5568; }
        
        .footer { background-color: #f7fafc; padding: 20px 30px; font-size: 12px; color: #a0aec0; text-align: center; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- HEADER -->
        <div class="email-header">
            <h2>Báo Cáo Chốt Ca Cuối Ngày</h2>
            <span style="font-size: 13px; opacity: 0.8;">Ngày giao dịch: {{ $date->format('d/m/Y') }}</span>
        </div>

        <div class="email-body">
            <p style="margin-top: 0;">Kính gửi Ban Quản lý,</p>
            <p>Hệ thống xin gửi báo cáo tổng hợp kết quả kinh doanh và tình hình xuất kho trong ngày hôm nay. Dưới đây là các số liệu chi tiết:</p>

            <!-- DOANH THU HIGHLIGHT -->
            <div class="alert-section">
                <div class="alert-heading heading-primary">
                    💰 Tổng Doanh Thu Trong Ngày
                </div>
                <div style="padding: 30px 20px; text-align: center; background-color: #ffffff;">
                    <p style="font-size: 36px; font-weight: bold; color: #2b6cb0; margin: 0;">
                        {{ number_format($totalRevenue, 0, ',', '.') }} VNĐ
                    </p>
                </div>
            </div>

            <!-- BẢNG CHI TIẾT SỐ LIỆU -->
            <div class="alert-section">
                <div class="alert-heading heading-neutral">
                    📊 Thống kê hoạt động
                </div>
                <table>
                    <thead>
                        <tr>
                            <th width="60%">Chỉ tiêu</th>
                            <th width="40%" style="text-align: right;">Kết quả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="product-name">🛒 Tổng số đơn hàng</span>
                                <span class="sku-text">Số lượng đơn hàng ở trạng thái đã hoàn tất</span>
                            </td>
                            <td style="text-align: right;">
                                <span class="badge badge-neutral">{{ number_format($totalOrders) }} đơn</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="product-name">📦 Tổng sản phẩm bán ra</span>
                                <span class="sku-text">Số lượng mặt hàng đã xuất kho thành công</span>
                            </td>
                            <td style="text-align: right;">
                                <span class="badge badge-neutral">{{ number_format($totalProductsSold) }} SP</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="product-name">📅 Thời gian chốt dữ liệu</span>
                                <span class="sku-text">Hệ thống tổng hợp tự động vào cuối ngày</span>
                            </td>
                            <td style="text-align: right;">
                                <strong>23:59:59</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p style="margin-bottom: 0; font-size: 13px; color: #718096; font-style: italic;">
                * Dữ liệu này cũng đã được tự động lưu vào hệ thống cơ sở dữ liệu (Bảng Daily Reports) để đối soát về sau.
            </p>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p style="margin: 0;">Email này được gửi hoàn toàn tự động bởi Hệ thống Quản lý Kho Mỹ Phẩm.<br>
            © {{ date('Y') }} Cosmetics Inventory System.</p>
        </div>
    </div>
</body>
</html>