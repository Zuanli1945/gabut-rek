<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('kategori', ['Top', 'Mid', 'Base', 'Fixative', 'Solvent']);
            $table->enum('tipe', ['Aromachemical', 'Essential Oil', 'Absolute']);
            $table->string('scent_family'); // Citrus, Floral, Woody, etc.
            $table->decimal('harga_beli', 12, 2);
            $table->decimal('jumlah_beli', 10, 2);
            $table->enum('satuan', ['ml', 'gram']);
            $table->decimal('stock_saat_ini', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
