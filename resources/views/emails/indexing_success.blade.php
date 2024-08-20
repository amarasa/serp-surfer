<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Avenir, Helvetica, sans-serif;
            box-sizing: border-box;
            background-color: #f8fafc;
            color: #74787e;
            height: 100%;
            line-height: 1.4;
            margin: 0;
            width: 100% !important;
            -webkit-text-size-adjust: none;
        }

        .wrapper {
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .content {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .header {
            padding: 25px 0;
            text-align: center;
        }

        .body {
            background-color: #ffffff;
            border-top: 1px solid #edeff2;
            border-bottom: 1px solid #edeff2;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .inner-body {
            background-color: #ffffff;
            margin: 0 auto;
            padding: 35px;
            width: 570px;
        }

        .content-cell {
            padding: 35px;
        }

        .button {
            background-color: #3869d4;
            border: 10px solid #3869d4;
            border-radius: 3px;
            color: #ffffff;
            display: inline-block;
            text-decoration: none;
            -webkit-text-size-adjust: none;
        }

        .footer {
            text-align: center;
            padding: 35px;
            width: 570px;
            margin: 0 auto;
        }

        .footer p {
            color: #aeaeae;
            margin-top: 0;
            line-height: 1.5em;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="header">
                            <a href="{{ config('app.url') }}" style="font-size: 19px; font-weight: bold; text-decoration: none;">
                                {{ config('app.name') }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-cell">
                                        <h1>Congratulations!</h1>
                                        <p>SERP Surfer has successfully indexed <strong>{{ $url }}</strong> after <strong>{{ $submissionCount }}</strong> attempts.</p>
                                        <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center">
                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td align="center">
                                                                <a href="{{ $url }}" class="button" target="_blank">View Your Site</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <p>Thank you for using SERP Surfer!</p>
                                        <p>Best Regards,<br>{{ config('app.name') }} Team</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-cell" align="center">
                                        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>