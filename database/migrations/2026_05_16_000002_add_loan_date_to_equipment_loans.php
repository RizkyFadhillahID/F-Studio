<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            // loan_date: kapan peralatan mulai dipinjam (untuk cek konflik jadwal)
            $table->date('loan_date')->nullable()->after('booking_id');
            // customer_name & customer_phone: siapa yang meminjam (untuk portal resepsionis)
            $table->string('customer_name', 200)->nullable()->after('loan_code');
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->dropColumn(['loan_date', 'customer_name', 'customer_phone']);
        });
    }
};
