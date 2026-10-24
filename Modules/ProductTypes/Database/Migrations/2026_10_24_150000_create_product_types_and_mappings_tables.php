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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 120)->unique();
            $table->timestamps();
        });

        Schema::create('product_type_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->constrained('product_types')->cascadeOnDelete();
            $table->foreignId('attribute_group_id')->constrained('attribute_groups')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_type_id', 'attribute_group_id'], 'ptg_type_group_unique');
            $table->index(['product_type_id', 'position'], 'ptg_type_pos_index');
        });

        Schema::create('product_type_group_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_group_id')->constrained('product_type_groups')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_type_group_id', 'attribute_id'], 'ptga_group_attr_unique');
            $table->index(['product_type_group_id', 'position'], 'ptga_group_pos_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_type_group_attributes');
        Schema::dropIfExists('product_type_groups');
        Schema::dropIfExists('product_types');
    }
};
