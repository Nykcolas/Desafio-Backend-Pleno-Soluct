<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Enums\TaskStatus;

class TaskFactory extends Factory
{

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(TaskStatus::cases())->value,
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
        ];
    }
}
