<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Quiz;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::insert([
            'login' => 'test',
            'email' => 'test@example.com',
            'password' => Hash::make('test'),
            'isAdmin' => false,
        ]);
User::insert([
            'login' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
            'isAdmin' => true,
        ]);
Quiz::factory(50)
        ->create();
    }
}
