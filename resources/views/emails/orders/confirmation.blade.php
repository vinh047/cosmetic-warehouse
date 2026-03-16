<x-mail::message>
# Xác nhận đơn hàng thành công!

Chào **{{ $order->customer_name }}**, cảm ơn bạn đã mua hàng tại cửa hàng chúng tôi.

<x-mail::button :url="config('app.url') . '/orders/' . $order->id">
Xem chi tiết đơn hàng
</x-mail::button>

Cảm ơn bạn,<br>
Đội ngũ {{ config('app.name') }}
</x-mail::message>