<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('distributor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('distributor_stock_day_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->enum('movement_type', [
                'entree_stock',
                'vente',
                'echantillon',
                'retour_stock',
                'ajustement',
            ]);

            // Raw quantity as entered by the user, in whatever unit they typed.
            $table->decimal('quantity_input', 12, 3);
            $table->string('input_unit', 10);

            // Signed delta actually applied to the running balance, in the
            // product's stock unit (positive = added, negative = deducted).
            $table->decimal('quantity_stock_unit', 12, 3);

            $table->decimal('balance_before', 12, 3);
            $table->decimal('balance_after', 12, 3);

            $table->foreignId('shop_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('visit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('distribution_item_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('sample_item_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['distributor_id', 'created_at']);
            $table->index(['product_id']);
            $table->index(['distributor_stock_day_id']);
            $table->index(['movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
