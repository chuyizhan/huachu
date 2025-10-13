<?php

namespace Database\Seeders;

use App\Models\RechargePackage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RechargePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => '入门套餐',
                'amount' => 10,
                'bonus' => 0,
                'description' => '适合新手尝试',
                'sort_order' => 1,
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'name' => '超值套餐',
                'amount' => 50,
                'bonus' => 5,
                'description' => '赠送5金币',
                'sort_order' => 2,
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'name' => '推荐套餐',
                'amount' => 100,
                'bonus' => 15,
                'description' => '赠送15金币，性价比最高',
                'sort_order' => 3,
                'is_active' => true,
                'is_popular' => true,
            ],
            [
                'name' => '热门套餐',
                'amount' => 200,
                'bonus' => 40,
                'description' => '赠送40金币',
                'sort_order' => 4,
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'name' => '豪华套餐',
                'amount' => 500,
                'bonus' => 125,
                'description' => '赠送125金币',
                'sort_order' => 5,
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'name' => '至尊套餐',
                'amount' => 1000,
                'bonus' => 300,
                'description' => '赠送300金币',
                'sort_order' => 6,
                'is_active' => true,
                'is_popular' => false,
            ],
        ];

        foreach ($packages as $package) {
            RechargePackage::create($package);
        }
    }
}
