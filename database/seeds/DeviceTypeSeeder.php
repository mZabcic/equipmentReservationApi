<?php

use Illuminate\Database\Seeder;

class DeviceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('device_type')->insert([
            'label' => 'A',
            'description' => 'Arduino'
        ]);
        DB::table('device_type')->insert([
            'label' => 'O',
            'description' => 'Other'
        ]);
        DB::table('device_type')->insert([
            'label' => 'RP',
            'description' => 'Rasberry Pi'
        ]);
        DB::table('device_type')->insert([
            'label' => 'W',
            'description' => 'Waspmote'
        ]);
    }
}
