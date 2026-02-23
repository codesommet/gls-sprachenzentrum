<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - GLS Sprachenzentrum</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/images/favicon/favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #211e1d;
            --primary-light: #3e3832;
            --surface: #fffee8;
            --light--off-white: #fffdf0;
            --text: #1f2937;
            --text-muted: #6b7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--surface);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .error-card {
            background-color: var(--light--off-white);
            border-radius: 24px;
            padding: 60px 40px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(33, 30, 29, 0.08);
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            opacity: 0.05;
            top: -100px;
            right: -100px;
        }

        .error-code {
            font-size: 120px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;

            background: @yield('icon-bg', 'linear-gradient(135deg, #ef4444, #dc2626)')

            ;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 1s ease-in-out;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .error-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
        }

        .error-message {
            font-size: 16px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .error-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(33, 30, 29, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #d1d5db;
            color: var(--text);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            background: rgba(33, 30, 29, 0.05);
        }

        .error-details {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 13px;
            color: var(--text-muted);
        }

        .error-details code {
            background: rgba(0, 0, 0, 0.05);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
        }

        @media (max-width: 480px) {
            .error-card {
                padding: 40px 24px;
            }

            .error-code {
                font-size: 80px;
            }

            .error-title {
                font-size: 22px;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-card">
            @yield('content')
        </div>
    </div>
</body>

</html>
