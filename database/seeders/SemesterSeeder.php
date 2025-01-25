<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('semesters')->insert([
            [
                'semester' => 'first',
                'academic_year' => '2023/2024',
                'start_date' => Carbon::create(2024, 9, 1),
                'end_date' => Carbon::create(2025, 1, 31),
                'promotion_status' => 'close',
                'timetable' => 'First semester timetable content',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'semester' => 'second',
                'academic_year' => '2023/2024',
                'start_date' => Carbon::create(2025, 2, 1),
                'end_date' => Carbon::create(2025, 6, 30),
                'promotion_status' => 'close',
                'timetable' => 'Second semester timetable content',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
