<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity_total')->default(1);
            $table->unsignedInteger('quantity_available')->default(1);
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'maintenance']);
            $table->string('location', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
            $table->index('category_id');
            $table->index(['condition', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
