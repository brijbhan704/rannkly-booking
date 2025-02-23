<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentInvitation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use function Pest\Laravel\{actingAs, post, get, postJson};


// Just use the base TestCase without any database traits
uses(TestCase::class, DatabaseTransactions::class);

beforeEach(function () {
    Mail::fake();
});

test('authenticated users can book an appointment', function () {
    $user = User::factory()->create();
    actingAs($user);

    $appointmentDate = now()->addDays(2)->setTime(9, 0);

    $response = post('/api/appointments', [
        'title' => 'Medical Checkup',
        'description' => 'Routine health checkup',
        'start_time' => $appointmentDate->format('Y-m-d H:i:s'),
        'timezone' => 'Asia/Kolkata',
        'reminder_minutes' => 30,
        'guests' => ['guest@example.com']
    ]);

    $response->assertStatus(200);
    Mail::assertSent(AppointmentInvitation::class, function ($mail) {
        return $mail->hasTo('guest@example.com');
    });
});

test('appointments can only be booked on weekdays', function () {
    $user = User::factory()->create();
    actingAs($user);

    // Attempt to book on a Saturday
    $response = post('/api/appointments', [
        'title' => 'Weekend Checkup',
        'description' => 'Health checkup',
        'start_time' => now()->nextWeekendDay()->format('Y-m-d H:i:s'),
        'timezone' => 'Asia/Kolkata',
        'reminder_minutes' => 30,
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Appointments can only be booked on weekdays']);
});

test('users cannot book appointments on weekends', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = post('/api/appointments', [
        'title' => 'Weekend Checkup',
        'description' => 'Health checkup',
        'start_time' => now()->nextWeekendDay()->format('Y-m-d H:i:s'),
        'timezone' => 'Asia/Kolkata',
        'reminder_minutes' => 30,
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Appointments can only be booked on weekdays']);
});

test('users can view their upcoming appointments', function () {
    $user = User::factory()->create();
    actingAs($user);

    // Create an upcoming appointment
    $appointment = Appointment::factory()->create([
        'user_id' => $user->id,
        'title' => 'Future Meeting',
        'description' => 'Team sync-up',
        'start_time' => now()->addDays(2),
        'timezone' => 'UTC',
        'status' => 'active',
        'reminder_minutes' => 30    
    ]);

    $response = get('/api/appointments?filter=upcoming');

    $response->assertOk()
        ->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'description',
                'start_time',
                'timezone',
                'status',
                'reminder_minutes'
            ]
        ])
        ->assertJsonCount(1);
});

test('users can view their cancelled appointments', function () {
    $user = User::factory()->create();
    actingAs($user);

    $appointment = Appointment::factory()->create([
        'user_id' => $user->id,
        'title' => 'Cancelled Meeting',
        'description' => 'Team sync-up',
        'start_time' => now()->addDays(2),
        'status' => 'cancelled'
    ]);
    $response = get('/api/appointments?filter=cancelled');

    $response->assertOk()
        ->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'description',
                'start_time',
                'timezone',
                'status',
                'reminder_minutes'
            ]
        ]);
});

test('users can view their past appointments', function () {
    $user = User::factory()->create();
    actingAs($user);

    $appointment = Appointment::factory()->create([
        'user_id' => $user->id,
        'title' => 'Past Meeting',
        'description' => 'Team sync-up',
        'start_time' => now()->subDays(2),
        'status' => 'active'
    ]);
    $response = get('/api/appointments?filter=past');

    $response->assertOk()
        ->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'description',
                'start_time',
                'timezone',
                'status',
                'reminder_minutes'
            ]
        ]);
});

test('users cannot cancel appointments less than 30 minutes before start time', function () {
    $user = User::factory()->create();
    actingAs($user);

    $appointment = Appointment::factory()->create([
        'user_id' => $user->id,
        'start_time' => now()->addMinutes(20), // Only 20 minutes in future
        'status' => 'active'
    ]);

    $response = post("/api/appointments/{$appointment->id}/cancel");

    $response->assertStatus(422)
        ->assertJson(['message' => 'Appointments can only be cancelled at least 30 minutes before the scheduled time']);
});

test('users cannot cancel past appointments', function () {
    $user = User::factory()->create();
    actingAs($user);

    $appointment = Appointment::factory()->create([
        'user_id' => $user->id,
        'start_time' => now()->subHour(), // 1 hour in past
        'status' => 'active'
    ]);

    $response = post("/api/appointments/{$appointment->id}/cancel");

    $response->assertStatus(422)
        ->assertJson(['message' => 'Cannot cancel past appointments']);
});

test('users cannot cancel appointments they do not own', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    actingAs($user);

    $appointment = Appointment::factory()->create([
        'user_id' => $otherUser->id,
        'start_time' => now()->addDay(),
        'status' => 'active'
    ]);

    $response = post("/api/appointments/{$appointment->id}/cancel");

    $response->assertStatus(403)
        ->assertJson(['message' => 'Unauthorized']);
});

