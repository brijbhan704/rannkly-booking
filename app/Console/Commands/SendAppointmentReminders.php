<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AppointmentReminder;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send reminders for upcoming appointments';

    public function handle()
    {
        $this->info('Starting to check for reminders...');
        Log::info('Starting appointment reminder check');

        $appointments = Appointment::query()
            ->where('status', 'active')
            ->where('reminder_sent', false)
            ->where('start_time', '>', now())
            ->get();

        $this->info("Found {$appointments->count()} appointments to check");
        Log::info("Found {$appointments->count()} appointments to check");

        $count = 0;
        foreach ($appointments as $appointment) {
            $reminderTime = Carbon::parse($appointment->start_time)
                ->subMinutes($appointment->reminder_minutes);

            $this->info("Checking appointment ID: {$appointment->id}");
            $this->info("Reminder time: {$reminderTime}");
            $this->info("Current time: " . now());

            if (now() >= $reminderTime) {
                try {
                    // Send to organizer
                    Mail::to($appointment->user->email)
                        ->queue(new AppointmentReminder($appointment, true));

                    $this->info("Queued reminder for organizer: {$appointment->user->email}");
                    Log::info("Queued reminder for organizer: {$appointment->user->email}");

                    // Send to guests
                    foreach ($appointment->guests as $guest) {
                        Mail::to($guest->email)
                            ->send(new AppointmentReminder($appointment));
                        
                        $this->info("Queued reminder for guest: {$guest->email}");
                        Log::info("Queued reminder for guest: {$guest->email}");
                    }

                    // Mark reminder as sent
                    $appointment->update(['reminder_sent' => true]);
                    $count++;

                    $this->info("✓ Successfully processed reminder for appointment: {$appointment->title}");
                    Log::info("Successfully processed reminder for appointment: {$appointment->title}");

                    Log::info('Reminder sent', [
                        'appointment_id' => $appointment->id,
                        'user_email' => $appointment->user->email,
                        'appointment_date' => $appointment->appointment_date
                    ]);
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder for appointment {$appointment->id}: {$e->getMessage()}");
                    Log::error("Reminder failed for appointment {$appointment->id}: {$e->getMessage()}");

                    Log::error('Failed to send reminder', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $this->info("Not yet time to send reminder for appointment {$appointment->id}");
                Log::info("Not yet time to send reminder for appointment {$appointment->id}");
            }
        }

        $this->info("Sent {$count} reminders");
        Log::info("Reminder batch completed", ['sent_count' => $count]);

        $this->info('Finished checking reminders');
        Log::info('Finished checking reminders');
    }
} 