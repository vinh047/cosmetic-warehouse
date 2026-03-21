<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f7f6; margin: 0; padding: 20px 0; }
        .email-wrapper { max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .email-header { background-color: #107c41; color: #ffffff; padding: 25px 30px; text-align: center; border-bottom: 4px solid #0b5e31; }
        .email-header h2 { margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 0.5px; }
        .email-body { padding: 30px; }
        
        .report-card { background-color: #f8f9fa; border-left: 4px solid #107c41; border-radius: 4px; padding: 20px; margin: 25px 0; }
        .report-title { font-size: 16px; font-weight: 600; color: #2d3748; margin-top: 0; margin-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px 0; font-size: 14px; border-bottom: 1px dashed #e2e8f0; }
        td:first-child { color: #718096; width: 45%; }
        td:last-child { font-weight: 600; color: #2d3748; text-align: right; }
        tr:last-child td { border-bottom: none; padding-bottom: 0; }

        .download-box { text-align: center; margin-top: 35px; margin-bottom: 25px; }
        .btn-download { display: inline-block; background-color: #107c41; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(16, 124, 65, 0.2); }
        .btn-download:hover { background-color: #0b5e31; }
        .note-text { font-size: 13px; color: #e53e3e; margin-top: 10px; }
        
        .footer { background-color: #f8fafc; padding: 20px 30px; font-size: 12px; color: #a0aec0; text-align: center; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h2>Hệ Thống Quản Lý Kho</h2>
        </div>

        <div class="email-body">
            <p style="margin-top: 0; font-size: 16px;">Kính gửi Ban Quản lý,</p>
            <p style="font-size: 15px; color: #4a5568;">Hệ thống đã hoàn tất việc trích xuất dữ liệu giao dịch kho theo yêu cầu của bạn. File báo cáo đã sẵn sàng để tải xuống.</p>

            <div class="report-card">
                <h3 class="report-title">Thông tin trích xuất</h3>
                <table>
                    <tbody>
                        <tr>
                            <td>Loại báo cáo</td>
                            <td>Lịch sử giao dịch kho</td>
                        </tr>
                        <tr>
                            <td>Kỳ báo cáo</td>
                            <td>Tháng {{ $month }} - Năm {{ $year }}</td>
                        </tr>
                        <tr>
                            <td>Định dạng tệp</td>
                            <td>Microsoft Excel (.xlsx)</td>
                        </tr>
                        <tr>
                            <td>Thời điểm hoàn thành</td>
                            <td>{{ now()->format('H:i d/m/Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- NÚT BẤM TẢI XUỐNG -->
            <div class="download-box">
                <a href="{{ $downloadUrl }}" class="btn-download">⬇ Tải Xuống Báo Cáo Excel</a>
                <p class="note-text">* Link tải xuống được bảo mật và sẽ tự động hết hạn sau 7 ngày.</p>
            </div>
            
            <p style="margin-bottom: 0; margin-top: 30px; font-size: 15px;">Trân trọng,</p>
            <p style="margin-top: 5px; font-weight: 600; font-size: 15px;">Đội ngũ Quản trị Hệ thống</p>
        </div>

        <div class="footer">
            <p style="margin: 0; padding-bottom: 8px;"><strong>CẢNH BÁO BẢO MẬT:</strong> Link tải này chứa Token bảo mật riêng biệt. Vui lòng không chuyển tiếp email này cho người ngoài tổ chức.</p>
            <p style="margin: 0;">Email này được tự động gửi từ hệ thống Logistics & Inventory.</p>
        </div>
    </div>
</body>
</html>