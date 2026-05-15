<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_loan_id')->constrained('equipment_loans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id', 100)->nullable();
            $table->enum('action', ['check_in', 'check_out']);
            $table->timestamp('checked_at');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['equipment_loan_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
