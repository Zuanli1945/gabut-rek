<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_supplier', function (Blueprint $table) {
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->decimal('harga_beli', 12, 2)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->primary(['material_id', 'supplier_id']);
        });

        Schema::create('material_ifra_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20)->comment('IFRA category 1-12');
            $table->decimal('max_percent', 8, 4)->comment('Maximum % allowed in formula');
            $table->timestamps();
        });

        Schema::create('formula_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('materials_snapshot');
            $table->decimal('cost_per_ml', 12, 4)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['formula_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formula_versions');
        Schema::dropIfExists('material_ifra_limits');
        Schema::dropIfExists('material_supplier');
    }
};
