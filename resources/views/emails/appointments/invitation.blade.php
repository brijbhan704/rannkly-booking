<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
        }
        .title {
            color: #2c5282;
            font-size: 24px;
            margin: 0;
            padding: 0;
        }
        .appointment-details {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .detail-row {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: bold;
            color: #4a5568;
        }
        .guest-list {
            background-color: #f8fafc;
            padding: 15px 20px;
            border-radius: 6px;
            margin-top: 20px;
        }
        .guest-item {
            padding: 5px 0;
            color: #4a5568;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="title">
                {{ $isOrganizer ? 'Appointment Confirmation' : 'Appointment Invitation' }}
            </h1>
        </div>

        <p>Dear {{ $isOrganizer ? $appointment->user->name : 'Guest' }},</p>

        <div class="appointment-details">
            <div class="detail-row">
                <span class="detail-label">Title:</span>
                <span>"{{ $appointment->title }}"</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span>{{ $isOrganizer ? 'Successfully booked' : 'Scheduled' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span>{{ $appointment->start_time->setTimezone($appointment->timezone)->format('F j, Y g:i A') }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <p style="margin: 5px 0;">{{ $appointment->description }}</p>
            </div>
        </div>

        @if($appointment->guests ? $appointment->guests->count() : 0 > 0)
        <div class="guest-list">
            <div class="detail-label">Guests:</div>
            @foreach($appointment->guests as $guest)
            <div class="guest-item">
                • {{ $guest->email }}
            </div>
            @endforeach
        </div>
        @endif

        <div class="footer">
            <p>Thank you!</p>
            <p>Best regards,<br>Rannkly</p>
        </div>
    </div>
</body>
</html> 