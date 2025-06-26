<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use App\Enums\TaskStatus;
use Faker\Factory as Faker;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = User::all();

        foreach ($users as $user) {
            for ($i = 0; $i < 100; $i++) {
                $dueDate = $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d');

                Task::create([
                    'user_id' => $user->id,
                    'title' => $faker->sentence(4),
                    'description' => $faker->text(200), // evita parágrafos vazios
                    'status' => $faker->randomElement(TaskStatus::values()),
                    'due_date' => $dueDate,
                ]);
            }
        }
    }
}