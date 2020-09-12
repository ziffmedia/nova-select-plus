<?php

use App\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeopleSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('people')->truncate();

        Person::create(['name' => 'Ralph Schindler']);
        Person::create(['name' => 'Josh Butts']);

    }

}
