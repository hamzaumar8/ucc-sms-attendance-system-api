<?php

namespace Database\Factories;

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::whereDoesntHave('student')
                ->whereDoesntHave('lecturer')
                ->inRandomOrder()
                ->first()
                ->id,
            'level_id' => Level::inRandomOrder()
                ->first()
                ->id,
            'index_number' => Str::random(10),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            // 'gender' => 'male',
        ];
    }
}
