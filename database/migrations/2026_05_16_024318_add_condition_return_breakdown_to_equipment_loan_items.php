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
        Schema::table('equipment_loan_items', function (Blueprint $table) {
            // Breakdown kondisi per unit: {'good':2,'fair':0,'poor':1,'maintenance':0}
            $table->json('condition_return_breakdown')->nullable()->after('condition_on_return');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_loan_items', function (Blueprint $table) {
            $table->dropColumn('condition_return_breakdown');
        });
    }
};
