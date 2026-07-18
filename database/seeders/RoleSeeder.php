<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'owner', 'guard_name' => 'web'],
            ['name' => 'produksi', 'guard_name' => 'web'],
            ['name' => 'cs', 'guard_name' => 'web'],
        ]);
    }
}
