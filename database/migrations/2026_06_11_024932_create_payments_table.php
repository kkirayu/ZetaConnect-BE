<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreignId('cashier_id')->constrained('users');
            $table->enum('payment_method', ['Tunai', 'QRIS', 'Transfer', 'Debit']);
            $table->decimal('amount_paid', 15, 2)->comment('Jumlah yang dibayarkan oleh pelanggan');
            $table->decimal('change_amount', 15, 2)->default(0)->comment('Kembalian kepada pelanggan');
            $table->string('reference_number')->nullable()->comment('Nomor referensi untuk Transfer/QRIS/Debit');
            $table->enum('status', ['Pending', 'Success', 'Failed', 'Refunded'])->default('Pending');
            $table->text('notes')->nullable()->comment('Catatan tambahan transaksi');
            $table->timestamp('paid_at')->nullable()->comment('Waktu pembayaran berhasil dikonfirmasi');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
