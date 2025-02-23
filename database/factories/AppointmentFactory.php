<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start_time' => now()->addDays(rand(1, 10)),
            'timezone' => 'UTC',
            'status' => 'active',
            'reminder_minutes' => 30,
            'reminder_sent' => 0,
        ];
    }
} 