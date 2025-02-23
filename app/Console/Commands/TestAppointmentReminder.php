<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentReminder;
use Carbon\Carbon;

class TestAppointmentReminder extends Command
{
    protected $signature = 'appointments:test-reminder {email}';
    protected $description = 'Test appointment reminder email';

    public function handle()
    {
        $email = $this->argument('email');

        // Create a test appointment
        $testAppointment = new Appointment([
            'title' => 'Test Appointment',
            'description' => 'This is a test appointment reminder',
            'start_time' => Carbon::now()->addHour(),
            'timezone' => 'UTC',
            'reminder_minutes' => 30,
            'status' => 'active',
            'user_id' => 1 // Make sure this user exists in your database
        ]);

        $this->info("Testing reminder email to: {$email}");

        try {
            // Send test email using the test appointment data
            Mail::to($email)
                ->send(new AppointmentReminder($testAppointment, true));

            $this->info('Test reminder sent successfully!');
            $this->info('Check storage/logs/laravel.log for the email content');
        } catch (\Exception $e) {
            $this->error("Failed to send test reminder: {$e->getMessage()}");
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
} 