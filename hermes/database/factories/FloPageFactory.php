<?php

namespace Database\Factories;

use App\Models\FloPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FloPageFactory extends Factory
{
    protected $model = FloPage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(), //
            'flo_id' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
