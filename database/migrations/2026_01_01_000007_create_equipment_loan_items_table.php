<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_loan_id')->constrained('equipment_loans')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('condition_on_loan', ['excellent', 'good', 'fair', 'poor', 'maintenance']);
            $table->enum('condition_on_return', ['excellent', 'good', 'fair', 'poor', 'maintenance'])->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('equipment_loan_id');
            $table->index('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_loan_items');
    }
};
