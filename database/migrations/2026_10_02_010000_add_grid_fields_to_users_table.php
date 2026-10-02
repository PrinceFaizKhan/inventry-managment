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
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete()->after('id');
                $table->string('status')->default('active')->after('password');
                $table->index(['status', 'name']);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex(['status', 'name']);
                $table->dropConstrainedForeignId('role_id');
                $table->dropColumn('status');
            });
        });
    }
};
