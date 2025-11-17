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
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);

            // vendor_id remains because ONLY vendors own products
            $table->foreignId('vendor_id')
                ->nullable()              // admin-created product (rare case)
                ->constrained('users')
                ->nullOnDelete();

            $table->string('category')->nullable();
            $table->string('sku')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'category']);
            $table->fullText(['name', 'description']);
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
