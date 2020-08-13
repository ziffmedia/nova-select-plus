<?php

use App\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('states')->truncate();

        State::create(['name' => 'Alaska', 'code' => 'AK']);
        State::create(['name' => 'Alabama', 'code' => 'AL']);
        State::create(['name' => 'American Samoa', 'code' => 'AS']);
        State::create(['name' => 'Arizona', 'code' => 'AZ']);
        State::create(['name' => 'Arkansas', 'code' => 'AR']);
        State::create(['name' => 'California', 'code' => 'CA']);
        State::create(['name' => 'Colorado', 'code' => 'CO']);
        State::create(['name' => 'Connecticut', 'code' => 'CT']);
        State::create(['name' => 'Delaware', 'code' => 'DE']);
        State::create(['name' => 'District of Columbia', 'code' => 'DC']);
        State::create(['name' => 'Federated States of Micronesia', 'code' => 'FM']);
        State::create(['name' => 'Florida', 'code' => 'FL']);
        State::create(['name' => 'Georgia', 'code' => 'GA']);
        State::create(['name' => 'Guam', 'code' => 'GU']);
        State::create(['name' => 'Hawaii', 'code' => 'HI']);
        State::create(['name' => 'Idaho', 'code' => 'ID']);
        State::create(['name' => 'Illinois', 'code' => 'IL']);
        State::create(['name' => 'Indiana', 'code' => 'IN']);
        State::create(['name' => 'Iowa', 'code' => 'IA']);
        State::create(['name' => 'Kansas', 'code' => 'KS']);
        State::create(['name' => 'Kentucky', 'code' => 'KY']);
        State::create(['name' => 'Louisiana', 'code' => 'LA']);
        State::create(['name' => 'Maine', 'code' => 'ME']);
        State::create(['name' => 'Marshall Islands', 'code' => 'MH']);
        State::create(['name' => 'Maryland', 'code' => 'MD']);
        State::create(['name' => 'Massachusetts', 'code' => 'MA']);
        State::create(['name' => 'Michigan', 'code' => 'MI']);
        State::create(['name' => 'Minnesota', 'code' => 'MN']);
        State::create(['name' => 'Mississippi', 'code' => 'MS']);
        State::create(['name' => 'Missouri', 'code' => 'MO']);
        State::create(['name' => 'Montana', 'code' => 'MT']);
        State::create(['name' => 'Nebraska', 'code' => 'NE']);
        State::create(['name' => 'Nevada', 'code' => 'NV']);
        State::create(['name' => 'New Hampshire', 'code' => 'NH']);
        State::create(['name' => 'New Jersey', 'code' => 'NJ']);
        State::create(['name' => 'New Mexico', 'code' => 'NM']);
        State::create(['name' => 'New York', 'code' => 'NY']);
        State::create(['name' => 'North Carolina', 'code' => 'NC']);
        State::create(['name' => 'North Dakota', 'code' => 'ND']);
        State::create(['name' => 'Northern Mariana Islands', 'code' => 'MP']);
        State::create(['name' => 'Ohio', 'code' => 'OH']);
        State::create(['name' => 'Oklahoma', 'code' => 'OK']);
        State::create(['name' => 'Oregon', 'code' => 'OR']);
        State::create(['name' => 'Palau', 'code' => 'PW']);
        State::create(['name' => 'Pennsylvania', 'code' => 'PA']);
        State::create(['name' => 'Puerto Rico', 'code' => 'PR']);
        State::create(['name' => 'Rhode Island', 'code' => 'RI']);
        State::create(['name' => 'South Carolina', 'code' => 'SC']);
        State::create(['name' => 'South Dakota', 'code' => 'SD']);
        State::create(['name' => 'Tennessee', 'code' => 'TN']);
        State::create(['name' => 'Texas', 'code' => 'TX']);
        State::create(['name' => 'Utah', 'code' => 'UT']);
        State::create(['name' => 'Vermont', 'code' => 'VT']);
        State::create(['name' => 'Virgin Islands', 'code' => 'VI']);
        State::create(['name' => 'Virginia', 'code' => 'VA']);
        State::create(['name' => 'Washington', 'code' => 'WA']);
        State::create(['name' => 'West Virginia', 'code' => 'WV']);
        State::create(['name' => 'Wisconsin', 'code' => 'WI']);
        State::create(['name' => 'Wyoming', 'code' => 'WY']);
        State::create(['name' => 'Armed Forces Africa', 'code' => 'AE']);
        State::create(['name' => 'Armed Forces Americas (except Canada)', 'code' => 'AA']);
        State::create(['name' => 'Armed Forces Canada', 'code' => 'AE']);
        State::create(['name' => 'Armed Forces Europe', 'code' => 'AE']);
        State::create(['name' => 'Armed Forces Middle East', 'code' => 'AE']);
        State::create(['name' => 'Armed Forces Pacific', 'code' => 'AP']);
    }

}
