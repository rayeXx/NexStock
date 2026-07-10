<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NEXSTOCK — Sistem Manajemen Gudang & Finansial Distributor</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Styles -->
    <style>
        :root {
            --bg-dark: #030712;
            --bg-card: rgba(17, 24, 39, 0.45);
            --border-card: rgba(255, 255, 255, 0.05);
            --border-hover: rgba(56, 189, 248, 0.25);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-blue: #0ea5e9;
            --accent-green: #10b981;
            --accent-indigo: #6366f1;
            --accent-yellow: #f59e0b;
            --font-sans: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            --transition-premium: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        /* Glowing Aurora Background Blobs */
        .aurora-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -2;
            overflow: hidden;
            background: #030712;
        }

        .aurora-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(140px);
            opacity: 0.55;
            mix-blend-mode: screen;
            will-change: transform;
        }

        .blob-1 {
            top: -15%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.28) 0%, transparent 80%);
            animation: aurora-move-1 25s infinite alternate ease-in-out;
        }

        .blob-2 {
            bottom: -20%;
            right: -15%;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, transparent 80%);
            animation: aurora-move-2 30s infinite alternate ease-in-out;
        }

        .blob-3 {
            top: 35%;
            left: 45%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, transparent 80%);
            animation: aurora-move-3 28s infinite alternate ease-in-out;
        }

        .blob-4 {
            bottom: 25%;
            left: 5%;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.2) 0%, transparent 80%);
            animation: aurora-move-4 22s infinite alternate ease-in-out;
        }

        /* Keyframes for smooth floating aurora waves */
        @keyframes aurora-move-1 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(120px, 90px) scale(1.15) rotate(45deg); }
            100% { transform: translate(-60px, 160px) scale(0.9) rotate(90deg); }
        }

        @keyframes aurora-move-2 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(-140px, -100px) scale(0.85) rotate(-30deg); }
            100% { transform: translate(90px, -50px) scale(1.1) rotate(60deg); }
        }

        @keyframes aurora-move-3 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(-90px, 130px) scale(1.2) rotate(90deg); }
            100% { transform: translate(130px, -70px) scale(0.85) rotate(0deg); }
        }

        @keyframes aurora-move-4 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(150px, -120px) scale(0.9) rotate(-60deg); }
            100% { transform: translate(-90px, 60px) scale(1.15) rotate(30deg); }
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            width: 100%;
        }

        /* Header / Navbar */
        header {
            border-bottom: 1px solid var(--border-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(3, 7, 18, 0.75);
            transition: var(--transition-premium);
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 90px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            text-decoration: none;
        }

        .logo-text {
            font-size: 1.7rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #38bdf8, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }

        .logo-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 0.55rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transition: var(--transition-premium);
        }

        .logo:hover .logo-icon-wrapper {
            transform: scale(1.05) rotate(3deg);
            border-color: rgba(56, 189, 248, 0.4);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
            padding: 0.25rem 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #38bdf8, #10b981);
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            padding: 7rem 0 5rem;
            text-align: center;
            position: relative;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(16, 185, 129, 0.06);
            border: 1px solid rgba(16, 185, 129, 0.18);
            color: var(--accent-green);
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 2.2rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.05);
        }

        .status-pulse {
            width: 8px;
            height: 8px;
            background-color: var(--accent-green);
            border-radius: 50%;
            animation: pulse 1.8s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .hero h1 {
            font-size: 4.2rem;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -2px;
            max-width: 1000px;
            margin: 0 auto 1.8rem;
            background: linear-gradient(135deg, #ffffff 40%, var(--text-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 740px;
            margin: 0 auto 3rem;
            line-height: 1.6;
        }

        .hero-ctas {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.2rem;
            margin-bottom: 5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #10b981);
            color: #fff;
            border: none;
            padding: 1.05rem 2.6rem;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition-premium);
            box-shadow: 0 10px 20px -3px rgba(14, 165, 233, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }

        .btn-primary:hover {
            box-shadow: 0 15px 30px -3px rgba(14, 165, 233, 0.5);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-card);
            color: var(--text-primary);
            padding: 1.05rem 2.6rem;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition-premium);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Dashboard CSS-only Mockup */
        .dashboard-mockup {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid var(--border-card);
            border-radius: 20px;
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.8),
                0 0 50px rgba(14, 165, 233, 0.05);
            overflow: hidden;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .mockup-header {
            background: rgba(3, 7, 18, 0.5);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-card);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mockup-dots {
            display: flex;
            gap: 0.45rem;
        }

        .mockup-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
        }

        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #10b981; }

        .mockup-title {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.25rem 1.5rem;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .mockup-content {
            padding: 2.2rem;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .mockup-card {
            background: rgba(30, 41, 59, 0.35);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            text-align: left;
        }

        .mockup-card h5 {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .mockup-card .value {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mockup-card .desc {
            font-size: 0.65rem;
            color: var(--text-muted);
            margin-top: 0.35rem;
        }

        .mockup-card.highlight {
            border-color: rgba(14, 165, 233, 0.2);
            background: rgba(14, 165, 233, 0.02);
        }

        .mockup-chart-box {
            grid-column: span 3;
            background: rgba(30, 41, 59, 0.2);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 1.5rem;
            height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .mockup-chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .mockup-chart-bars {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 100px;
            margin-top: 1rem;
            padding: 0 1rem;
        }

        .mockup-chart-bar {
            width: 28px;
            background: linear-gradient(to top, var(--accent-blue), var(--accent-indigo));
            border-radius: 6px 6px 0 0;
            transition: var(--transition-premium);
        }

        .mockup-chart-bar.alt {
            background: linear-gradient(to top, var(--accent-green), #059669);
        }

        /* Bento Grid Features Section */
        .features {
            padding: 6rem 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.6rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 0.75rem;
        }

        .section-title p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Bento Grid */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            grid-auto-rows: minmax(280px, auto);
        }

        .bento-item {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            transition: var(--transition-premium);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .bento-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.02), transparent 70%);
            pointer-events: none;
        }

        .bento-item:hover {
            transform: translateY(-4px) scale(1.005);
            border-color: var(--border-hover);
            box-shadow: 
                0 25px 50px -15px rgba(0, 0, 0, 0.5),
                0 0 20px rgba(14, 165, 233, 0.05);
        }

        .bento-item.span-2 {
            grid-column: span 2;
        }

        .bento-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 2rem;
        }

        .bento-content h3 {
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            letter-spacing: -0.5px;
        }

        .bento-content p {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Custom Bento Graphics */
        .bento-graphic {
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.78rem;
            font-family: monospace;
            color: var(--text-muted);
        }

        .graphic-fefo-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.4rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .graphic-fefo-row:last-child {
            border: none;
        }

        .badge-fefo {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .badge-fefo.warn {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-yellow);
        }

        /* Pricing Section */
        .pricing {
            padding: 6rem 0;
        }

        .pricing-grid {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 2rem;
            margin-top: 2rem;
        }

        .pricing-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 3.5rem 2.5rem;
            text-align: center;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            transition: var(--transition-premium);
            position: relative;
            width: 360px;
            max-width: 100%;
        }

        .pricing-card:hover {
            transform: translateY(-4px);
            border-color: var(--border-hover);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .pricing-card.premium {
            border-color: rgba(14, 165, 233, 0.3);
            background: rgba(14, 165, 233, 0.02);
        }

        .pricing-card.premium::after {
            content: 'Rekomendasi';
            position: absolute;
            top: 24px;
            right: 24px;
            background: linear-gradient(135deg, #0ea5e9, #10b981);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.3rem 0.85rem;
            border-radius: 50px;
        }

        .pricing-card h3 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .pricing-card .price {
            font-size: 2.6rem;
            font-weight: 900;
            margin: 1.8rem 0;
            color: var(--accent-blue);
            background: linear-gradient(135deg, #38bdf8, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .pricing-card .price span {
            font-size: 1.05rem;
            color: var(--text-secondary);
            font-weight: 500;
            -webkit-text-fill-color: var(--text-secondary);
        }

        .pricing-card ul {
            list-style: none;
            text-align: left;
            margin-bottom: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .pricing-card ul li {
            font-size: 0.92rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .pricing-card ul li::before {
            content: '✓';
            color: var(--accent-green);
            font-weight: 800;
            font-size: 1rem;
        }

        .pricing-card .btn-primary {
            display: block;
            width: 100%;
            text-align: center;
            justify-content: center;
        }

        /* About Section */
        .about {
            padding: 6.5rem 0;
            background: rgba(17, 24, 39, 0.25);
            border-top: 1px solid var(--border-card);
            border-bottom: 1px solid var(--border-card);
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-text h2 {
            font-size: 2.4rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .about-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-secondary);
        }

        /* Contact Section */
        .contact {
            padding: 7rem 0;
            text-align: center;
        }

        .contact-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .btn-contact {
            padding: 1rem 2.4rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            transition: var(--transition-premium);
        }

        .btn-wa {
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: var(--accent-green);
        }

        .btn-wa:hover {
            background: var(--accent-green);
            color: #fff;
            box-shadow: 0 12px 22px -3px rgba(16, 185, 129, 0.35);
            transform: translateY(-2px);
            border-color: transparent;
        }

        .btn-email {
            background: rgba(14, 165, 233, 0.05);
            border: 1px solid rgba(14, 165, 233, 0.25);
            color: var(--accent-blue);
        }

        .btn-email:hover {
            background: var(--accent-blue);
            color: #fff;
            box-shadow: 0 12px 22px -3px rgba(14, 165, 233, 0.35);
            transform: translateY(-2px);
            border-color: transparent;
        }

        /* Deployment Info / Security Section */
        .deployment-info {
            background: rgba(17, 24, 39, 0.1);
            border-top: 1px solid var(--border-card);
            border-bottom: 1px solid var(--border-card);
            padding: 4.5rem 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2.5rem;
            text-align: center;
        }

        .info-item h4 {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin-bottom: 0.6rem;
        }

        .info-item p {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .info-item span.highlight {
            background: linear-gradient(135deg, #38bdf8, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Footer */
        footer {
            padding: 3rem 0;
            text-align: center;
            font-size: 0.88rem;
            color: var(--text-muted);
            border-top: 1px solid rgba(255, 255, 255, 0.02);
            background: #02050b;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .bento-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .bento-item.span-2 {
                grid-column: span 2;
            }
            .about-grid {
                grid-template-columns: 1fr;
                gap: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .hero {
                padding: 4.5rem 0 3.5rem;
            }
            .hero h1 {
                font-size: 2.6rem;
                letter-spacing: -1px;
            }
            .hero p {
                font-size: 1.05rem;
            }
            .hero-ctas {
                flex-direction: column;
                gap: 1rem;
            }
            .hero-ctas .btn-primary,
            .hero-ctas .btn-secondary {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
            .bento-grid {
                grid-template-columns: 1fr;
            }
            .bento-item.span-2 {
                grid-column: span 1;
            }
            .mockup-content {
                grid-template-columns: 1fr;
            }
            .mockup-chart-box {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

    <!-- Glowing Aurora Background -->
    <div class="aurora-bg">
        <div class="aurora-blob blob-1"></div>
        <div class="aurora-blob blob-2"></div>
        <div class="aurora-blob blob-3"></div>
        <div class="aurora-blob blob-4"></div>
    </div>

    <!-- Header Navbar -->
    <header>
        <div class="container nav-wrapper">
            <a href="/" class="logo">
                <div class="logo-icon-wrapper">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="url(#logoGradLandingMaster)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <defs>
                            <linearGradient id="logoGradLandingMaster" x1="0%" y1="0%" x2="100%" y2="100%">
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
                        <circle cx="7" cy="15.5" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="7" cy="17" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="7" cy="18.5" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="8.5" cy="16.25" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="8.5" cy="17.75" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="10" cy="17" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />

                        <circle cx="17" cy="15.5" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="17" cy="17" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="17" cy="18.5" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="15.5" cy="16.25" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="15.5" cy="17.75" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                        <circle cx="14" cy="17" r="0.5" fill="url(#logoGradLandingMaster)" stroke="none" />
                    </svg>
                </div>
                <span class="logo-text">NEXSTOCK</span>
            </a>
            <nav class="nav-links">
                <a href="#">Beranda</a>
                <a href="#features">Fitur</a>
                <a href="#about">Tentang Kami</a>
                <a href="#pricing">Harga</a>
                <a href="#contact">Kontak</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero container">
        <div class="status-badge" id="status">
            <div class="status-pulse"></div>
            Single-Tenant Instance: Active
        </div>
        <h1>Sistem Operasional & Finansial Gudang Distributor Pintar</h1>
        <p>Solusi manajemen inventaris kelas enterprise dengan arsitektur terisolasi. Mengintegrasikan penataan rak FEFO otomatis, pencegahan kesalahan pengambilan barang, serta analisis laba kotor real-time khusus owner.</p>
        <div class="hero-ctas">
            <a href="{{ route('login') }}" class="btn-primary">
                Masuk ke Sistem
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
            <a href="#features" class="btn-secondary">Pelajari Fitur</a>
        </div>

        <!-- CSS-only Live Dashboard Mockup -->
        <div class="dashboard-mockup">
            <div class="mockup-header">
                <div class="mockup-dots">
                    <div class="mockup-dot dot-red"></div>
                    <div class="mockup-dot dot-yellow"></div>
                    <div class="mockup-dot dot-green"></div>
                </div>
                <div class="mockup-title">NEXSTOCK — OWNER INSIGHTS</div>
                <div style="width: 40px;"></div>
            </div>
            <div class="mockup-content">
                <div class="mockup-card highlight">
                    <h5>Total Pemasukan</h5>
                    <div class="value" style="color: var(--accent-green);">Rp 152,480,000</div>
                    <div class="desc">Omzet transaksi outbound terkonfirmasi</div>
                </div>
                <div class="mockup-card">
                    <h5>Nilai Aset Stok</h5>
                    <div class="value">Rp 842,500,000</div>
                    <div class="desc">Nilai kapitalisasi modal aktif di rak</div>
                </div>
                <div class="mockup-card">
                    <h5>Total Kerugian</h5>
                    <div class="value" style="color: #f87171;">Rp 1,420,000</div>
                    <div class="desc">Dari laporan kerusakan/expired barang</div>
                </div>
                <div class="mockup-chart-box">
                    <div class="mockup-chart-header">
                        <span>Tren Pemasukan vs Kerugian (7 Hari Terakhir)</span>
                        <span style="color: var(--accent-blue);">Aktif</span>
                    </div>
                    <div class="mockup-chart-bars">
                        <div class="mockup-chart-bar" style="height: 30%;"></div>
                        <div class="mockup-chart-bar" style="height: 45%;"></div>
                        <div class="mockup-chart-bar" style="height: 40%;"></div>
                        <div class="mockup-chart-bar alt" style="height: 70%;"></div>
                        <div class="mockup-chart-bar" style="height: 60%;"></div>
                        <div class="mockup-chart-bar" style="height: 85%;"></div>
                        <div class="mockup-chart-bar alt" style="height: 95%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features Section -->
    <section class="features container" id="features">
        <div class="section-title">
            <h2>Arsitektur Fitur Pintar</h2>
            <p>Dibangun khusus untuk mendigitalisasi operasional gudang Anda secara aman, cepat, dan presisi.</p>
        </div>
        <div class="bento-grid">
            <!-- Bento 1 (Large - span 2) -->
            <div class="bento-item span-2">
                <div>
                    <div class="bento-icon icon-indigo">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    </div>
                    <div class="bento-content">
                        <h3>Analisis Laba Owner & AI Insights</h3>
                        <p>Hak akses eksklusif bagi Owner untuk melihat margin laba kotor, pergerakan fast/slow moving, tren omzet penjualan, serta saran restock strategis berbasis AI secara langsung dan real-time.</p>
                    </div>
                </div>
                <div class="bento-graphic" style="background: rgba(99,102,241,0.03); border-color: rgba(99,102,241,0.15);">
                    <span style="color: var(--accent-indigo); font-weight: 700;">[AI Suggestion]</span> "Beras Pandan Wangi (PRD-01) memiliki perputaran tercepat (1.8 Hari). Naikkan kuota pemesanan PO sebesar 20% untuk mencegah stockout minggu depan."
                </div>
            </div>

            <!-- Bento 2 -->
            <div class="bento-item">
                <div>
                    <div class="bento-icon icon-blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                    </div>
                    <div class="bento-content">
                        <h3>FEFO & Penataan Rak</h3>
                        <p>Sistem secara cerdas menyusun slips picking barang keluar berdasarkan prinsip FEFO (kadaluwarsa terdekat) serta mengoptimalkan kapasitas penempatan rak dinamis.</p>
                    </div>
                </div>
                <div class="bento-graphic">
                    <div class="graphic-fefo-row">
                        <span>Batch A (Exp: 10 Agt)</span>
                        <span class="badge-fefo">Ambil Pertama</span>
                    </div>
                    <div class="graphic-fefo-row">
                        <span>Batch B (Exp: 22 Des)</span>
                        <span class="badge-fefo warn">Antre</span>
                    </div>
                </div>
            </div>

            <!-- Bento 3 -->
            <div class="bento-item">
                <div>
                    <div class="bento-icon icon-green">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="9" x2="20" y2="9"></line><line x1="4" y1="15" x2="20" y2="15"></line><line x1="10" y1="3" x2="8" y2="21"></line><line x1="16" y1="3" x2="14" y2="21"></line></svg>
                    </div>
                    <div class="bento-content">
                        <h3>Anti-Blind Picking Scan</h3>
                        <p>Mengunci pengiriman keluar dari human error. Staff gudang wajib memindai barcode nomor batch fisik produk sebelum pesanan retail dapat diselesaikan.</p>
                    </div>
                </div>
                <div class="bento-graphic" style="text-align: center; color: var(--accent-green); font-weight: 700;">
                    ✓ SCAN MATCHED (BATCH-001)
                </div>
            </div>

            <!-- Bento 4 (Large - span 2) -->
            <div class="bento-item span-2">
                <div>
                    <div class="bento-icon icon-yellow">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2 12 12"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    </div>
                    <div class="bento-content">
                        <h3>Template Diskon Expired Otomatis</h3>
                        <p>Minimalkan barang terbuang (damaged). Tentukan aturan diskon bertahap berdasarkan sisa umur produk. Sistem otomatis menghitung potongan harga jual retail saat proses outbound dibuat.</p>
                    </div>
                </div>
                <div class="bento-graphic" style="background: rgba(245,158,11,0.03); border-color: rgba(245,158,11,0.15);">
                    <div style="display:flex; justify-content:space-between; margin-bottom: 0.25rem;">
                        <span>Aturan Diskon: Sisa Umur < 90 Hari</span>
                        <span style="color: var(--accent-yellow); font-weight: 700;">Potongan 20%</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size: 0.7rem;">
                        <span>Harga Master: Rp 50.000</span>
                        <span>Harga Jual Retail: Rp 40.000 (Auto-Diskon)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container about-grid">
            <div class="about-text">
                <h2>Mendigitalisasi Operasional dengan Keamanan Mutlak.</h2>
                <p>NEXSTOCK dirancang sebagai sistem ERP pergudangan privat yang berjalan eksklusif pada server klien masing-masing (Single-Tenant). Keamanan data finansial, daftar supplier, serta margin profitabilitas Anda terlindungi sepenuhnya dengan enkripsi AES-256 dan isolasi total database.</p>
            </div>
            <div class="dashboard-mockup" style="animation: none;">
                <div class="mockup-header">
                    <div class="mockup-dots"><div class="mockup-dot dot-red"></div><div class="mockup-dot dot-yellow"></div><div class="mockup-dot dot-green"></div></div>
                    <div class="mockup-title">SECURITY STATUS</div>
                </div>
                <div style="padding: 1.5rem 2rem; font-size: 0.85rem; font-family: monospace; color: var(--text-secondary);">
                    <p style="color: var(--accent-green); margin-bottom: 0.5rem;">$ nexstock-security --status</p>
                    <p>[INFO] Database Connection: ENCRYPTED</p>
                    <p>[INFO] Encryption Method: AES-256-CBC</p>
                    <p>[INFO] Tenant Isolation Scope: LOCAL_INSTANCE</p>
                    <p>[INFO] Automated Backups: ACTIVE (Daily, 02:00 AM)</p>
                    <p style="color: var(--accent-blue); margin-top: 0.5rem;">[STATUS] System Secure.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing container" id="pricing">
        <div class="section-title">
            <h2>Skema Lisensi & Pilihan Investasi</h2>
            <p>Pilih model kepemilikan software yang paling pas untuk ekosistem dan skala bisnis logistik Anda.</p>
        </div>
        <div class="pricing-grid">
            <!-- Card 1 -->
            <div class="pricing-card">
                <h3>Lisensi Mandiri</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">Self-Hosted / Server Lokal</p>
                <div class="price">Rp 15jt<span> / Sekali Bayar</span></div>
                <ul>
                    <li>Dideploy di server lokal kantor/cloud mandiri</li>
                    <li>Lisensi aktif selamanya tanpa biaya bulanan</li>
                    <li>Dukungan setup awal & pemindahan database</li>
                    <li>Garansi penanganan bug gratis 1 tahun</li>
                </ul>
                <a href="#contact" class="btn-primary" style="margin-top: 1rem;">Pilih Lisensi</a>
            </div>
            <!-- Card 2 -->
            <div class="pricing-card premium">
                <h3>Managed Cloud</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">Full Hosting & Managed Services</p>
                <div class="price">Rp 1.2jt<span> / Bulan</span></div>
                <ul>
                    <li>Server cloud privat aman terdedikasi</li>
                    <li>Layanan backup data harian otomatis</li>
                    <li>Pembaruan fitur sistem berkala</li>
                    <li>Bantuan teknis VIP & Support 24/7</li>
                </ul>
                <a href="#contact" class="btn-primary" style="margin-top: 1rem;">Hubungi Sales</a>
            </div>
        </div>
    </section>

    <!-- Deployment Info Section -->
    <section class="deployment-info">
        <div class="container info-grid">
            <div class="info-item">
                <h4>Arsitektur Deployment</h4>
                <p><span class="highlight">Single-Tenant</span> (Privat)</p>
            </div>
            <div class="info-item">
                <h4>Keamanan Data</h4>
                <p>AES-256 (Terenkripsi)</p>
            </div>
            <div class="info-item">
                <h4>Status Sistem</h4>
                <p><span class="highlight">Normal / Online</span></p>
            </div>
            <div class="info-item">
                <h4>Pemeliharaan</h4>
                <p>Managed Backup</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact container" id="contact">
        <div class="section-title">
            <h2>Hubungi Tim Teknis Kami</h2>
            <p>Konsultasikan kebutuhan digitalisasi gudang Anda atau jadwalkan demo gratis sekarang.</p>
        </div>
        <div class="contact-buttons">
            <a href="https://wa.me/6281234567890" target="_blank" class="btn-contact btn-wa">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                WhatsApp Business
            </a>
            <a href="mailto:info@nexstock.com" class="btn-contact btn-email">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                Email Sales
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} NEXSTOCK. Hak Cipta Dilindungi Undang-Undang.</p>
        </div>
    </footer>

</body>
</html>
