<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed default base fare
        AppSetting::updateOrCreate(
            ['key' => 'base_fare'],
            [
                'value' => '13.00',
                'type' => 'number',
                'description' => 'The minimum fare charged for jeepney rides',
                'is_public' => true
            ]
        );

        // Seed default fare per kilometer
        AppSetting::updateOrCreate(
            ['key' => 'fare_per_km'],
            [
                'value' => '1.80',
                'type' => 'number',
                'description' => 'Additional fare charged per kilometer traveled',
                'is_public' => true
            ]
        );
    }
}
