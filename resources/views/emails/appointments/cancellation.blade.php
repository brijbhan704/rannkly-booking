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
            color: #dc3545;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .appointment-details {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
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
            Appointment Cancellation
        </div>

        <p>Dear {{ $isOrganizer ? $appointment->user->name : 'Guest' }},</p>

        <p>The appointment titled "{{ $appointment->title }}" scheduled for {{ $appointment->start_time->setTimezone($appointment->timezone)->format('F j, Y g:i A') }} has been cancelled.</p>

        <p>We regret any inconvenience caused.</p>

        <div class="footer">
            <p>Thank you!</p>
            <p>
                Best regards,<br>
                {{ config('app.name') }}
            </p>
        </div>
    </div>
</body>
</html> 