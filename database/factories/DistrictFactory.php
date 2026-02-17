<?php

namespace Database\Factories;

use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistrictFactory extends Factory
{
    protected $model = District::class;

    public function definition(): array
    {
        return [
            'district_code' => $this->faker->unique()->numerify('####'),
            'name'          => $this->faker->city(),
        ];
    }
}
