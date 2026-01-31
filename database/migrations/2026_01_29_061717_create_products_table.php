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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 100)->unique();

            $table->foreignId('category_id')
                ->constrained('categories');

            $table->foreignId('brand_id')
                ->constrained('brands');

            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            // Bật / tắt sử dụng
            $table->boolean('is_active')->default(true);

            // Soft delete
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
