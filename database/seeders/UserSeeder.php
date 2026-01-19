<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'user@adm.test')->exists()) {
            User::create([
                'name' => 'Usuário Teste ADM',
                'email' => 'user@adm.test',
                'is_admin' => true,
                'password' => Hash::make('123456')
            ]);
        }
        if (!User::where('email', 'user@local.test')->exists()) {
            User::create([
                'name' => 'Usuário Teste Local',
                'email' => 'user@local.test',
                'password' => Hash::make('123456')
            ]);
        }
    }
}
