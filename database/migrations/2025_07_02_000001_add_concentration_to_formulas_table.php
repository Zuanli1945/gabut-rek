<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->string('jenis_konsentrasi')->nullable()->after('deskripsi');
            $table->unsignedInteger('volume_botol_ml')->nullable()->after('jenis_konsentrasi');
        });
    }

    public function down(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->dropColumn(['jenis_konsentrasi', 'volume_botol_ml']);
        });
    }
};
