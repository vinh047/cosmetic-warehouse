<?php

use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Mail\OrderConfirmationMail;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview-mail', function () {
    $order = Order::first(); // Lấy đại một đơn hàng có sẵn trong DB để test
    return new OrderConfirmationMail($order);
});