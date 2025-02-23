<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;

class AppointmentCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $isOrganizer;

    public function __construct(Appointment $appointment, $isOrganizer = false)
    {
        $this->appointment = $appointment;
        $this->isOrganizer = $isOrganizer;
    }

    public function build()
    {
        return $this->view('emails.appointments.cancellation')
                    ->subject('Appointment Cancelled: ' . $this->appointment->title);
    }
} 