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
        // Tabel Data Dokter
        Schema::create('doctors', function (Blueprint $table) {
            $table->id('doctor_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // Tabel Jadwal Praktik Dokter 
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->foreignId('doctor_id')->references('doctor_id')->on('doctors')->onDelete('cascade');
            $table->enum('hari_praktik', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->enum('sesi_praktik', ['Sesi 1', 'Sesi 2', 'Sesi 3', 'Sesi 4', 'Sesi 5', 'Sesi 6', 'Sesi 7', 'Sesi 8']);
            $table->timestamps();
        });
    }
};


