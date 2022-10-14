<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'deadline' => '2022-09-09 09:09:09',
            'project_id' => 1,
            'unique_id' => 'T-11',
            'user_id' => '1f0955c5-7313-4536-8361-caddb186f6b5',
        ];
    }
}
