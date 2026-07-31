<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('distributor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('distance', 10, 2);

            $table->decimal('allowed_distance', 10, 2)
                ->default(30);

            $table->enum('status', [
                'pending',
                'reviewed',
                'justified',
                'rejected',
            ])->default('pending');

            $table->text('distributor_justification')->nullable();
            $table->text('admin_comment')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_alerts');
    }
};
