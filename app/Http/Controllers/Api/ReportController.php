<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateExcelReportJob;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function exportInventory(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020',
        ]);

        $month = $request->month;
        $year = $request->year ?? now()->year; // Nếu không truyền năm thì lấy năm hiện tại
        
        // Chú ý: Ở hệ thống thực tế bạn sẽ lấy user đang login: $request->user()->id
        $managerId = $request->user()->id ?? 1; // Số 1 là fix cứng để test nếu bạn chưa làm Auth

        // 2. Ném Job vào Queue
        GenerateExcelReportJob::dispatch($month, $year, $managerId);

        // 3. Trả về Response ngay lập tức (Chưa tới 0.1 giây)
        return response()->json([
            'success' => true,
            'message' => 'Hệ thống đang tạo báo cáo. File Excel sẽ được gửi vào email của bạn sau ít phút.',
            'data' => [
                'month' => $month,
                'year' => $year
            ]
        ], 202); // HTTP 202: Accepted (Đã tiếp nhận yêu cầu và đang xử lý)
    }
}
