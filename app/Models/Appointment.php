<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_time',
        'timezone',
        'status',
        'reminder_minutes',
        'reminder_sent',
        'guests'
    ];

    protected $casts = [
        'guests' => 'array',
        'start_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guests()
    {
        return $this->hasMany(AppointmentGuest::class);
    }

    // Scope for upcoming appointments
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    // Scope for past appointments
    public function scopePast($query)
    {
        return $query->where('start_time', '<=', now());
    }

    // Add scope for active appointments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Add scope for cancelled appointments
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}