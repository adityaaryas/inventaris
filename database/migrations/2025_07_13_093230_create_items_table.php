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
        Schema::create('items', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->integer('stock')->default(0);
        $table->string('unit')->nullable(); // pcs, liter, dll
        $table->integer('min_stock')->default(0);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
