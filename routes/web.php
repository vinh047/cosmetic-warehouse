<?php

use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview-mail', function () {
    $order = Order::first(); 
    return new OrderConfirmationMail($order);
});

Route::get('/download-report/{fileName}', function ($fileName) {
    
    $path = "reports/" . $fileName;

    if (!Storage::disk('local')->exists($path)) {
        abort(404, 'File báo cáo không tồn tại hoặc đã bị xóa.');
    }
    return Storage::disk('local')->download($path);
})->name('report.download')->middleware('signed');