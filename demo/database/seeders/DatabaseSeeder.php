<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->call(PeopleSeeder::class);
        $this->call(StatesSeeder::class);
    }
}
