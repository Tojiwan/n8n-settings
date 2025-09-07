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
                'name_slug' => 'tigernethost-demo',
                'email' => 'demo@tigernethost.com',
                'mobile' => '09175100074',
            ],
            [
                'name' => 'LionTech Solutions',
                'name_slug' => 'liontech-solutions',
                'email' => 'info@liontech.com',
                'mobile' => '09171234567',
            ],
            [
                'name' => 'PandaSoft Innovations',
                'name_slug' => 'pandasoft-innovations',
                'email' => 'contact@pandasoft.com',
                'mobile' => '09221234567',
            ],
            [
                'name' => 'KoalaWorks Co.',
                'name_slug' => 'koalaworks-co',
                'email' => 'support@koalaworks.co',
                'mobile' => '09189998877',
            ],
            [
                'name' => 'Falcon Enterprises',
                'name_slug' => 'falcon-enterprises',
                'email' => 'admin@falconent.com',
                'mobile' => '09335554422',
            ],
        ];

        foreach ($businesses as $business) {
            Business::create($business);
        }
    }
}
