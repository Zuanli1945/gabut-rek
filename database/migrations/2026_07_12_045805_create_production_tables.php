<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();
            $table->foreignId('formula_version_id')->nullable()->constrained('formula_versions')->nullOnDelete();
            $table->decimal('batch_volume_ml', 10, 2);
            $table->unsignedInteger('jumlah_unit')->comment('Bottles produced');
            $table->string('status')->default('planned')->comment('planned/in_progress/completed/cancelled');
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('batch_material_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_production_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_used', 10, 2);
            $table->decimal('cost', 12, 2);
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('type')->comment('in/out/adjustment');
            $table->decimal('quantity', 10, 2);
            $table->string('reference_type')->nullable()->comment('batch_production/purchase/adjustment/order');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('batch_material_usages');
        Schema::dropIfExists('batch_productions');
    }
};
