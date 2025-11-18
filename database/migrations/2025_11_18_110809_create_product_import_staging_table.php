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
            $table->string('product_name', 191);
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->string('category', 191)->nullable();
            $table->string('product_sku', 191)->nullable()->index();
            $table->string('variant_color', 100)->nullable();
            $table->string('variant_storage', 50)->nullable();
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->string('variant_sku', 191)->nullable()->index();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();

            // Ensure unique combination of product + variant SKU
            $table->unique(['product_sku', 'variant_sku'], 'staging_product_variant_unique');
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
