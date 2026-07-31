<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();

            $table->string('shop_number')->unique();
            $table->string('name');
            $table->string('owner_name');
            $table->string('phone', 30)->nullable();

            $table->string('wilaya')->nullable();
            $table->string('moughataa')->nullable();
            $table->string('district')->nullable();
            $table->text('address')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamp('location_updated_at')->nullable();

            $table->enum('location_source', [
                'admin',
                'shop_owner',
                'distributor',
            ])->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['latitude', 'longitude']);
            $table->index('name');
            $table->index('owner_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
