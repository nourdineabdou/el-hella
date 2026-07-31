<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('distributor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('shop_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('total_quantity', 12, 3)->default(0);

            $table->enum('gps_status', [
                'valid',
                'outside_area',
                'unknown',
            ])->default('unknown');

            $table->text('observation')->nullable();

            $table->timestamp('distributed_at');

            $table->timestamps();

            $table->index(['distributor_id', 'distributed_at']);
            $table->index(['shop_id', 'distributed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
