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
        Schema::create('inpatient_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->cascadeOnDelete();
            $table->foreignId('cage_id')->constrained('cages')->cascadeOnDelete();
            $table->dateTime('admission_date');
            $table->dateTime('estimated_discharge_date')->nullable();
            $table->dateTime('actual_discharge_date')->nullable();
            $table->enum('status', ['Dirawat', 'Pulang'])->default('Dirawat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inpatient_records');
    }
};
