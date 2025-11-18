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
        Schema::create('product_import_staging', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->string('category');
            $table->string('product_sku')->unique();
            $table->string('variant_color')->nullable();
            $table->string('variant_storage')->nullable();
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->string('variant_sku')->unique();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_import_staging');
    }
};
