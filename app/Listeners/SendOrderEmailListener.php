<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOrderEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    
    // THÊM CÁI NÀY: Ép Job này phải chờ 5 giây mới được chạy.
    // Việc này giúp tránh đụng độ (Rate Limit) với mail cảnh báo kho chạy cùng lúc.
    public $delay = 10; 

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        try {
            // FIX LỖI 2: Đảm bảo đơn hàng này CÓ LIÊN KẾT với 1 tài khoản user (creator)
            $user = $event->order->creator; // (Hoặc $event->order->user tuỳ cách bạn đặt tên quan hệ)
            
            if (!$user || empty($user->email)) {
                // Nếu đơn offline không có email -> Huỷ job không gửi nữa, tránh báo lỗi
                Log::info('Bỏ qua gửi mail đơn hàng: Đơn hàng ' . $event->order->order_code . ' không có email người mua.');
                return;
            }

            // Tiến hành gửi mail
            Mail::to($user->email)->send(new OrderConfirmationMail($event->order));
            
        } catch (\Exception $e) {
            // Ghi lỗi ra log để dễ bắt bệnh thay vì chỉ hiện "FAIL"
            Log::error('Lỗi khi gửi mail xác nhận đơn hàng: ' . $e->getMessage());
            
            // Đẩy lỗi ra lại để Queue tự động chạy lại (retry)
            throw $e;
        }
    }
}