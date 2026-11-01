<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_connectors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('connector_code', 64);
            $table->boolean('status')->default(true);
            $table->string('sync_status', 32)->default('synced');
            $table->timestamp('synced_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'connector_code']);
            $table->index('connector_code');
            $table->index(['connector_code', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_connectors');
    }
};
