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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('pet_id')->constrained('pets');
            $table->foreignId('service_id')->constrained('services');
            $table->enum('booking_type', ['Online', 'Walk-in']);
            $table->date('schedule_date');
            $table->time('schedule_time');
            $table->text('initial_complaint');
            $table->string('queue_number');
            $table->enum('status', ['Menunggu', 'Disetujui', 'Dalam Periksa', 'Selesai', 'Batal'])->default('Menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
