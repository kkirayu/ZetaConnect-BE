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
        Schema::dropIfExists('medical_certificates');

        Schema::create('medical_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->integer('rest_duration'); // Durasi istirahat dalam hari
            $table->date('start_date');
            $table->text('additional_notes')->nullable();
            $table->string('certificate_file')->nullable(); // PDF file path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_certificates');

        Schema::create('medical_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records');
            $table->foreignId('pet_id')->constrained('pets');
            $table->foreignId('doctor_id')->constrained('users');
            $table->enum('type', ['Surat Sehat', 'Surat Rujukan', 'Keterangan Kematian']);
            $table->datetime('issued_date');
            $table->string('pdf_url');
            $table->timestamps();
        });
    }
};
