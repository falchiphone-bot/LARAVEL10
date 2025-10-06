<?php
namespace Database\Factories;

use App\Models\InvestmentAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentAccountFactory extends Factory
{
    protected $model = InvestmentAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => now()->toDateString(),
            'total_invested' => $this->faker->randomFloat(2,1000,50000),
            'account_name' => 'Conta '.strtoupper($this->faker->bothify('INV-###')),
            'broker' => 'Avenue',
        ];
    }
}
