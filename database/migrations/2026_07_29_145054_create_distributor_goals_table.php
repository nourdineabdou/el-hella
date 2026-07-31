<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_goals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('distributor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('goal_date');

            $table->unsignedInteger('target_visits')->default(0);
            $table->unsignedInteger('target_distributions')->default(0);

            $table->decimal('target_quantity', 12, 3)->default(0);

            $table->text('observation')->nullable();

            $table->timestamps();

            $table->unique([
                'distributor_id',
                'goal_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_goals');
    }
};
