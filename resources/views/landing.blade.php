<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPC Online Voting & Event Management System</title>
    <link rel="icon" href="{{ asset('images/spc-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --maroon:   #800020;
            --maroon-d: #5c0015;
            --maroon-l: #a0153e;
            --black:    #111827;
            --gray:     #6b7280;
            --light:    #f9fafb;
            --border:   #e5e7eb;
            --white:    #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--black);
            background: var(--white);
            overflow-x: hidden;
        }

        /* ─── NAVBAR ─────────────────────────────────────── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 68px;
            display: flex; align-items: center; justify-content: space-between;
            transition: box-shadow .3s;
        }
        nav.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.1); }

        .nav-brand {
            display: flex; align-items: center; gap: .75rem;
            text-decoration: none;
        }
        .nav-logo {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-d));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem; font-weight: 900; flex-shrink: 0;
        }
        .nav-brand-text {
            font-size: .9rem; font-weight: 800; color: var(--maroon);
            line-height: 1.25;
        }
        .nav-brand-sub { font-size: .68rem; font-weight: 500; color: var(--gray); }

        .nav-links {
            display: flex; align-items: center; gap: 2.2rem;
            list-style: none;
        }
        .nav-links a {
            text-decoration: none; font-size: .87rem; font-weight: 600;
            color: var(--black); transition: color .2s;
        }
        .nav-links a:hover { color: var(--maroon); }

        .nav-actions { display: flex; align-items: center; gap: .75rem; }
        .btn-ghost {
            padding: .5rem 1.1rem; border-radius: 8px;
            background: transparent; border: none;
            font-size: .87rem; font-weight: 600; color: var(--black);
            cursor: pointer; text-decoration: none; transition: background .2s, color .2s;
        }
        .btn-ghost:hover { background: var(--light); }
        .btn-primary {
            padding: .55rem 1.3rem; border-radius: 8px;
            background: var(--maroon); color: #fff;
            border: 2px solid var(--maroon);
            font-size: .87rem; font-weight: 700;
            cursor: pointer; text-decoration: none; transition: background .2s, transform .15s;
        }
        .btn-primary:hover { background: var(--maroon-d); border-color: var(--maroon-d); transform: translateY(-1px); }

        .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
        .hamburger span { display: block; width: 24px; height: 2px; background: var(--black); border-radius: 2px; transition: .3s; }

        /* ─── HERO ───────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            padding: 110px 2rem 80px;
            background: #ffffff;
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        /* 3-D canvas fills the hero */
        #voteCanvas {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0; pointer-events: none; display: block;
        }
        /* lift all hero children above canvas */
        .hero-bg-blob, .hero-bg-blob-2 { display: none; }
        .hero-inner {
            position: relative; z-index: 1;
            max-width: 1200px; width: 100%;
            display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;
        }

        /* hero text — dark on white background */
        .hero-badge {
            background: rgba(128,0,32,.08);
            border-color: rgba(128,0,32,.2);
            color: var(--maroon);
        }
        .hero h1 { color: var(--black); }
        .hero h1 span { color: var(--maroon); }
        .hero-sub { color: var(--gray); }
        .btn-hero-outline {
            color: var(--black);
            border-color: var(--border);
        }
        .btn-hero-outline:hover {
            border-color: var(--maroon); color: var(--maroon);
            background: rgba(128,0,32,.04);
        }
        .hero-stat-num  { color: var(--black); }
        .hero-stat-label { color: var(--gray); }

        .hero-badge i { font-size: .75rem; }

        .hero h1 {
            font-size: clamp(2.2rem, 4vw, 3.4rem);
            font-weight: 900; line-height: 1.13;
            color: var(--black); margin-bottom: 1.25rem;
        }
        .hero h1 span { color: var(--maroon); }

        .hero-sub {
            font-size: 1.05rem; color: var(--gray); line-height: 1.75;
            margin-bottom: 2.2rem; font-weight: 400; max-width: 500px;
        }

        .hero-cta { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 3rem; }

        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: .6rem;
            padding: .9rem 2rem; border-radius: 10px;
            background: var(--maroon); color: #fff;
            font-size: 1rem; font-weight: 700; text-decoration: none;
            border: 2px solid var(--maroon);
            transition: background .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 8px 24px rgba(128,0,32,.3);
        }
        .btn-hero-primary:hover {
            background: var(--maroon-d); border-color: var(--maroon-d);
            transform: translateY(-2px); box-shadow: 0 12px 32px rgba(128,0,32,.4);
        }
        .btn-hero-outline {
            display: inline-flex; align-items: center; gap: .6rem;
            padding: .9rem 2rem; border-radius: 10px;
            background: transparent; color: var(--black);
            font-size: 1rem; font-weight: 700; text-decoration: none;
            border: 2px solid var(--border);
            transition: border-color .2s, background .2s, transform .15s;
        }
        .btn-hero-outline:hover {
            border-color: var(--maroon); color: var(--maroon);
            background: rgba(128,0,32,.04); transform: translateY(-2px);
        }

        /* Stats under CTA */
        .hero-stats {
            display: flex; gap: 2rem;
        }
        .hero-stat-num {
            font-size: 1.5rem; font-weight: 900; color: var(--black); display: block;
        }
        .hero-stat-label { font-size: .78rem; color: var(--gray); font-weight: 500; }

        /* ─── HERO VISUAL (right column) ─────────────────── */
        .hero-visual { position: relative; }

        /* Hero left glass card */
        .hero-left-card {
            background: rgba(255,255,255,0.72);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.85);
            border-radius: 24px;
            padding: 2.5rem 2.75rem;
            box-shadow: 0 8px 40px rgba(128,0,32,0.10), 0 2px 12px rgba(0,0,0,0.07);
        }
        .hero-left-card h1 {
            text-shadow: 0 2px 12px rgba(128,0,32,0.10);
        }
        .btn-hero-primary {
            box-shadow: 0 8px 24px rgba(128,0,32,.35), 0 2px 8px rgba(0,0,0,.12) !important;
        }
        .btn-hero-outline {
            box-shadow: 0 4px 16px rgba(0,0,0,.10);
        }
        .float-badge {
            position: absolute; background: #fff;
            border-radius: 12px; padding: .6rem 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            font-size: .78rem; font-weight: 700; color: var(--black);
            display: flex; align-items: center; gap: .45rem;
            animation: floatBadge 3s ease-in-out infinite;
            border: 1px solid var(--border);
        }
        .float-badge i { color: var(--maroon); }
        .float-badge-1 { top: 10px; left: -24px; animation-delay: 0s; }
        .float-badge-2 { top: 30px; right: -24px; animation-delay: 1s; }
        .float-badge-3 { bottom: 90px; left: -18px; animation-delay: 1.8s; }

        @keyframes floatBadge {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }

        /* ─── TRUSTED BY ──────────────────────────────────── */
        .trusted {
            background: var(--light);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .trusted p {
            font-size: .82rem; color: var(--gray); font-weight: 600;
            text-transform: uppercase; letter-spacing: .06em; margin-bottom: 1.5rem;
        }
        .trusted-logos {
            display: flex; justify-content: center; align-items: center;
            gap: 3rem; flex-wrap: wrap;
        }
        .trusted-logo {
            font-size: 1.1rem; font-weight: 800; color: #9ca3af;
            letter-spacing: -.01em; transition: color .2s;
        }
        .trusted-logo:hover { color: var(--maroon); }

        /* ─── FEATURES ────────────────────────────────────── */
        section { padding: 6rem 2rem; }

        .section-inner { max-width: 1200px; margin: 0 auto; }

        .section-tag {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(128,0,32,.08); color: var(--maroon);
            border-radius: 50px; padding: .3rem .9rem;
            font-size: .75rem; font-weight: 700;
            border: 1px solid rgba(128,0,32,.18); margin-bottom: 1rem;
        }
        .section-title {
            font-size: clamp(1.8rem,3vw,2.6rem); font-weight: 900;
            color: var(--black); margin-bottom: .9rem; line-height: 1.2;
        }
        .section-title span { color: var(--maroon); }
        .section-sub {
            font-size: 1rem; color: var(--gray); max-width: 560px;
            line-height: 1.75; font-weight: 400;
        }

        .features-grid {
            display: grid; grid-template-columns: repeat(3,1fr);
            gap: 1.75rem; margin-top: 3.5rem;
        }
        .feature-card {
            background: #fff; border-radius: 18px;
            padding: 2rem; border: 1.5px solid var(--border);
            transition: box-shadow .25s, transform .25s, border-color .25s;
        }
        .feature-card:hover {
            box-shadow: 0 16px 40px rgba(128,0,32,.1);
            transform: translateY(-4px); border-color: rgba(128,0,32,.25);
        }
        .feature-icon {
            width: 56px; height: 56px; border-radius: 14px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-l));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.3rem; margin-bottom: 1.25rem;
        }
        .feature-card h3 {
            font-size: 1.05rem; font-weight: 800; margin-bottom: .6rem; color: var(--black);
        }
        .feature-card p {
            font-size: .88rem; color: var(--gray); line-height: 1.7;
        }

        /* ─── HOW IT WORKS ────────────────────────────────── */
        .how-section { background: var(--light); }

        .steps-grid {
            display: grid; grid-template-columns: repeat(4,1fr);
            gap: 1.5rem; margin-top: 3.5rem; position: relative;
        }
        .steps-grid::before {
            content: '';
            position: absolute; top: 36px; left: 10%; right: 10%;
            height: 2px; background: linear-gradient(90deg, var(--maroon), var(--maroon-l));
            z-index: 0;
        }
        .step-card {
            background: #fff; border-radius: 18px; padding: 2rem 1.5rem;
            text-align: center; border: 1.5px solid var(--border);
            position: relative; z-index: 1;
            transition: box-shadow .25s, transform .25s;
        }
        .step-card:hover { box-shadow: 0 12px 32px rgba(128,0,32,.1); transform: translateY(-4px); }
        .step-number {
            width: 52px; height: 52px; border-radius: 50%;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-l));
            color: #fff; font-size: 1.2rem; font-weight: 900;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
        }
        .step-card h3 { font-size: .97rem; font-weight: 800; margin-bottom: .5rem; }
        .step-card p  { font-size: .83rem; color: var(--gray); line-height: 1.65; }

        /* ─── STATS BANNER ────────────────────────────────── */
        .stats-banner {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-d) 100%);
            color: #fff; text-align: center; padding: 5rem 2rem;
        }
        .stats-banner .section-title { color: #fff; }
        .stats-banner .section-sub   { color: rgba(255,255,255,.75); margin: 0 auto; }
        .stats-row {
            display: flex; justify-content: center; gap: 5rem;
            flex-wrap: wrap; margin-top: 3.5rem;
        }
        .stat-block { text-align: center; }
        .stat-block .big-num {
            font-size: 3rem; font-weight: 900; color: #fff; display: block;
            line-height: 1;
        }
        .stat-block .stat-dsc { font-size: .85rem; color: rgba(255,255,255,.7); margin-top: .45rem; font-weight: 500; }

        /* ─── TESTIMONIAL ─────────────────────────────────── */
        .testimonial-section { background: #fff; }
        .testimonial-grid {
            display: grid; grid-template-columns: repeat(3,1fr);
            gap: 1.75rem; margin-top: 3.5rem;
        }
        .testimonial-card {
            background: var(--light); border-radius: 18px; padding: 2rem;
            border: 1.5px solid var(--border);
        }
        .testi-stars { color: var(--maroon); font-size: .85rem; margin-bottom: 1rem; }
        .testi-text  { font-size: .9rem; color: #374151; line-height: 1.75; font-style: italic; margin-bottom: 1.5rem; }
        .testi-author { display: flex; align-items: center; gap: .75rem; }
        .testi-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-l));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: .9rem; flex-shrink: 0;
        }
        .testi-name  { font-size: .88rem; font-weight: 700; color: var(--black); }
        .testi-role  { font-size: .76rem; color: var(--gray); }

        /* ─── CTA BANNER ──────────────────────────────────── */
        .cta-banner {
            background: var(--light); padding: 6rem 2rem; text-align: center;
        }
        .cta-banner .section-title { margin: 0 auto .9rem; max-width: 700px; }
        .cta-banner .section-sub   { margin: 0 auto 2.5rem; }
        .cta-actions { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }

        /* ─── FOOTER ──────────────────────────────────────── */
        footer {
            background: var(--black); color: rgba(255,255,255,.8);
            padding: 4rem 2rem 2rem;
        }
        .footer-grid {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem; padding-bottom: 3rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .footer-brand .nav-logo { border-radius: 12px; margin-bottom: .75rem; }
        .footer-brand-name { font-size: 1rem; font-weight: 800; color: #fff; margin-bottom: .35rem; }
        .footer-brand-sub  { font-size: .8rem; color: rgba(255,255,255,.5); line-height: 1.7; max-width: 220px; }

        .footer-col h4 { font-size: .82rem; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 1.1rem; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: .65rem; }
        .footer-col ul li a { color: rgba(255,255,255,.55); font-size: .84rem; text-decoration: none; transition: color .2s; }
        .footer-col ul li a:hover { color: rgba(128,0,32,.9); }

        .footer-bottom {
            max-width: 1200px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 1.75rem; flex-wrap: wrap; gap: .75rem;
        }
        .footer-copy { font-size: .8rem; color: rgba(255,255,255,.35); }
        .footer-links { display: flex; gap: 1.5rem; }
        .footer-links a { font-size: .8rem; color: rgba(255,255,255,.35); text-decoration: none; }
        .footer-links a:hover { color: rgba(255,255,255,.7); }

        /* ─── MOBILE NAV OVERLAY ──────────────────────────── */
        .mobile-nav {
            display: none;
            position: fixed; inset: 0; background: #fff; z-index: 999;
            flex-direction: column; padding: 5rem 2rem 2rem;
            gap: 1.5rem;
        }
        .mobile-nav.open { display: flex; }
        .mobile-nav a {
            font-size: 1.1rem; font-weight: 700; color: var(--black);
            text-decoration: none; padding: .75rem 0;
            border-bottom: 1px solid var(--border);
        }
        .mobile-nav a:hover { color: var(--maroon); }

        /* ─── ANIMATIONS ──────────────────────────────────── */
        .fade-up {
            opacity: 0; transform: translateY(30px);
            transition: opacity .6s ease, transform .6s ease;
        }
        .fade-up.visible { opacity: 1; transform: translateY(0); }

        /* ─── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 1024px) {
            .features-grid   { grid-template-columns: repeat(2,1fr); }
            .steps-grid      { grid-template-columns: repeat(2,1fr); }
            .steps-grid::before { display: none; }
            .testimonial-grid { grid-template-columns: repeat(2,1fr); }
            .footer-grid     { grid-template-columns: 1fr 1fr; gap: 2rem; }
        }
        @media (max-width: 768px) {
            .hero-inner      { grid-template-columns: 1fr; gap: 2.5rem; }
            .hero-visual     { order: -1; }
            .hero-stats      { gap: 1.5rem; }
            .nav-links, .nav-actions { display: none; }
            .hamburger       { display: flex; }
            .features-grid   { grid-template-columns: 1fr; }
            .steps-grid      { grid-template-columns: 1fr; }
            .testimonial-grid{ grid-template-columns: 1fr; }
            .stats-row       { gap: 2.5rem; }
            .footer-grid     { grid-template-columns: 1fr; gap: 2rem; }
            .float-badge-1, .float-badge-2, .float-badge-3 { display: none; }
        }
    </style>
</head>
<body>

<!-- ─── NAVBAR ─────────────────────────────────────────────── -->
<nav id="mainNav">
    <a href="{{ url('/') }}" class="nav-brand">
        <img src="{{ asset('images/spc-logo.png') }}" alt="SPC Logo"
             style="width:42px;height:42px;object-fit:contain;border-radius:8px;flex-shrink:0;">
        <div>
            <div class="nav-brand-text">SPC VoteSys</div>
            <div class="nav-brand-sub">Online Voting and Event Management System for SPC</div>
        </div>
    </a>

    <ul class="nav-links">
        <li><a href="#features">Features</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>

    <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn-ghost">Sign In</a>
        <a href="{{ route('register') }}" class="btn-primary"><i class="fa-solid fa-paper-plane" style="margin-right:.35rem;"></i>Register</a>
    </div>

    <div class="hamburger" id="hamburger" onclick="toggleMobileNav()">
        <span></span><span></span><span></span>
    </div>
</nav>

<!-- Mobile Nav -->
<div class="mobile-nav" id="mobileNav">
    <a href="#features" onclick="toggleMobileNav()">Features</a>
    <a href="#how-it-works" onclick="toggleMobileNav()">How It Works</a>
    <a href="#about" onclick="toggleMobileNav()">About</a>
    <a href="#contact" onclick="toggleMobileNav()">Contact</a>
    <a href="{{ route('login') }}" style="color: var(--maroon);">Sign In</a>
    <a href="{{ route('register') }}" style="background: var(--maroon); color: #fff; border-radius: 10px; padding: .85rem 1.5rem; border: none; text-align: center;">Get Started</a>
</div>

<!-- ─── HERO ───────────────────────────────────────────────── -->
<section class="hero" id="home">
    <canvas id="voteCanvas"></canvas>

    <div class="hero-inner">
        <!-- LEFT -->
        <div class="hero-left-card">
            <div class="hero-badge fade-up">
                <i class="fa-solid fa-fire"></i>
                Trusted by SPC Students &amp; Faculty
            </div>

            <h1 class="fade-up" style="transition-delay:.1s;">
                Easy <span>Online Voting</span> &amp;<br>Event Excellence
            </h1>

            <p class="hero-sub fade-up" style="transition-delay:.2s;">
                SPC's all-in-one platform for campus elections and student events.
                Secure, transparent, and easy to use — built for the SPC community.
            </p>

            <div class="hero-cta fade-up" style="transition-delay:.3s;">
                <a href="{{ route('register') }}" class="btn-hero-primary">
                    <i class="fa-solid fa-paper-plane"></i> Register
                </a>
                <a href="#how-it-works" class="btn-hero-outline">
                    <i class="fa-solid fa-play-circle"></i> See How It Works
                </a>
            </div>

            <div class="hero-stats fade-up" style="transition-delay:.4s;">
                <div>
                    <span class="hero-stat-num" data-count="{{ $voteCount ?? 1240 }}">0</span>
                    <span class="hero-stat-label">Votes Cast</span>
                </div>
                <div>
                    <span class="hero-stat-num" data-count="{{ $electionCount ?? 18 }}">0</span>
                    <span class="hero-stat-label">Elections Held</span>
                </div>
                <div>
                    <span class="hero-stat-num" data-count="{{ $studentCount ?? 620 }}">0</span>
                    <span class="hero-stat-label">Students Registered</span>
                </div>
            </div>
        </div>

        <!-- RIGHT: Visual -->
        <div class="hero-visual fade-up" style="transition-delay:.2s;">
            <!-- Floating badges -->
            <div class="float-badge float-badge-1">
                <i class="fa-solid fa-check-circle"></i> Vote Submitted!
            </div>
            <div class="float-badge float-badge-2">
                <i class="fa-solid fa-calendar-check"></i> Event Live
            </div>
            <div class="float-badge float-badge-3">
                <i class="fa-solid fa-shield-halved"></i> Secure & Verified
            </div>

        </div>
    </div>
</section>

<!-- ─── TRUSTED BY ─────────────────────────────────────────── -->
<div class="trusted">
    <p>Recognized & used across SPC departments</p>
    <div class="trusted-logos">
        <span class="trusted-logo">BSIT — Information Technology</span>
        <span class="trusted-logo">CBAE — Business &amp; Accountancy</span>
        <span class="trusted-logo">CRIM — Criminology</span>
        <span class="trusted-logo">CHTM — Hospitality &amp; Tourism</span>
        <span class="trusted-logo">CTE — Teacher Education</span>
        <span class="trusted-logo">SHS — Senior High School</span>
    </div>
</div>

<!-- ─── FEATURES ──────────────────────────────────────────── -->
<section id="features">
    <div class="section-inner">
        <div style="text-align:center; max-width:600px; margin:0 auto;">
            <div class="section-tag fade-up"><i class="fa-solid fa-star"></i> Platform Features</div>
            <h2 class="section-title fade-up" style="transition-delay:.1s;">For a better future  <span>Let's get you there!</span></h2>
            <p class="section-sub fade-up" style="transition-delay:.2s; margin:0 auto;">
                From casting a vote to sharing a campus event — SPC VoteSys has every student covered with one secure platform.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card fade-up">
                <div class="feature-icon"><i class="fa-solid fa-ballot-check"></i></div>
                <h3>Secure Online Voting</h3>
                <p>Cast your vote with confidence. Each student gets exactly one vote per position, verified by your SPC student ID.</p>
            </div>
            <div class="feature-card fade-up" style="transition-delay:.1s;">
                <div class="feature-icon"><i class="fa-solid fa-calendar-days"></i></div>
                <h3>Campus Event Feed</h3>
                <p>Stay updated with all college events. React, comment, and share events with your classmates in seconds.</p>
            </div>
            <div class="feature-card fade-up" style="transition-delay:.2s;">
                <div class="feature-icon"><i class="fa-solid fa-chart-bar"></i></div>
                <h3>Live Election Results</h3>
                <p>Watch real-time vote tallies as they come in — fully transparent and updated instantly after every submission.</p>
            </div>
            <div class="feature-card fade-up" style="transition-delay:.3s;">
                <div class="feature-icon"><i class="fa-solid fa-users"></i></div>
                <h3>Candidate Profiles</h3>
                <p>View full candidate bios, photos, and platforms before making your choice. Know who you're voting for.</p>
            </div>
            <div class="feature-card fade-up" style="transition-delay:.4s;">
                <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Anti-Fraud Protection</h3>
                <p>Duplicate voting prevention, self-vote blocking, and session validation ensure every election is fair.</p>
            </div>
            <div class="feature-card fade-up" style="transition-delay:.5s;">
                <div class="feature-icon"><i class="fa-solid fa-receipt"></i></div>
                <h3>Voting Receipt</h3>
                <p>Get a full receipt of your ballot before submission. Review all your picks in one clean summary screen.</p>
            </div>
        </div>
    </div>
</section>

<!-- ─── HOW IT WORKS ──────────────────────────────────────── -->
<section id="how-it-works" class="how-section">
    <div class="section-inner">
        <div style="text-align:center; max-width:600px; margin:0 auto;">
            <div class="section-tag fade-up"><i class="fa-solid fa-list-ol"></i> Simple Process</div>
            <h2 class="section-title fade-up" style="transition-delay:.1s;">Vote in <span>4 Easy Steps</span></h2>
            <p class="section-sub fade-up" style="transition-delay:.2s; margin: 0 auto;">
                From sign-up to submitted ballot — the whole process takes under two minutes.
            </p>
        </div>

        <div class="steps-grid">
            <div class="step-card fade-up">
                <div class="step-number">1</div>
                <h3>Register & Log In</h3>
                <p>Create your account using your student ID and SPC email, then sign in to the portal.</p>
            </div>
            <div class="step-card fade-up" style="transition-delay:.15s;">
                <div class="step-number">2</div>
                <h3>Choose Election</h3>
                <p>Browse active campus elections from your department and select the one you want to vote in.</p>
            </div>
            <div class="step-card fade-up" style="transition-delay:.3s;">
                <div class="step-number">3</div>
                <h3>Pick Your Candidates</h3>
                <p>Browse candidate cards per position, review their profiles, and select your preferred candidate.</p>
            </div>
            <div class="step-card fade-up" style="transition-delay:.45s;">
                <div class="step-number">4</div>
                <h3>Submit Your Ballot</h3>
                <p>Review your voting receipt, confirm your selections, and submit — your vote is recorded instantly.</p>
            </div>
        </div>
    </div>
</section>

<!-- ─── STATS BANNER ──────────────────────────────────────── -->
<div class="stats-banner" id="about">
    <div class="section-inner">
        <div class="section-tag" style="background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.2);margin:0 auto 1rem;display:inline-flex; fade-up">
            <i class="fa-solid fa-chart-pie"></i> By The Numbers
        </div>
        <h2 class="section-title fade-up" style="max-width:600px;margin:0 auto .9rem;">
            SPC Students Love <span style="color:rgba(255,200,100,1);">Voting Online</span>
        </h2>
        <p class="section-sub fade-up" style="transition-delay:.1s;margin:0 auto;">
            Our system has powered every campus election at SPC since its launch — fast, fair, and fully digital.
        </p>

        <div class="stats-row">
            <div class="stat-block fade-up">
                <span class="big-num" data-count="{{ $voteCount ?? 0 }}">0</span>
                <div class="stat-dsc">Total Votes Cast</div>
            </div>
            <div class="stat-block fade-up" style="transition-delay:.15s;">
                <span class="big-num" data-count="{{ $electionCount ?? 0 }}">0</span>
                <div class="stat-dsc">Elections Held</div>
            </div>
            <div class="stat-block fade-up" style="transition-delay:.3s;">
                <span class="big-num" data-count="{{ $studentCount ?? 0 }}">0</span>
                <div class="stat-dsc">Registered Students</div>
            </div>
            <div class="stat-block fade-up" style="transition-delay:.45s;">
                <span class="big-num" data-count="100">0</span>
                <div class="stat-dsc" style="white-space:nowrap;">% Secure & Verified</div>
            </div>
        </div>
    </div>
</div>

<!-- ─── TESTIMONIALS ──────────────────────────────────────── -->
<section class="testimonial-section">
    <div class="section-inner">
        <div style="text-align:center; max-width:600px; margin:0 auto;">
            <div class="section-tag fade-up"><i class="fa-solid fa-quote-left"></i> Student Voices</div>
            <h2 class="section-title fade-up" style="transition-delay:.1s;">What <span>SPC Students</span> Say</h2>
            @if(isset($reviews) && $reviews->count())
                <p class="section-sub fade-up" style="transition-delay:.2s; margin:0 auto;">
                    Real feedback from students who have used the SPC Online Voting System.
                </p>
            @endif
        </div>

        <div class="testimonial-grid">
        @if(isset($reviews) && $reviews->count())
            @foreach($reviews as $i => $rev)
                @php
                    $u        = $rev->user;
                    $fullName = trim(($u->name ?? '') . ' ' . ($u->last_name ?? ''));
                    $initials = strtoupper(substr($u->name ?? 'S', 0, 1) . substr($u->last_name ?? 'U', 0, 1));
                    $role     = ($u->department ?? '') . ($u->year_level ? ', ' . $u->year_level . ' Year' : '');
                    $stars    = str_repeat('★', $rev->rating) . str_repeat('☆', 5 - $rev->rating);
                    $delay    = $i * 0.15;
                @endphp
                <div class="testimonial-card fade-up" style="transition-delay:{{ $delay }}s;">
                    <div class="testi-stars" style="color:var(--maroon);">{{ $stars }}</div>
                    <p class="testi-text">"{{ $rev->review }}"</p>
                    <div class="testi-author">
                        @if($u && $u->profile_picture)
                            <img src="{{ $u->profile_picture }}" alt="{{ $fullName }}"
                                 style="width:42px;height:42px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                        @else
                            <div class="testi-avatar">{{ $initials }}</div>
                        @endif
                        <div>
                            <div class="testi-name">{{ $fullName ?: 'SPC Student' }}</div>
                            <div class="testi-role">{{ $role ?: 'SPC Student' }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            {{-- Static fallback when no reviews exist yet --}}
            <div class="testimonial-card fade-up">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"Voting was so smooth and fast! I picked my candidates, reviewed the receipt, and submitted in less than a minute."</p>
                <div class="testi-author">
                    <div class="testi-avatar">MR</div>
                    <div>
                        <div class="testi-name">Maria Reyes</div>
                        <div class="testi-role">BS Information Technology, 3rd Year</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card fade-up" style="transition-delay:.15s;">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"The event feed is amazing! I never miss a campus event now. I can react, comment, and even share with friends."</p>
                <div class="testi-author">
                    <div class="testi-avatar">JC</div>
                    <div>
                        <div class="testi-name">Juan Carlos Santos</div>
                        <div class="testi-role">BS Education, 2nd Year</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card fade-up" style="transition-delay:.3s;">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"As SSG President, this system made our elections transparent and efficient. Live results are a game-changer!"</p>
                <div class="testi-author">
                    <div class="testi-avatar">AL</div>
                    <div>
                        <div class="testi-name">Ana Liza dela Cruz</div>
                        <div class="testi-role">SSG President, SPC</div>
                    </div>
                </div>
            </div>
        @endif
        </div>
    </div>
</section>

<!-- ─── CTA BANNER ────────────────────────────────────────── -->
<div class="cta-banner" id="contact">
    <div class="section-inner" style="max-width:800px; margin:0 auto; text-align:center;">
        <div class="section-tag fade-up" style="margin:0 auto 1rem;display:inline-flex;">
            <i class="fa-solid fa-rocket"></i> Ready to Vote?
        </div>
        <h2 class="section-title fade-up" style="transition-delay:.1s;">
            Join the <span>SPC Digital</span> Campus Experience
        </h2>
        <p class="section-sub fade-up" style="transition-delay:.2s; margin: 0 auto 2.5rem;">
            Register with your SPC student account and participate in campus elections and events — all in one place.
        </p>
        <div class="cta-actions fade-up" style="transition-delay:.3s;">
            <a href="{{ route('register') }}" class="btn-hero-primary">
                <i class="fa-solid fa-paper-plane"></i> Submit Request
            </a>
            <a href="{{ route('login') }}" class="btn-hero-outline">
                <i class="fa-solid fa-right-to-bracket"></i> Student Sign In
            </a>
        </div>
    </div>
</div>

<!-- ─── FOOTER ────────────────────────────────────────────── -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <img src="{{ asset('images/spc-logo.png') }}" alt="SPC Logo"
                 style="width:44px;height:44px;object-fit:contain;margin-bottom:.75rem;border-radius:10px;">
            <div class="footer-brand-name">SPC VoteSys</div>
            <p class="footer-brand-sub">
                Online Voting & Event Management System for Southern de Oro Philippines College. For a better tomorrow let's get you there! since 1982.
            </p>
        </div>

        <div class="footer-col">
            <h4>Platform</h4>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="{{ route('login') }}">Sign In</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Elections</h4>
            <ul>
                <li><a href="#">Active Elections</a></li>
                <li><a href="#">Past Results</a></li>
                <li><a href="#">Candidates</a></li>
                <li><a href="#">Voting History</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Use</a></li>
                <li><a href="#">Contact Admin</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <span class="footer-copy">&copy; {{ date('Y') }} Southern de Oro Philippines College. All rights reserved.</span>
        <div class="footer-links">
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Contact</a>
        </div>
    </div>
</footer>

<script>
    // ── Navbar scroll shadow ──────────────────────────────────
    window.addEventListener('scroll', () => {
        document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 10);
    });

    // ── Mobile nav toggle ─────────────────────────────────────
    function toggleMobileNav() {
        document.getElementById('mobileNav').classList.toggle('open');
    }
    document.addEventListener('click', (e) => {
        const nav = document.getElementById('mobileNav');
        const btn = document.getElementById('hamburger');
        if (nav.classList.contains('open') && !nav.contains(e.target) && !btn.contains(e.target)) {
            nav.classList.remove('open');
        }
    });

    // ── Intersection Observer fade-up ─────────────────────────
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // ── Counter animation ─────────────────────────────────────
    function animateCounter(el) {
        const target = parseInt(el.dataset.count, 10);
        const duration = 1800;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
    }

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting && !e.target.dataset.animated) {
                e.target.dataset.animated = '1';
                animateCounter(e.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

    // ── Trigger hero fade-ups immediately ─────────────────────
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.hero .fade-up').forEach((el, i) => {
            setTimeout(() => el.classList.add('visible'), i * 120);
        });
    });
</script>

<!-- ─── THREE.JS: Digital Vote Stream ───────────────────────── -->
<script>
(function () {
    if (typeof THREE === 'undefined') return;

    const canvas = document.getElementById('voteCanvas');
    const hero   = document.querySelector('.hero');

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: false });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0xffffff, 1);
    renderer.setSize(hero.offsetWidth, hero.offsetHeight);

    const scene  = new THREE.Scene();
    scene.fog    = new THREE.FogExp2(0xffffff, 0.055);

    const camera = new THREE.PerspectiveCamera(60, hero.offsetWidth / hero.offsetHeight, 0.1, 100);
    camera.position.set(-1.8, 0, 9);
    camera.lookAt(0, 0, 0);

    // ── LIGHTS ────────────────────────────────────────────────
    scene.add(new THREE.AmbientLight(0xffeef2, 3));
    const coreLight = new THREE.PointLight(0xff0033, 4, 7);
    scene.add(coreLight);

    // ── CENTRAL CORE ──────────────────────────────────────────
    const coreGeo  = new THREE.SphereGeometry(0.32, 32, 32);
    const coreMat  = new THREE.MeshStandardMaterial({
        color: 0x800020, emissive: 0xcc0020,
        emissiveIntensity: 1.8, roughness: 0.15, metalness: 0.9
    });
    const coreMesh = new THREE.Mesh(coreGeo, coreMat);
    scene.add(coreMesh);

    // Outer wire shell
    const shellGeo = new THREE.SphereGeometry(0.62, 12, 8);
    const shellMat = new THREE.MeshBasicMaterial({ color: 0xcc0033, wireframe: true, transparent: true, opacity: 0.22 });
    scene.add(new THREE.Mesh(shellGeo, shellMat));

    // Equatorial rings
    const ringGeo  = new THREE.TorusGeometry(0.58, 0.018, 6, 80);
    const ringMat  = new THREE.MeshBasicMaterial({ color: 0xff2255, transparent: true, opacity: 0.55 });
    const ring1    = new THREE.Mesh(ringGeo, ringMat);
    const ring2    = new THREE.Mesh(ringGeo, ringMat.clone());
    ring2.rotation.x = Math.PI / 3;
    const ring3    = new THREE.Mesh(ringGeo, ringMat.clone());
    ring3.rotation.x = -Math.PI / 3;
    scene.add(ring1, ring2, ring3);

    // ── NETWORK NODES ─────────────────────────────────────────
    const NODE_COUNT   = 90;
    const nodePositions = [];
    const nodeGeo      = new THREE.SphereGeometry(0.038, 8, 8);
    const nodeMat      = new THREE.MeshBasicMaterial({ color: 0x800020, transparent: true, opacity: 0.7 });
    const nodeGroup    = new THREE.Group();
    scene.add(nodeGroup);

    for (let i = 0; i < NODE_COUNT; i++) {
        // Fibonacci sphere
        const phi   = Math.acos(1 - 2 * (i + 0.5) / NODE_COUNT);
        const theta = Math.PI * (1 + Math.sqrt(5)) * i;
        const r     = 3.2 + Math.random() * 1.6;
        const pos   = new THREE.Vector3(
            r * Math.sin(phi) * Math.cos(theta),
            r * Math.sin(phi) * Math.sin(theta),
            r * Math.cos(phi)
        );
        nodePositions.push(pos);
        const m = new THREE.Mesh(nodeGeo, nodeMat);
        m.position.copy(pos);
        nodeGroup.add(m);
    }

    // ── NETWORK LINES ─────────────────────────────────────────
    const lineMat = new THREE.LineBasicMaterial({ color: 0x800020, transparent: true, opacity: 0.22 });
    for (let i = 0; i < NODE_COUNT; i++) {
        for (let j = i + 1; j < NODE_COUNT; j++) {
            if (nodePositions[i].distanceTo(nodePositions[j]) < 1.35) {
                const lg = new THREE.BufferGeometry().setFromPoints([nodePositions[i], nodePositions[j]]);
                nodeGroup.add(new THREE.Line(lg, lineMat));
            }
        }
    }

    // ── DATA PACKETS (golden spheres) ─────────────────────────
    const PACKET_COUNT = 28;
    const packetGeo    = new THREE.SphereGeometry(0.042, 8, 8);
    const packetMat    = new THREE.MeshStandardMaterial({
        color: 0xf59e0b, emissive: 0xf59e0b,
        emissiveIntensity: 1.2, roughness: 0.2, metalness: 0.5
    });
    const packets      = [];
    const packetGroup  = new THREE.Group();
    scene.add(packetGroup);
    const TARGET       = new THREE.Vector3(0, 0, 0);

    function spawnPacket(delay) {
        setTimeout(function () {
            const src  = nodePositions[Math.floor(Math.random() * NODE_COUNT)];
            const mesh = new THREE.Mesh(packetGeo, packetMat);
            mesh.position.copy(src);
            packetGroup.add(mesh);
            packets.push({ mesh, origin: src.clone(), t: 0, speed: 0.004 + Math.random() * 0.005 });
        }, delay);
    }
    for (let i = 0; i < PACKET_COUNT; i++) spawnPacket(i * 180);

    // ── STAR FIELD ────────────────────────────────────────────
    const starVerts = [];
    for (let i = 0; i < 600; i++) {
        starVerts.push(
            (Math.random() - 0.5) * 40,
            (Math.random() - 0.5) * 40,
            (Math.random() - 0.5) * 40
        );
    }
    const starGeo = new THREE.BufferGeometry();
    starGeo.setAttribute('position', new THREE.Float32BufferAttribute(starVerts, 3));
    const starMat = new THREE.PointsMaterial({ color: 0xc0b0b4, size: 0.045, transparent: true, opacity: 0.4 });
    scene.add(new THREE.Points(starGeo, starMat));

    // ── SCROLL → camera depth ─────────────────────────────────
    let scrollPct = 0;
    window.addEventListener('scroll', function () {
        scrollPct = Math.min(window.scrollY / (hero.offsetHeight || window.innerHeight), 1);
    });

    // ── ANIMATE ───────────────────────────────────────────────
    let t = 0;
    (function animate() {
        requestAnimationFrame(animate);
        t += 0.012;

        // Pulse core
        const pulse = 1 + 0.14 * Math.sin(t * 2.4);
        coreMesh.scale.setScalar(pulse);
        coreMat.emissiveIntensity = 1.5 + 0.7 * Math.sin(t * 2.4);
        coreLight.intensity       = 3 + 1.5 * Math.sin(t * 2.4);

        // Rotate rings
        ring1.rotation.z += 0.009;
        ring2.rotation.z -= 0.007;
        ring3.rotation.y += 0.006;

        // Slowly rotate the entire node network
        nodeGroup.rotation.y = t * 0.04;
        nodeGroup.rotation.x = Math.sin(t * 0.015) * 0.18;

        // Camera flies in on scroll
        const targetZ = THREE.MathUtils.lerp(9, 2.2, scrollPct);
        const targetY = THREE.MathUtils.lerp(0, 0.6, scrollPct);
        camera.position.z += (targetZ - camera.position.z) * 0.06;
        camera.position.y += (targetY - camera.position.y) * 0.06;
        camera.position.x += (-1.8 - camera.position.x) * 0.06;
        camera.lookAt(0, 0, 0);

        // Data packets
        for (let i = packets.length - 1; i >= 0; i--) {
            const p = packets[i];
            p.t += p.speed;

            // Apply same rotation as nodeGroup to origin so packets track node positions
            const rotOrigin = p.origin.clone().applyEuler(nodeGroup.rotation);
            p.mesh.position.lerpVectors(rotOrigin, TARGET, p.t);

            // Scale in at start, stay full size
            p.mesh.scale.setScalar(Math.min(p.t / 0.12, 1));

            if (p.t >= 1) {
                packetGroup.remove(p.mesh);
                packets.splice(i, 1);
                coreMat.emissiveIntensity = 3.5; // flash
                spawnPacket(0);
            }
        }

        renderer.render(scene, camera);
    })();

    // ── RESIZE ────────────────────────────────────────────────
    window.addEventListener('resize', function () {
        const w = hero.offsetWidth, h = hero.offsetHeight;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    });

})();
</script>
</body>
</html>
