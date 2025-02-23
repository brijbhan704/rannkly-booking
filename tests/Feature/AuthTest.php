<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentInvitation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use function Pest\Laravel\{actingAs, post, get, postJson};
use App\Models\AppointmentGuest;
use Laravel\Sanctum\Sanctum;

// Just use the base TestCase without any database traits
uses(TestCase::class, DatabaseTransactions::class);

beforeEach(function () {
    Mail::fake();
});

test('users cannot book appointments with duplicate guest emails', function () {
    $user = User::factory()->create();
    actingAs($user);

    $appointmentDate = now()->addDays(2)->setTime(9, 0);

    $response = post('/api/appointments', [
        'title' => 'Team Meeting',
        'description' => 'Weekly sync',
        'start_time' => $appointmentDate->format('Y-m-d H:i:s'),
        'timezone' => 'Asia/Kolkata',
        'reminder_minutes' => 30,
        'guests' => ['guest@example.com', 'guest@example.com'] // Duplicate email
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Duplicate guest emails are not allowed']);
});

// test('cannot book appointment if guest already has appointment at same time', function () {
//     $user = User::factory()->create();
//     $otherUser = User::factory()->create();
//     actingAs($user);

//     // Create an existing appointment with a guest
//     $existingAppointment = Appointment::factory()->create([
//         'user_id' => $otherUser->id,
//         'start_time' => now()->addDays(2)->setTime(10, 0),
//         'status' => 'active'
//     ]);

//     AppointmentGuest::create([
//         'appointment_id' => $existingAppointment->id,
//         'email' => 'busy.guest@example.com',
//         'status' => 'pending'
//     ]);

//     // Try to book another appointment with the same guest at the same time
//     $response = post('/api/appointments', [
//         'title' => 'Another Meeting',
//         'description' => 'Should fail',
//         'start_time' => $existingAppointment->start_time->format('Y-m-d H:i:s'),
//         'timezone' => 'Asia/Kolkata',
//         'reminder_minutes' => 30,
//         'guests' => ['busy.guest@example.com']
//     ]);

//     $response->assertStatus(422)
//         ->assertJson(['message' => 'Guest busy.guest@example.com already has an appointment at this time']);
// });

test('successful appointment booking sends emails to organizer and guests', function () {
    $user = User::factory()->create();
    actingAs($user);

    $appointmentDate = now()->addDays(2)->setTime(9, 0);

    $response = post('/api/appointments', [
        'title' => 'Team Meeting',
        'description' => 'Weekly sync',
        'start_time' => $appointmentDate->format('Y-m-d H:i:s'),
        'timezone' => 'Asia/Kolkata',
        'reminder_minutes' => 30,
        'guests' => ['guest1@example.com', 'guest2@example.com']
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Appointment created successfully']);

    // Verify organizer email
    Mail::assertSent(AppointmentInvitation::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    // Verify guest emails
    Mail::assertSent(AppointmentInvitation::class, function ($mail) {
        return $mail->hasTo('guest1@example.com');
    });

    Mail::assertSent(AppointmentInvitation::class, function ($mail) {
        return $mail->hasTo('guest2@example.com');
    });

    // Verify total number of emails sent (1 organizer + 2 guests)
    Mail::assertSent(AppointmentInvitation::class, 3);
});

test('users can logout successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = post('/api/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully']);
});