<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_stock_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('distributor_stock_day_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            // In the product's own unit (kg, carton, bag, unit).
            $table->decimal('received_quantity', 12, 3)->default(0);
            $table->decimal('current_quantity', 12, 3)->default(0);
            $table->decimal('returned_quantity', 12, 3)->nullable();

            $table->timestamps();

            $table->unique(['distributor_stock_day_id', 'product_id'], 'stock_items_day_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_stock_items');
    }
};
