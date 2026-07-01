<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>NEXSTOCK - Login</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body {
            background-color: #030712 !important;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(14, 165, 233, 0.15) 0%, transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.05) 0%, transparent 60%) !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-wrapper {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Decorative glowing orbs in background */
        .glow-orb-1 {
            position: absolute;
            top: 25%;
            left: 20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, rgba(14, 165, 233, 0) 70%);
            filter: blur(50px);
            pointer-events: none;
            z-index: 0;
        }

        .glow-orb-2 {
            position: absolute;
            bottom: 25%;
            right: 20%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0) 70%);
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 2.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .auth-logo-icon-wrapper {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 0.75rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .auth-logo-title {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, #38bdf8, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .auth-logo-subtitle {
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.35rem;
            letter-spacing: 0.01em;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Adjustments for inputs inside guest layout */
        .auth-card .form-control {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1rem !important;
            transition: all 0.2s ease !important;
            height: 48px !important;
        }

        .auth-card .form-control:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .auth-card .form-label {
            color: #cbd5e1 !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            margin-bottom: 0.5rem !important;
        }

        .auth-card .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #10b981) !important;
            border: none !important;
            border-radius: 0.75rem !important;
            height: 48px !important;
            font-weight: 600 !important;
            letter-spacing: 0.025em;
            color: #ffffff !important;
            box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3) !important;
            transition: all 0.2s ease !important;
            cursor: pointer;
        }

        .auth-card .btn-primary:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 12px 20px -3px rgba(14, 165, 233, 0.4) !important;
            opacity: 0.95;
        }

        .auth-card .btn-primary:active {
            transform: translateY(1px) !important;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="glow-orb-1"></div>
        <div class="glow-orb-2"></div>
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon-wrapper">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="url(#logoGrad)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <defs>
                            <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#10b981" />
                            </linearGradient>
                        </defs>
                        <!-- Box Outer Outline -->
                        <path d="M12 2L21 7L12 12L3 7Z" />
                        <path d="M3 7v10l9 5V12" />
                        <path d="M21 7v10l-9 5" />
                        
                        <!-- Packing Tape -->
                        <path d="M7.5 4.5L16.5 9.5" />
                        <path d="M16.5 9.5v3" />

                        <!-- Dotted patterns -->
                        <circle cx="7" cy="15.5" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="7" cy="17" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="7" cy="18.5" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="8.5" cy="16.25" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="8.5" cy="17.75" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="10" cy="17" r="0.5" fill="url(#logoGrad)" stroke="none" />

                        <circle cx="17" cy="15.5" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="17" cy="17" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="17" cy="18.5" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="15.5" cy="16.25" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="15.5" cy="17.75" r="0.5" fill="url(#logoGrad)" stroke="none" />
                        <circle cx="14" cy="17" r="0.5" fill="url(#logoGrad)" stroke="none" />
                    </svg>
                </div>
                <div class="auth-logo-title">NEXSTOCK</div>
                <div class="auth-logo-subtitle">Inventory & Logistics Management System</div>
            </div>
            {{ $slot }}
        </div>
    </div>
</body>
</html>
