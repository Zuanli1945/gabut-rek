<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("formula_produk", function (Blueprint $table) {
            $table->id();
            $table->foreignId("formula_id")->constrained()->cascadeOnDelete();
            $table
                ->foreignId("produk_id")
                ->constrained("produks")
                ->cascadeOnDelete();
            $table->decimal("jumlah_ml", 10, 2)->default(0);
            $table->decimal("persentase_komposisi", 5, 2)->default(0);
            $table->timestamps();

            $table->unique(["formula_id", "produk_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("formula_produk");
    }
};
