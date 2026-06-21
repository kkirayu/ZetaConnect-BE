<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cek & Buat tabel e_receipts jika belum ada
        if (!Schema::hasTable('e_receipts')) {
            Schema::create('e_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
                $table->text('doctor_instructions')->nullable();
                $table->enum('status', ['Pending', 'Completed'])->default('Pending');
                $table->timestamps();
            });
        }

        // 2. Cek & Buat tabel e_receipt_items (ini yang penting untuk fitur obatmu!)
        if (!Schema::hasTable('e_receipt_items')) {
            Schema::create('e_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('e_receipt_id')->constrained('e_receipts')->onDelete('cascade');
                $table->string('medicine_name');
                $table->string('dosage'); 
                $table->string('frequency'); 
                $table->integer('quantity');
                $table->timestamps();
            });
        }

        // 3. Hapus e_prescriptions lama jika timmu belum menghapusnya
        Schema::dropIfExists('e_prescriptions');
    }

    public function down(): void
    {
        Schema::dropIfExists('e_receipt_items');
        Schema::dropIfExists('e_receipts');
    }
};