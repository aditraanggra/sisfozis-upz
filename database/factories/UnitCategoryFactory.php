<?php

namespace Database\Factories;

use App\Models\UnitCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitCategoryFactory extends Factory
{
    protected $model = UnitCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'desc' => $this->faker->sentence(),
        ];
    }
}
