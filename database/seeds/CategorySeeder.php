<?php

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types')->insert([
            'label' => 'B',
            'description' => 'Board'
        ]);
        DB::table('types')->insert([
            'label' => 'C',
            'description' => 'Communication Module'
        ]);
        DB::table('types')->insert([
            'label' => 'D',
            'description' => 'Device'
        ]);
        DB::table('types')->insert([
            'label' => 'S',
            'description' => 'Sensor'
        ]);
        DB::table('types')->insert([
            'label' => 'ST',
            'description' => 'Set'
        ]);
    }
}
