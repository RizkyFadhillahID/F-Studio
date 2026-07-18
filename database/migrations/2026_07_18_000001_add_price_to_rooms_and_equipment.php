<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('price_per_hour', 12, 2)->default(50000)->after('capacity');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->decimal('price_per_day', 12, 2)->default(15000)->after('quantity_available');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('price_per_hour');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('price_per_day');
        });
    }
};
