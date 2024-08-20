<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Basic email styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .email-container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .email-body {
            padding: 20px;
            line-height: 1.6;
        }

        .email-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #888888;
        }

        .email-footer a {
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="email-body">
            <h2>New Contact Message</h2>
            <p>You have received a new message from <strong>{{ $name }}</strong> ({{ $email }}).</p>
            <p><strong>Message:</strong></p>
            <p>{{ $messageContent }}</p>
        </div>
        <div class="email-footer">
            <p>Thanks,<br>{{ config('app.name') }}</p>
            <p><a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
        </div>
    </div>
</body>

</html>