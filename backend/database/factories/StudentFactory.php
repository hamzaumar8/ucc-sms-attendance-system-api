<?php

namespace Database\Factories;

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
            'user_id' => $attribute['user_id'] ?? User::factory(),
            'index_number' => Str::random(10),
            'first_name' => $this->faker->firstName(),
            'index_number' => $this->faker->uniqueGenerator(),
            'last_name' => $this->faker->lastName(),
            'gender' => 'male',
        ];
    }
}