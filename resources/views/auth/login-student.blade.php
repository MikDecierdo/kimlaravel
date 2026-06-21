<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPC Voting System - Student & Faculty Login</title>
    <link rel="icon" href="{{ asset('images/spc-logo.png') }}" type="image/png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Maroon & Yellow Palette */
            --primary-maroon: #800000;       /* Deep Maroon */
            --hover-maroon: #5c0000;         
            --accent-yellow: #EAB308;        /* Gold/Yellow */
            --accent-yellow-light: #fef3c7;  
            
            --bg-color: #ffffff;
            --text-main: #2d3748;
            --text-light: #718096;
            --input-border: #e2e8f0;
            --focus-ring: rgba(234, 179, 8, 0.3); 
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            width: 100%;
            overflow: hidden;
            background-color: var(--bg-color);
        }

        /* --- Split Layout Container --- */
        .split-screen {
            display: flex;
            height: 100%;
            width: 100%;
        }

        /* --- Left Side: Image --- */
        .left-pane {
            flex: 1.2;
            background-image: url('{{ asset('images/campus-courtyard.jpg') }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Maroon Tint Overlay */
        .left-pane::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(128, 0, 0, 0.2), rgba(128, 0, 0, 0.8));
        }

        .image-overlay-text {
            position: absolute;
            bottom: 4rem;
            left: 4rem;
            color: white;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .image-overlay-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .image-overlay-text p {
            font-size: 1.1rem;
            opacity: 0.9;
            color: var(--accent-yellow);
        }

        /* --- Right Side: Login Form --- */
        .right-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-color: white;
            position: relative;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        /* --- Brand Logo Section --- */
        .brand-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            text-align: center;
        }

        .brand-logo img {
            /* The User's Logo */
            height: 80px; 
            width: auto;
            margin-bottom: 1rem;
            object-fit: contain;
        }

        .brand-logo h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-maroon);
            line-height: 1.4;
        }

        .brand-logo span {
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 500;
        }

        .welcome-text {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .welcome-text h2 {
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* --- Form Elements --- */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        .form-input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.8rem;
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            color: var(--text-main);
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--accent-yellow);
            box-shadow: 0 0 0 4px var(--focus-ring);
        }

        .form-input:focus ~ i {
            color: var(--accent-yellow);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--text-light);
        }

        .checkbox-group input {
            accent-color: var(--primary-maroon);
            width: 16px;
            height: 16px;
        }

        .forgot-link {
            color: var(--primary-maroon);
            text-decoration: none;
            font-weight: 600;
            transition: text-decoration 0.2s;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        /* --- Submit Button --- */
        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background-color: var(--primary-maroon);
            color: white;
            border: 2px solid var(--primary-maroon);
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background-color: var(--hover-maroon);
            border-color: var(--hover-maroon);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .login-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .login-footer a {
            color: var(--primary-maroon);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .btn-register {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 1rem;
            width: 100%;
            padding: 0.75rem;
            background-color: transparent;
            color: var(--primary-maroon);
            border: 2px solid var(--primary-maroon);
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background-color: var(--primary-maroon);
            color: white;
            text-decoration: none;
        }

        .or-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0 0.25rem;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--input-border);
        }

        .other-logins {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--input-border);
        }

        .other-logins p {
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            color: var(--text-light);
            text-align: center;
        }

        .other-logins-links {
            display: flex;
            gap: 0.5rem;
        }

        .other-logins-links a {
            flex: 1;
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid var(--input-border);
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
            text-align: center;
        }

        .other-logins-links a:hover {
            background: var(--accent-yellow-light);
            border-color: var(--accent-yellow);
        }

        /* --- Error Message --- */
        .error-msg {
            background-color: #fff5f5;
            color: #c53030;
            padding: 0.75rem;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #c53030;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- Responsive Design --- */
        @media (max-width: 900px) {
            .left-pane {
                display: none;
            }
            .split-screen {
                justify-content: center;
            }
            .right-pane {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="split-screen">
        
        <!-- Left Side: University Image -->
        <div class="left-pane">
            <div class="image-overlay-text">
                <h2>Student and Faculty Elections</h2>
                <p>Excellence in Leadership & Service</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="right-pane">
            <div class="login-container">
                
                <!-- User's Logo and School Name -->
                <div class="brand-logo">
                    <!-- SOPC Logo -->
                    <img src="{{ asset('images/spc-logo.png') }}" alt="Southern de Oro Philippines College Logo">
                    
                    <h1>Southern de Oro Philippines College</h1>
                    <span>Voting Portal</span>
                </div>

                <div class="welcome-text">
                    <h2>Sign In</h2>
                    <p>Please enter your student or faculty credentials.</p>
                </div>

                <!-- Error Message -->
                @if ($errors->any())
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('registration_pending'))
                    <div style="background:#f0fdf4;color:#166534;padding:.85rem 1rem;border-radius:8px;font-size:.88rem;margin-bottom:1.5rem;border-left:4px solid #22c55e;display:flex;align-items:flex-start;gap:.6rem;">
                        <i class="fas fa-clock" style="margin-top:2px;flex-shrink:0;"></i>
                        <span>{{ session('registration_pending') }}</span>
                    </div>
                @endif

                @if(session('account_denied'))
                    <div style="background:#fef2f2;color:#991b1b;padding:.85rem 1rem;border-radius:8px;font-size:.88rem;margin-bottom:1.5rem;border-left:4px solid #ef4444;display:flex;align-items:flex-start;gap:.6rem;">
                        <i class="fas fa-ban" style="margin-top:2px;flex-shrink:0;"></i>
                        <span>{{ session('account_denied') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.student') }}">
                    @csrf

                    <!-- Email Input -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" class="form-input" 
                                placeholder="student.or.faculty@spc.edu.ph" 
                                   value="{{ old('email') }}" 
                                   required autofocus>
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-input" 
                                   placeholder="Enter your password" required>
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="form-actions">
                        <label class="checkbox-group">
                            <input type="checkbox" id="remember" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <span>Login</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                </form>

                <div class="or-divider">or</div>

                <a href="{{ route('register') }}" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    Create an Account
                </a>

                <div class="login-footer">
                    No account yet? <a href="{{ route('register') }}">Register here</a> or contact your Department Head.
                    <br><br>
                    &copy; 2026 SPC Voting System. All rights reserved.
                </div>

            </div>
        </div>
    </div>

</body>
</html>
