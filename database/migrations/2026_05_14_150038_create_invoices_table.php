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
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('id')->primary(); // Format INV-YYYYMMDD-XXX
            $table->foreignId('appointment_id')->unique()->constrained('appointments');
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('cashier_id')->constrained('users');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('payment_method', ['Tunai', 'QRIS', 'Transfer', 'Debit']);
            $table->enum('status', ['Unpaid', 'Paid', 'Cancelled'])->default('Unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
