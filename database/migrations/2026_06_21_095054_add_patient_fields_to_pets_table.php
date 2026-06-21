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
        Schema::table('pets', function (Blueprint $table) {
            if (!Schema::hasColumn('pets', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('allergies');
            }

            if (!Schema::hasColumn('pets', 'subjective_complaint')) {
                $afterColumn = Schema::hasColumn('pets', 'weight') ? 'weight' : 'allergies';
                
                $table->text('subjective_complaint')->nullable()->after($afterColumn);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            if (Schema::hasColumn('pets', 'weight')) {
                $table->dropColumn('weight');
            }
            if (Schema::hasColumn('pets', 'subjective_complaint')) {
                $table->dropColumn('subjective_complaint');
            }
        });
    }
};