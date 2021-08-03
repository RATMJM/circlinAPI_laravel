<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserStat;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserStatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserStat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'birthday' => date('Ymd', time()),
            'gender' => ['W', 'M'][random_int(0, 1)],
            'height' => random_int(150, 190),
            'weight' => random_int(40, 100),
            'bmi' => random_int(20, 40),
        ];
    }
}
