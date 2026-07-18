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
        // SQLite has no ALTER COLUMN ... DROP NOT NULL; rebuild the table.
        // solvent_material_id is optional (a formula may exist before a
        // solvent is chosen), so the column must allow NULL.
        DB::statement('ALTER TABLE biaya_produksis RENAME TO biaya_produksis_old');

        Schema::create('biaya_produksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();
            $table->foreignId('solvent_material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->decimal('persentase_konsentrasi', 5, 2);
            $table->decimal('jumlah_batch_ml', 10, 2);
            $table->decimal('biaya_kemasan', 12, 2)->default(0);
            $table->decimal('target_margin_persen', 5, 2);
            $table->decimal('jumlah_unit_hasil', 10, 2);
            $table->decimal('cogs_per_unit', 12, 2);
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('margin_rupiah', 12, 2);
            $table->timestamps();
        });

        DB::statement('INSERT INTO biaya_produksis (id, formula_id, solvent_material_id, persentase_konsentrasi, jumlah_batch_ml, biaya_kemasan, target_margin_persen, jumlah_unit_hasil, cogs_per_unit, harga_jual, margin_rupiah, created_at, updated_at)
            SELECT id, formula_id, NULL, persentase_konsentrasi, jumlah_batch_ml, biaya_kemasan, target_margin_persen, jumlah_unit_hasil, cogs_per_unit, harga_jual, margin_rupiah, created_at, updated_at FROM biaya_produksis_old');

        Schema::drop('biaya_produksis_old');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE biaya_produksis RENAME TO biaya_produksis_old');

        Schema::create('biaya_produksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();
            $table->foreignId('solvent_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('persentase_konsentrasi', 5, 2);
            $table->decimal('jumlah_batch_ml', 10, 2);
            $table->decimal('biaya_kemasan', 12, 2)->default(0);
            $table->decimal('target_margin_persen', 5, 2);
            $table->decimal('jumlah_unit_hasil', 10, 2);
            $table->decimal('cogs_per_unit', 12, 2);
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('margin_rupiah', 12, 2);
            $table->timestamps();
        });

        DB::statement('INSERT INTO biaya_produksis (id, formula_id, solvent_material_id, persentase_konsentrasi, jumlah_batch_ml, biaya_kemasan, target_margin_persen, jumlah_unit_hasil, cogs_per_unit, harga_jual, margin_rupiah, created_at, updated_at)
            SELECT id, formula_id, solvent_material_id, persentase_konsentrasi, jumlah_batch_ml, biaya_kemasan, target_margin_persen, jumlah_unit_hasil, cogs_per_unit, harga_jual, margin_rupiah, created_at, updated_at FROM biaya_produksis_old WHERE solvent_material_id IS NOT NULL');

        Schema::drop('biaya_produksis_old');
    }
};
