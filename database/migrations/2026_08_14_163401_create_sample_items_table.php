<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sample_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            // Raw quantity as entered: grams for kg products, native unit otherwise.
            $table->decimal('quantity_input', 12, 3);
            $table->string('input_unit', 10);

            // Same quantity converted into the product's stock unit (kg for
            // kg products, unchanged otherwise) — this is what's deducted.
            $table->decimal('quantity_stock_unit', 12, 3);

            $table->timestamps();

            $table->unique(['sample_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_items');
    }
};
