<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lecturer>
 */
class LecturerFactory extends Factory
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
            'title' => 'Dr.',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'other_name' => $this->faker->lastName(),
            'gender' => 'male',
            // 'phone1' => $this->faker->phoneNumber(),
        ];
    }
}