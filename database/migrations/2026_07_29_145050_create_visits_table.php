<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('distributor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('shop_id')
                ->constrained()
                ->restrictOnDelete();

            $table->enum('visit_type', [
                'distribution',
                'without_distribution',
            ]);

            $table->enum('without_distribution_reason', [
                'shop_closed',
                'owner_absent',
                'refused',
                'sufficient_stock',
                'client_absent',
                'other',
            ])->nullable();

            $table->text('observation')->nullable();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('gps_accuracy', 10, 2)->nullable();

            $table->decimal('distance_from_shop', 10, 2)->nullable();

            $table->boolean('is_within_allowed_distance')
                ->default(false);

            $table->timestamp('visited_at');

            $table->timestamps();

            $table->index(['distributor_id', 'visited_at']);
            $table->index(['shop_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
