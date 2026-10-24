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
        Schema::create('product_type_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_type_id')->constrained('product_types')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('code', 120);
            $table->unsignedTinyInteger('level_count')->default(1); // 1 = 1-level, 2 = 2-level
            $table->timestamps();

            $table->unique(['product_type_id', 'code'], 'ptv_type_code_unique');
        });

        Schema::create('product_type_variant_axes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_type_variant_id')->constrained('product_type_variants')->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->default(1); // 1 = Variant Group / Level 1, 2 = Final Variant / Level 2
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_type_variant_id', 'attribute_id'], 'ptva_var_attr_unique');
            $table->index(['product_type_variant_id', 'level', 'position'], 'ptva_var_lvl_pos_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_type_variant_axes');
        Schema::dropIfExists('product_type_variants');
    }
};
