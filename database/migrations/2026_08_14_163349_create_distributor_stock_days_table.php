<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_stock_days', function (Blueprint $table) {
            $table->id();

            $table->foreignId('distributor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('stock_date');

            // Null while the day is open; set once the distributor closes it.
            $table->timestamp('closed_at')->nullable();

            $table->text('admin_note')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['distributor_id', 'stock_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_stock_days');
    }
};
