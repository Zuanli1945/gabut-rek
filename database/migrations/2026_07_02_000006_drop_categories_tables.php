<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('material_category');
        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        // Tables dropped intentionally — no rollback data to restore.
    }
};
