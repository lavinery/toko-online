<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        $admin = User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@toko.com',
            'password' => Hash::make('password'),
            'phone' => '08123456789',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Customer Sample
        $customer = User::create([
            'name' => 'John Doe',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'phone' => '08987654321',
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Sample Address
        UserAddress::create([
            'user_id' => $customer->id,
            'label' => 'rumah',
            'name' => 'John Doe',
            'phone' => '08987654321',
            'address' => 'Jl. Merdeka No. 123, RT 01/02',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'subdistrict' => 'Kebayoran Baru',
            'postal_code' => '12180',
            'province_id' => 6, // DKI Jakarta di RajaOngkir
            'city_id' => 151, // Jakarta Selatan
            'subdistrict_id' => 1234,
            'is_default' => true,
        ]);
    }
}
