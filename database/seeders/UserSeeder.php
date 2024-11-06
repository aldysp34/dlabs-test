<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'age' => 30,
            'membership_status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $user->roles()->attach(2); 
    }
}
