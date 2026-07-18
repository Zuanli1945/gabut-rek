<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'owner@venturaofscent.com'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
            ]
        );

        $role = DB::table('roles')->where('name', 'owner')->first();
        if ($role) {
            DB::table('role_user')->updateOrInsert(
                ['role_id' => $role->id, 'user_id' => $user->id],
            );
        }
    }
}
