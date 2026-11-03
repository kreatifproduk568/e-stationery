<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@smartstationery.test',
                'password' => 'Password123!',
            ],
            [
                'name' => 'Staff Store',
                'email' => 'staff@smartstationery.test',
                'password' => 'Password123!',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                [
                    'email' => $data['email'],
                ],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                ]
            );
        }


    }
}
