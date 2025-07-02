<?php

namespace Database\Factories;

use App\Models\Experiment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExperimentFactory extends Factory
{
    protected $model = Experiment::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
