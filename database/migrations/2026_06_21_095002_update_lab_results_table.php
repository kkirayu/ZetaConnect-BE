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
        Schema::dropIfExists('lab_results');

        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('document_type'); // e.g., "Cek Darah", "X-Ray", "USG"
            $table->string('document_file'); // File path to PDF/JPG/PNG
            $table->decimal('file_size', 12, 2)->nullable(); // File size in MB
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_results');

        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->onDelete('cascade');
            $table->enum('test_type', ['X-Ray', 'USG', 'Darah']);
            $table->string('file_path');
            $table->timestamps();
        });
    }
};
