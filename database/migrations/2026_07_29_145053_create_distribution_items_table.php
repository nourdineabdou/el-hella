<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('distribution_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('quantity', 12, 3);

            $table->string('unit', 30)->default('kg');

            $table->timestamps();

            $table->unique([
                'distribution_id',
                'product_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_items');
    }
};
