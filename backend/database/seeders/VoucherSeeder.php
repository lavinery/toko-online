<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        // Voucher Percentage
        Voucher::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome New Customer',
            'description' => 'Diskon 10% untuk pelanggan baru',
            'type' => 'percentage',
            'value' => 10,
            'minimum_amount' => 100000,
            'maximum_discount' => 50000,
            'usage_limit' => 1000,
            'used_count' => 0,
            'usage_limit_per_customer' => 1,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
            'conditions' => [
                'new_customer_only' => true
            ]
        ]);

        // Voucher Fixed Amount
        Voucher::create([
            'code' => 'SAVE50K',
            'name' => 'Save 50K',
            'description' => 'Potongan langsung Rp 50.000',
            'type' => 'fixed_amount',
            'value' => 50000,
            'minimum_amount' => 500000,
            'maximum_discount' => null,
            'usage_limit' => 500,
            'used_count' => 0,
            'usage_limit_per_customer' => 3,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        // Free Shipping Voucher
        Voucher::create([
            'code' => 'FREESHIP',
            'name' => 'Free Shipping',
            'description' => 'Gratis ongkos kirim seluruh Indonesia',
            'type' => 'free_shipping',
            'value' => 0,
            'minimum_amount' => 200000,
            'maximum_discount' => 100000,
            'usage_limit' => null, // unlimited
            'used_count' => 0,
            'usage_limit_per_customer' => 5,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        // Flash Sale Voucher
        Voucher::create([
            'code' => 'FLASH20',
            'name' => 'Flash Sale 20%',
            'description' => 'Flash sale diskon 20% terbatas!',
            'type' => 'percentage',
            'value' => 20,
            'minimum_amount' => 300000,
            'maximum_discount' => 100000,
            'usage_limit' => 100,
            'used_count' => 0,
            'usage_limit_per_customer' => 1,
            'starts_at' => now(),
            'expires_at' => now()->addDays(7),
            'is_active' => true,
            'conditions' => [
                'categories' => [1], // hanya kategori Fashion
                'time_limited' => true
            ]
        ]);
    }
}
