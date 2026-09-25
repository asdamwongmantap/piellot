<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

         User::updateOrCreate(
            ['email' => 'msilena122@gmail.com'],
            [
                'name' => 'Lena MSI',
                'role' => 'ADMIN',
                'company_id' => null,
                'status' => 'ACTIVE',
                'password' => bcrypt('lenamsi26@'),
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'asdam@gmail.com'],
            [
                'name' => 'Asdam',
                'role' => 'ADMIN',
                'company_id' => null,
                'status' => 'ACTIVE',
                'password' => bcrypt('admin'),
            ]
        );
        
        if (Vehicle::count() > 0) {
            return;
        }

        Vehicle::insert([
            ['plate' => 'B 9021 TRK', 'type' => 'Truk Engkel', 'capacity_ton' => 3, 'status' => 'AVAILABLE', 'created_at' => now(), 'updated_at' => now()],
            ['plate' => 'B 9124 TRK', 'type' => 'Truk Colt Diesel', 'capacity_ton' => 3, 'status' => 'AVAILABLE', 'created_at' => now(), 'updated_at' => now()],
            ['plate' => 'B 9330 TRK', 'type' => 'Truk Engkel', 'capacity_ton' => 2, 'status' => 'AVAILABLE', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
