<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Target dana yang ingin dihimpun (funding target/raise), sejajar dengan
            // valuation & collected_funds (integer). Berbeda makna dari valuation
            // (nilai perusahaan) dan collected_funds (dana yang sudah terkumpul).
            $table->integer('funding_target')->default(0)->after('valuation');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('funding_target');
        });
    }
};
