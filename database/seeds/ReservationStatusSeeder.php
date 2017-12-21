<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        DB::table('reservation_status')->insert([
            'name' => 'Zahtijev poslan',
            'Description' => 'Zahtjev za rezervaciju je poslan administratoru',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Odobrena',
            'Description' => 'Rezervacija je odobrena',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Odbijena',
            'Description' => 'Rezervacija je odbijena',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Stavke vraćene',
            'Description' => 'Sve stavke vraćene',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Otkazana',
            'Description' => 'Korisnik je otkazao',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        
    }
}
