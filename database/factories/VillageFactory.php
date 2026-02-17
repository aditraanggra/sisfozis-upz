<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

class VillageFactory extends Factory
{
    protected $model = Village::class;

    public function definition(): array
    {
        return [
            'district_id'  => District::factory(),
            'village_code' => $this->faker->unique()->numerify('######'),
            'name'         => $this->faker->streetName(),
        ];
    }
}
