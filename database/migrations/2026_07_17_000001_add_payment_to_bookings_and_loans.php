<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pembayaran simulasi untuk booking ruangan & peminjaman alat.
        // Bukan integrasi payment gateway sungguhan — hanya menandai status
        // 'paid'/'unpaid', metode terpilih, nominal, dan waktu bayar.
        foreach (['bookings', 'equipment_loans'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('payment_status', 20)->default('unpaid')->after('status');
                $t->string('payment_method', 30)->nullable()->after('payment_status');
                $t->decimal('amount', 12, 2)->default(0)->after('payment_method');
                $t->timestamp('paid_at')->nullable()->after('amount');
            });
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'equipment_loans'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['payment_status', 'payment_method', 'amount', 'paid_at']);
            });
        }
    }
};
