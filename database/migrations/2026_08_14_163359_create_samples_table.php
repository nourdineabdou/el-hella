<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
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

            $table->text('observation')->nullable();

            $table->timestamp('given_at');

            $table->timestamps();

            $table->index(['distributor_id', 'given_at']);
            $table->index(['shop_id', 'given_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
