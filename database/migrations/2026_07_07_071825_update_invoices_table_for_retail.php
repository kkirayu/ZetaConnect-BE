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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->nullable()->change();
            $table->unsignedBigInteger('owner_id')->nullable()->change();
            $table->string('client_name')->nullable()->after('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->nullable(false)->change();
            $table->unsignedBigInteger('owner_id')->nullable(false)->change();
            $table->dropColumn('client_name');
        });
    }
};
