<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tạo bảng riêng để quản lý cảnh báo, giúp bảng products luôn "sạch"
        Schema::create('product_alerts', function (Blueprint $table) {
            $table->id();
            // Quan hệ 1-1 với bảng products
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            
            // Cấu hình ngưỡng cảnh báo
            $table->integer('stock_threshold')->default(10);
            $table->integer('expiry_threshold_days')->default(90);
            
            // Lưu thời gian gửi cảnh báo gần nhất để chống spam (Đơn giản hóa, không cần bảng log lịch sử)
            $table->timestamp('last_stock_alert_at')->nullable();
            $table->timestamp('last_expiry_alert_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_alerts');
    }
};