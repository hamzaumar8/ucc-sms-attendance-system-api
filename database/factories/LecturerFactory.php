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
            'user_id' => User::whereDoesntHave('student')
                            ->whereDoesntHave('lecturer')
                            ->inRandomOrder()
                            ->first()
                            ->id,
            'staff_id' => $this->faker->unique()->numberBetween(12345, 99999),
            'title' => 'Dr.',
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->firstName(),
            'other_name' => $this->faker->lastName(),
            // 'gender' => 'male',
            // 'phone' => $this->faker->phoneNumber(),
        ];
    }
}
