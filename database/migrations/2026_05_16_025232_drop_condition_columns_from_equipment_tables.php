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
        Schema::table('equipment', function (Blueprint $table) {
            // Index [condition, is_active] harus dilepas dulu sebelum kolomnya
            // dihapus (SQLite menolak drop kolom yang masih diindeks).
            $table->dropIndex(['condition', 'is_active']);
            $table->dropColumn('condition');
        });

        Schema::table('equipment_loan_items', function (Blueprint $table) {
            $table->dropColumn(['condition_on_loan', 'condition_on_return', 'condition_return_breakdown']);
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('condition', 20)->default('good')->after('quantity_available');
        });

        Schema::table('equipment_loan_items', function (Blueprint $table) {
            $table->string('condition_on_loan', 20)->nullable()->after('quantity');
            $table->string('condition_on_return', 20)->nullable()->after('condition_on_loan');
            $table->json('condition_return_breakdown')->nullable()->after('condition_on_return');
        });
    }
};
