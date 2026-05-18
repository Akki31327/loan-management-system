<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        $loanAmount = rand(10000, 100000);

        $paid = rand(0, $loanAmount);

        return [

            'loan_no' => 'LN' . rand(1000, 9999),

            'customer_name' => fake()->name(),

            'mobile' => '9' . rand(100000000, 999999999),

            'address' => fake()->address(),

            'loan_amount' => $loanAmount,

            'emi_amount' => rand(1000, 10000),

            'total_paid' => $paid,

            'pending_amount' => $loanAmount - $paid,

            'status' => ($loanAmount - $paid) <= 0
                ? 'closed'
                : 'active',

            'created_by' => 1
        ];
    }
}