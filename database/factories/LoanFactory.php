<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "amount" => $this->faker->numberBetween(10,100) * 1000,
            "terms" => $this->faker->numberBetween(2, 20),
            "user_id" => User::factory()->create()->id
        ];
    }
}
