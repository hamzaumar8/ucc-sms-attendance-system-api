<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Level::insert([
            ['name' => 'Level 200'],
            ['name' => 'GEM 250'],
            ['name' => 'Level 200'],
            ['name' => 'GEM 300'],
            ['name' => 'Level 300'],
            ['name' => 'Level 400'],
            ['name' => 'Level 500'],
            ['name' => 'Level 600'],
        ]);
    }
}