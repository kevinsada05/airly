<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Airly' }}</title>
    <link rel="stylesheet" href="https://fonts.cdnfonts.com/css/hangout">
    <link href="https://fonts.cdnfonts.com/css/hagrid-trial?styles=52966" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/quicksand" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/unbounded" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/auromiya" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/maria-2" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f4ee;
            --ink: #1a1c17;
            --muted: #6a6d63;
            --brand: #3f7a56;
            --brand-dark: #2f5f43;
            --accent: #f0b24a;
            --card: #ffffff;
            --border: #e2e5da;
            --shadow: 0 20px 50px rgba(26, 28, 23, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'unbounded', sans-serif;
            background: radial-gradient(circle at top, #f1f4ee 0%, var(--bg) 45%, #faf5ea 100%);
            color: var(--ink);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        form {
            margin: 0;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 6vw;
            gap: 16px;
        }

        .logo {
            font-family: 'Quarantype hangout', sans-serif;
            font-weight: 700;
            font-size: 50px;
            letter-spacing: 0.5px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-weight: 600;
            font-family: 'hagrid text trial', sans-serif;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
            box-shadow: 0 12px 30px rgba(31, 111, 74, 0.25);
        }

        .btn-primary:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn-ghost {
            border-color: var(--border);
            color: var(--ink);
            background: #fff;
        }

        .btn-ghost:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        .btn-ghost.active {
            border-color: var(--brand);
            color: var(--brand);
            background: rgba(63, 122, 86, 0.1);
            font-weight: 700;
        }

        .instagram-link {
            color: var(--brand);
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .nav-toggle {
            display: none;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--ink);
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 600;
            font-family: 'unbounded', sans-serif;
        }

        .nav-toggle span {
            width: 18px;
            height: 2px;
            background: var(--ink);
            display: block;
            position: relative;
        }

        .nav-toggle span::before,
        .nav-toggle span::after {
            content: "";
            position: absolute;
            left: 0;
            width: 18px;
            height: 2px;
            background: var(--ink);
        }

        .nav-toggle span::before {
            top: -6px;
        }

        .nav-toggle span::after {
            top: 6px;
        }

        .container {
            width: min(1100px, 90vw);
            margin: 0 auto;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px;
            box-shadow: var(--shadow);
        }

        .card.accent {
            background: linear-gradient(140deg, rgba(31, 111, 74, 0.08), rgba(242, 185, 75, 0.12));
            border-color: rgba(31, 111, 74, 0.2);
        }

        .hero {
            padding: 40px 0 70px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 28px;
            align-items: center;
        }

        .eyebrow {
            font-family: 'quicksand', sans-serif;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 12px;
            color: var(--muted);
        }

        h1, h2 {
            font-family: 'auromiya', sans-serif;
            margin: 12px 0;
        }

        h3 {
            font-family: 'unbounded', sans-serif;
            margin: 12px 0;
        }

        h1 {
            font-size: clamp(32px, 5vw, 54px);
            line-height: 1.05;
        }

        h2 {
            font-size: clamp(24px, 3.2vw, 36px);
            line-height: 1.15;
        }

        .lead {
            font-size: 1.05rem;
            line-height: 1.7;
        }

        p {
            color: var(--muted);
            line-height: 1.6;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(242, 185, 75, 0.18);
            color: #8a5d10;
            font-weight: 600;
            font-size: 12px;
        }

        .section {
            padding: 20px 0 70px;
        }

        .section-alt {
            background: #f7faf6;
            border-radius: 32px;
            padding: 50px 0;
            margin-bottom: 24px;
        }

        .section-contrast {
            background: linear-gradient(135deg, #ffffff 0%, #edf3ee 100%);
            color: var(--ink);
            border-radius: 32px;
            padding: 60px 0;
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }

        .text-narrow {
            max-width: 520px;
        }

        .split-tight {
            grid-template-columns: 1fr;
            gap: 22px;
        }

        .steps-compact {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .centered {
            max-width: 900px;
            margin: 0 auto;
        }

        .center-text {
            text-align: center;
        }

        .center-text .feature-list li {
            grid-template-columns: 10px 1fr;
            justify-items: start;
        }

        .section-angled {
            background: linear-gradient(135deg, #ffffff 0%, #f3f8f4 100%);
            border-radius: 36px;
            padding: 60px 0;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .section-angled::after {
            content: "";
            position: absolute;
            inset: -30% -10% auto auto;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(242, 185, 75, 0.35), transparent 60%);
            transform: rotate(18deg);
        }

        .section-ink {
            background: #0f1b12;
            color: #eef4ef;
            border-radius: 36px;
            padding: 60px 0;
            margin-bottom: 24px;
        }

        .section-ink p {
            color: #c3d0c7;
        }

        .section-slate {
            background: linear-gradient(120deg, #0f1b12 0%, #173022 60%, #1f3a2a 100%);
            color: #f1f6f2;
            border-radius: 32px;
            padding: 50px 0;
            margin-bottom: 24px;
        }

        .section-slate p {
            color: #c7d3cb;
        }

        .section-header {
            display: grid;
            gap: 8px;
            margin-bottom: 20px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .list-card {
            padding: 18px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid var(--border);
        }

        .list-card.soft {
            background: #f7faf6;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(31, 111, 74, 0.12);
            color: var(--brand);
            font-weight: 600;
            font-size: 12px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .stat {
            padding: 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .stat strong {
            display: block;
            font-size: 26px;
            font-family: 'unbounded', sans-serif;
        }

        .split {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 28px;
            align-items: center;
        }

        .reason-card {
            padding: 18px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid var(--border);
            display: grid;
            gap: 6px;
        }

        .reason-stack {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            align-content: start;
            position: relative;
        }

        .reason-stack .reason-card:last-child {
            grid-column: 1 / -1;
            max-width: 70%;
            justify-self: center;
            transform: translateY(-6px);
        }

        @media (max-width: 800px) {
            .reason-stack {
                grid-template-columns: 1fr;
            }

            .reason-stack .reason-card:last-child {
                max-width: none;
                justify-self: stretch;
                transform: none;
            }
        }

        .reason-list {
            list-style: none;
            padding: 0;
            margin: 16px 0 0;
            display: grid;
            gap: 10px;
        }

        .reason-list li {
            display: grid;
            grid-template-columns: 10px 1fr;
            gap: 10px;
            align-items: start;
            color: var(--muted);
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--accent);
            margin-top: 6px;
        }

        .timeline {
            display: grid;
            gap: 14px;
        }

        .timeline-item {
            display: grid;
            grid-template-columns: 64px 1fr;
            gap: 14px;
            align-items: start;
            padding: 16px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid var(--border);
        }

        .timeline-step {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(31, 111, 74, 0.12);
            color: var(--brand);
            font-weight: 700;
            font-family: 'unbounded', sans-serif;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            align-items: start;
        }

        .step {
            padding: 10px 0 12px;
            border-top: 2px solid rgba(31, 111, 74, 0.18);
        }

        .step span {
            font-weight: 700;
            font-family: 'auromiya', sans-serif;
            color: var(--brand);
        }

        .step-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 18px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .step-card::after {
            content: "";
            position: absolute;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(31, 111, 74, 0.08);
            right: -10px;
            top: -10px;
        }

        .step-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .icon-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .icon-chip span {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: rgba(31, 111, 74, 0.12);
            color: var(--brand);
            font-weight: 700;
        }

        .divider-list {
            display: grid;
            gap: 14px;
        }

        .divider-item {
            padding-bottom: 14px;
            border-bottom: 1px dashed var(--border);
        }

        .pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 12px 0 0;
            padding: 0;
            list-style: none;
        }

        .pill-list li {
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            font-weight: 600;
        }

        .soft-panel {
            border-radius: 26px;
            padding: 26px;
            background: linear-gradient(145deg, #ffffff 0%, #f6faf7 100%);
            border: 1px solid var(--border);
        }

        .zigzag {
            display: grid;
            gap: 14px;
        }

        .zigzag-item {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 14px;
            align-items: start;
            padding: 16px 18px;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid var(--border);
        }

        .zigzag-item:nth-child(even) {
            transform: translateX(14px);
            background: #f7faf6;
        }

        .badge-round {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(242, 185, 75, 0.2);
            color: #8a5d10;
            font-weight: 700;
        }

        .orbit {
            display: grid;
            gap: 12px;
        }

        .orbit-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .orbit-pill {
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #ffffff;
            font-weight: 600;
        }

        .strips {
            display: grid;
            gap: 12px;
        }

        .strip {
            padding: 12px 16px;
            border-left: 4px solid var(--accent);
            background: rgba(255, 255, 255, 0.06);
            border-radius: 12px;
        }

        .icon-card {
            border-radius: 20px;
            padding: 18px;
            background: #ffffff;
            border: 1px solid var(--border);
            display: grid;
            gap: 10px;
        }

        .icon-badge {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(242, 185, 75, 0.22);
            color: #8a5d10;
            font-weight: 700;
        }

        .feature-stack {
            position: relative;
            display: grid;
            gap: 16px;
        }

        .feature-stack .icon-card {
            border: none;
            background: linear-gradient(135deg, #ffffff 0%, #f6faf6 100%);
            box-shadow: 0 18px 40px rgba(15, 27, 18, 0.12);
        }

        .feature-stack .icon-card:nth-child(2) {
            margin-left: 32px;
            transform: translateY(-6px);
        }

        .feature-stack .icon-card:nth-child(3) {
            margin-left: 12px;
            transform: translateY(-12px);
        }

        .accent-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 22px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .accent-grid {
                grid-template-columns: 1fr;
            }

            .feature-stack .icon-card:nth-child(2),
            .feature-stack .icon-card:nth-child(3) {
                margin-left: 0;
                transform: none;
            }
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
        }

        .feature {
            border-radius: 18px;
            padding: 18px;
            background: #f8faf7;
            border: 1px solid var(--border);
        }

        .editorial-split {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 26px;
            align-items: center;
        }

        .editorial-quote {
            font-family: 'quicksand', serif;
            font-size: clamp(18px, 2.2vw, 26px);
            line-height: 1.5;
            padding: 18px 0 18px 18px;
            border-left: 3px solid var(--accent);
        }

        .editorial-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 12px;
        }

        .editorial-list li {
            display: grid;
            grid-template-columns: 40px 1fr;
            gap: 12px;
            align-items: start;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(31, 111, 74, 0.12);
        }

        .editorial-list li:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .editorial-index {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: rgba(31, 111, 74, 0.12);
            color: var(--brand);
            font-weight: 700;
            font-family: 'auromiya', sans-serif;
        }

        @media (max-width: 900px) {
            .editorial-split {
                grid-template-columns: 1fr;
            }
        }

        .steps-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-top: 16px;
        }

        .step-tile {
            padding: 12px 0 14px;
            border-top: 2px solid rgba(31, 111, 74, 0.18);
        }

        .step-circle {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(31, 111, 74, 0.12);
            color: var(--brand);
            font-weight: 700;
            font-family: 'auromiya', sans-serif;
        }

        .section-contrast .step-tile {
            background: #ffffff;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid var(--border);
        }

        .section-contrast .steps-row {
            gap: 14px;
        }

        .section-contrast .feature-list li {
            color: var(--muted);
        }

        .feature-list {
            list-style: none;
            margin: 16px 0 0;
            padding: 0;
            display: grid;
            gap: 12px;
        }

        .feature-list li {
            display: grid;
            grid-template-columns: 18px 1fr;
            gap: 10px;
            align-items: start;
            color: var(--muted);
        }

        .feature-list li span {
            width: 10px;
            height: 10px;
            border-radius: 4px;
            background: var(--accent);
            margin-top: 6px;
        }

        .pill-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .pill-cloud span {
            padding: 10px 16px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid var(--border);
            font-weight: 600;
        }

        .callout {
            margin-top: 18px;
            padding: 16px 0 0;
            border-top: 2px solid rgba(31, 111, 74, 0.2);
        }

        .form {
            display: grid;
            gap: 14px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        label {
            font-weight: 600;
            font-size: 14px;
            font-family: 'unbounded', sans-serif;
        }

        input {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 15px;
            font-family: 'unbounded', sans-serif;
        }

        input:focus {
            outline: 2px solid rgba(31, 111, 74, 0.25);
            border-color: var(--brand);
        }

        .alert {
            background: #fff4f4;
            border: 1px solid #f2b5b5;
            color: #a12828;
            padding: 10px 12px;
            border-radius: 12px;
        }

        .footer {
            margin-top: auto;
            padding: 30px 6vw 40px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 700px) {
            .nav {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-top {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .nav-toggle {
                display: inline-flex;
            }

            .nav-links {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding-top: 14px;
                display: none;
            }

            .nav-links.open {
                display: flex;
            }

            .card {
                padding: 22px;
            }

            .section-alt {
                border-radius: 22px;
                padding: 32px 0;
            }

            .section-contrast {
                border-radius: 22px;
                padding: 36px 0;
            }

            .section-angled,
            .section-ink {
                border-radius: 22px;
                padding: 36px 0;
            }

            .zigzag-item:nth-child(even) {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <nav class="nav">
            <div class="nav-top">
                <a class="logo" href="{{ route('home') }}">Airly</a>
                <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-links">
                    <span></span>
                    Menu
                </button>
            </div>
            <div class="nav-links" id="nav-links">
                @auth
                    <a class="btn btn-ghost {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Paneli</a>
                    <a class="btn btn-ghost {{ request()->routeIs('uploads.*') ? 'active' : '' }}" href="{{ route('uploads.index') }}">Ngarkimet</a>
                    <a class="btn btn-ghost {{ request()->routeIs('scanner.*') ? 'active' : '' }}" href="{{ route('scanner.index') }}">Skaneri</a>
                    <a class="btn btn-ghost {{ request()->routeIs('zones.*') ? 'active' : '' }}" href="{{ route('zones.index') }}">Zonat</a>
                    <a class="btn btn-ghost {{ request()->routeIs('map.*') ? 'active' : '' }}" href="{{ route('map.index') }}">Harta</a>
                    <a class="btn btn-ghost" href="#">Komuniteti</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-primary" type="submit">Dil</button>
                    </form>
                @else
                    <a class="btn btn-ghost {{ request()->routeIs('uploads.*') ? 'active' : '' }}" href="{{ route('uploads.index') }}">Ngarkimet</a>
                    <a class="btn btn-ghost {{ request()->routeIs('scanner.*') ? 'active' : '' }}" href="{{ route('scanner.index') }}">Skaneri</a>
                    <a class="btn btn-ghost {{ request()->routeIs('zones.*') ? 'active' : '' }}" href="{{ route('zones.index') }}">Zonat</a>
                    <a class="btn btn-ghost {{ request()->routeIs('map.*') ? 'active' : '' }}" href="{{ route('map.index') }}">Harta</a>
                    <a class="btn btn-ghost {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Kyçu</a>
                @endauth
            </div>
        </nav>

        <main class="container">
            @yield('content')
        </main>

        <footer class="footer">
            @hasSection('footer_content')
                @yield('footer_content')
            @else
                Monitorim i ndotjes me prova vizuale nga dronët dhe fotografia në terren.
            @endif
        </footer>
    </div>
    <script>
        const navToggle = document.querySelector('.nav-toggle');
        const navLinks = document.getElementById('nav-links');

        if (navToggle && navLinks) {
            navToggle.addEventListener('click', () => {
                const isOpen = navLinks.classList.toggle('open');
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }
    </script>
</body>
</html>
