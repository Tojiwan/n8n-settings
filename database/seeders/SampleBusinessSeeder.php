<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class SampleBusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = [
            [
                'name' => 'Tigernethost Demo',
                'name-slug' => 'tigernethost-demo',
                'email' => 'demo@tigernethost.com',
                'phone' => '09175100074',
            ],
            [
                'name' => 'LionTech Solutions',
                'name-slug' => 'liontech-solutions',
                'email' => 'info@liontech.com',
                'phone' => '09171234567',
            ],
            [
                'name' => 'PandaSoft Innovations',
                'name-slug' => 'pandasoft-innovations',
                'email' => 'contact@pandasoft.com',
                'phone' => '09221234567',
            ],
            [
                'name' => 'KoalaWorks Co.',
                'name-slug' => 'koalaworks-co',
                'email' => 'support@koalaworks.co',
                'phone' => '09189998877',
            ],
            [
                'name' => 'Falcon Enterprises',
                'name-slug' => 'falcon-enterprises',
                'email' => 'admin@falconent.com',
                'phone' => '09335554422',
            ],
        ];

        foreach ($businesses as $business) {
            Business::create($business);
        }
    }
}
