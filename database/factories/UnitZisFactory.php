<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\UnitCategory;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitZisFactory extends Factory
{
    protected $model = UnitZis::class;

    public function definition(): array
    {
        $district = District::factory()->create();
        $village  = Village::factory()->create(['district_id' => $district->id]);

        return [
            'user_id'        => User::factory(),
            'category_id'    => UnitCategory::factory(),
            'village_id'     => $village->id,
            'district_id'    => $district->id,
            'no_sk'          => $this->faker->unique()->numerify('SK-####'),
            'unit_name'      => 'UPZ ' . $this->faker->company(),
            'no_register'    => $this->faker->unique()->numerify('REG-######'),
            'address'        => $this->faker->address(),
            'unit_leader'    => $this->faker->name(),
            'unit_assistant' => $this->faker->name(),
            'unit_finance'   => $this->faker->name(),
            'operator_phone' => $this->faker->phoneNumber(),
            'rice_price'     => $this->faker->numberBetween(10000, 15000),
            'is_verified'    => true,
        ];
    }
}
