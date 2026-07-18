<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formula_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('persentase', 5, 2); // % of concentrate
            $table->decimal('gram', 10, 2)->nullable(); // optional absolute gram
            $table->enum('note_posisi', ['top', 'mid', 'base'])->nullable();
            $table->timestamps();

            $table->unique(['formula_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formula_material');
    }
};
