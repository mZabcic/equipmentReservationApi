<?php

use Illuminate\Database\Seeder;

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
            'name' => 'U izradi',
            'Description' => 'Trenutno se izrađuje rezervacija'
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Zahtijev poslan',
            'Description' => 'Zahtjev za rezervaciju je poslan administratoru'
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Odobrena',
            'Description' => 'Rezervacija je odobrena'
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Odbijena',
            'Description' => 'Rezervacija je odbijena'
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Stavke vraćene',
            'Description' => 'Sve stavke vraćene'
        ]);
        DB::table('reservation_status')->insert([
            'name' => 'Odobrena',
            'Description' => 'Rezervacija je odobrena'
        ]);
    }
}
