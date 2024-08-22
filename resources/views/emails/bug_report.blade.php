<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Report Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #333;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }

        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.15);
        }

        .header {
            background-color: #2d3748;
            padding: 20px;
            text-align: center;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
        }

        .content {
            padding: 20px;
            line-height: 1.6;
            font-size: 16px;
            color: #333;
        }

        .content h1 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #2d3748;
        }

        .content p {
            margin: 0 0 20px;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>Bug Report Submission</h1>
            </div>
            <div class="content">
                <h1>Bug Report from {{ $name }}</h1>
                <p>{{ $messageContent }}</p>
            </div>
            <div class="footer">
                <p>Thank you for your submission. Our team will review the report and take the necessary actions.</p>
            </div>
        </div>
    </div>
</body>

</html>