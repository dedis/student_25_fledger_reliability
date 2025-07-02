<?php

namespace Database\Factories;

use App\Models\DataPoint;
use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DataPointFactory extends Factory
{
    protected $model = DataPoint::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'value' => $this->faker->word(),
            'time' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'node_id' => Node::factory(),
        ];
    }
}
