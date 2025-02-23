<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentGuest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Mail\AppointmentInvitation;
use App\Mail\AppointmentCancellation;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $startTime = Carbon::parse($value);
                    if ($startTime->isPast()) {
                        $fail('Cannot book appointments in the past.');
                    }
                },
            ],
            'timezone' => 'required|string',
            'guests' => 'array',
            'guests.*' => 'email',
            'reminder_minutes' => 'required|integer|min:1'
        ]);

        // Convert start time to UTC for storage
        $startTime = Carbon::parse($request->start_time, $request->timezone)
            ->setTimezone('UTC');

        // Check if it's a weekday
        if ($startTime->isWeekend()) {
            return response()->json([
                'message' => 'Appointments can only be booked on weekdays'
            ], 422);
        }

        // Check for duplicate appointments
        $existingAppointment = Appointment::where('user_id', auth()->id())
            ->where('start_time', $startTime)
            ->where('status', 'active')
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'message' => 'You already have an appointment scheduled for this time'
            ], 422);
        }

        // Check for duplicate guest emails
        if ($request->guests) {
            $uniqueGuests = array_unique($request->guests);
            if (count($uniqueGuests) !== count($request->guests)) {
                return response()->json([
                    'message' => 'Duplicate guest emails are not allowed'
                ], 422);
            }

            // Check if any guest is already booked for this time
            foreach ($request->guests as $guestEmail) {
                $existingGuestAppointment = AppointmentGuest::whereHas('appointment', function ($query) use ($startTime) {
                    $query->where('start_time', $startTime)
                        ->where('status', 'active');
                })->where('email', $guestEmail)->first();

                if ($existingGuestAppointment) {
                    return response()->json([
                        'message' => "Guest {$guestEmail} already has an appointment at this time"
                    ], 422);
                }
            }
        }

        try {
            $appointment = Appointment::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'start_time' => $startTime,
                'timezone' => $request->timezone,
                'reminder_minutes' => $request->reminder_minutes,
                'reminder_sent' => false
            ]);

            // Add guests and send invitations
            $guestErrors = [];
            if ($request->guests) {
                foreach ($request->guests as $guestEmail) {
                    try {
                        // Create guest record
                        $guest = $appointment->guests()->create([
                            'email' => $guestEmail,
                            'status' => 'pending'
                        ]);

                        // Send invitation email
                        Mail::to($guestEmail)
                            ->send(new AppointmentInvitation($appointment, false));
                    } catch (\Exception $e) {
                        \Log::error("Failed to process guest {$guestEmail}: " . $e->getMessage());
                        $guestErrors[] = $guestEmail;
                    }
                }
            }

            // Send organizer confirmation
            try {
                Mail::to(auth()->user()->email)
                    ->send(new AppointmentInvitation($appointment, true));
            } catch (\Exception $e) {
                \Log::error('Failed to send organizer email: ' . $e->getMessage());
            }

            // Return response with any guest processing errors
            return response()->json([
                'appointment' => $appointment->load('guests'),
                'guest_errors' => $guestErrors,
                'message' => $guestErrors 
                    ? 'Appointment created but some guest invitations failed to send' 
                    : 'Appointment created successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Appointment creation error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create appointment'], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Appointment::with('guests')
            ->where('user_id', auth()->id());

        // Filter by status if specified
        if ($request->filter) {
            switch ($request->filter) {
                case 'upcoming':
                    $query->where('status', 'active')
                          ->where('start_time', '>', now());
                    break;
                case 'cancelled':
                    $query->where('status', 'cancelled');
                    break;
                case 'past':
                    $query->where('status', 'active')
                          ->where('start_time', '<=', now());
                    break;
            }
        }

        // Handle sorting
        switch ($request->sort) {
            case 'start_time':
                $query->orderBy('start_time', 'asc');
                break;
            case 'created_at':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('start_time', 'desc');
        }

        $appointments = $query->get();

        return response()->json($appointments);
    }

    public function cancel(Appointment $appointment)
    {
        // Check if user owns the appointment
        if ($appointment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if appointment is in the past
        if ($appointment->start_time < now()) {
            return response()->json([
                'message' => 'Cannot cancel past appointments'
            ], 422);
        }

        // Check if within 30 minutes of start time
        $thirtyMinutesBeforeStart = Carbon::parse($appointment->start_time)->subMinutes(30);
        if (now() > $thirtyMinutesBeforeStart) {
            return response()->json([
                'message' => 'Appointments can only be cancelled at least 30 minutes before the scheduled time'
            ], 422);
        }

        try {
            // Ensure relationships are loaded
            $appointment->refresh();
            $appointment->load(['user', 'guests']);

            // Update status
            $appointment->status = 'cancelled';
            $appointment->save();

            // Send cancellation email to organizer
            if ($appointment->user) {
                try {
                    Mail::to($appointment->user->email)
                        ->send(new AppointmentCancellation($appointment, true));
                } catch (\Exception $e) {
                    \Log::error('Failed to send organizer cancellation email: ' . $e->getMessage());
                }
            }

            // Send cancellation emails to guests
            if ($appointment->guests && $appointment->guests->count() > 0) {
                foreach ($appointment->guests as $guest) {
                    try {
                        Mail::to($guest->email)
                            ->send(new AppointmentCancellation($appointment, false));
                    } catch (\Exception $e) {
                        \Log::error("Failed to send guest cancellation email to {$guest->email}: " . $e->getMessage());
                    }
                }
            }

            return response()->json(['message' => 'Appointment cancelled successfully']);
        } catch (\Exception $e) {
            \Log::error('Appointment cancellation error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to cancel appointment'], 500);
        }
    }
} 