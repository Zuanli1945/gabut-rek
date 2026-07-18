<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Insert 5 standard categories
        $categories = ['Top Note', 'Middle/Heart Note', 'Base Note', 'Modifier', 'Blender'];
        $catIds = [];
        foreach ($categories as $name) {
            DB::table('categories')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $catIds[$name] = DB::table('categories')->where('name', $name)->value('id');
        }

        // Map old kategori values to new categories
        $kategoriMap = [
            'Top'    => 'Top Note',
            'Mid'    => 'Middle/Heart Note',
            'Base'   => 'Base Note',
        ];

        $inserts = [];
        foreach ($kategoriMap as $oldName => $newName) {
            $catId = $catIds[$newName];
            $materialIds = DB::table('materials')->where('kategori', $oldName)->pluck('id');
            foreach ($materialIds as $id) {
                $inserts[] = ['material_id' => $id, 'category_id' => $catId];
            }
        }

        if ($inserts) {
            DB::table('material_category')->insert($inserts);
        }

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama');
        });

        $categoryMap = [
            'Top Note'          => 'Top',
            'Middle/Heart Note' => 'Mid',
            'Base Note'         => 'Base',
        ];

        foreach ($categoryMap as $catName => $oldValue) {
            $catId = DB::table('categories')->where('name', $catName)->value('id');
            if (! $catId) continue;
            $materialIds = DB::table('material_category')
                ->where('category_id', $catId)
                ->pluck('material_id');
            DB::table('materials')
                ->whereIn('id', $materialIds)
                ->update(['kategori' => $oldValue]);
        }
    }
};
