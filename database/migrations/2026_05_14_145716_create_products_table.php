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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Obat', 'Vaksin', 'Makanan', 'Aksesoris']);
            $table->decimal('base_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->integer('current_stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->date('exp_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
