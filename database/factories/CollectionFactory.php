<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    public function definition(): array
    {
        return [

            'loan_id' => rand(1, 20),

            'collected_by' => 1,

            'amount_paid' => rand(500, 5000),

            'payment_mode' => fake()->randomElement([
                'cash',
                'upi',
                'card'
            ]),

            'location' => fake()->city(),

            'remarks' => fake()->sentence(),

            'collected_at' => fake()->dateTimeThisYear()
        ];
    }
}