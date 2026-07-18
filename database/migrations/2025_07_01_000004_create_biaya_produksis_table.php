<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_produksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();
            $table->foreignId('solvent_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('persentase_konsentrasi', 5, 2); // e.g. 18.00 for EDP
            $table->decimal('jumlah_batch_ml', 10, 2);
            $table->decimal('biaya_kemasan', 12, 2)->default(0);
            $table->decimal('target_margin_persen', 5, 2); // e.g. 60.00
            $table->decimal('jumlah_unit_hasil', 10, 2);
            $table->decimal('cogs_per_unit', 12, 2); // computed
            $table->decimal('harga_jual', 12, 2); // computed
            $table->decimal('margin_rupiah', 12, 2); // computed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_produksis');
    }
};
