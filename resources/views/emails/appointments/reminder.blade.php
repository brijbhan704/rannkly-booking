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
            color: #2c5282;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            Appointment Reminder
        </div>

        <p>Dear {{ $isOrganizer ? ($appointment->user->name ?? 'Organizer') : 'Guest' }},</p>

        <p>This is a reminder for your upcoming appointment titled "{{ $appointment->title ?? 'Untitled' }}" scheduled for {{ $appointment->start_time ? $appointment->start_time->setTimezone($appointment->timezone ?? 'UTC')->format('F j, Y g:i A') : 'upcoming time' }}.</p>

        <div class="footer">
            <p>Thank you!</p>
            <p>
                Best regards,<br>
                {{ config('app.name', 'Rannkly') }}
            </p>
        </div>
    </div>
</body>
</html> 