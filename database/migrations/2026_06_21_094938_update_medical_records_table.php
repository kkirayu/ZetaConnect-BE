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
        Schema::table('medical_records', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_records', 'diagnosis_dictionary_id')) {
                $table->foreignId('diagnosis_dictionary_id')
                      ->after('doctor_id')
                      ->nullable()
                      ->constrained('diagnosis_dictionary')
                      ->onDelete('set null');
            }

            if (Schema::hasColumn('medical_records', 'assessment')) {
                $table->dropColumn('assessment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_records', 'assessment')) {
                $table->string('assessment')->nullable();
            }

            if (Schema::hasColumn('medical_records', 'diagnosis_dictionary_id')) {
                $table->dropForeign(['diagnosis_dictionary_id']);
                $table->dropColumn('diagnosis_dictionary_id');
            }
        });
    }
};