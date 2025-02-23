<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentGuest extends Model
{
    protected $fillable = [
        'appointment_id',
        'email',
        'status',
        'timezone'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
} 