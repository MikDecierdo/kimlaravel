<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPC Voting System</title>
    <link rel="icon" href="{{ asset('images/spc-logo.png') }}" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .login-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2b2d42;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #800020;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #8d99ae;
        }

        .login-footer a {
            color: #800020;
            text-decoration: none;
            font-weight: 500;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fa-solid fa-graduation-cap" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h1>SPC Voting System</h1>
            <p>Sign in to your account</p>
        </div>
        
        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('registration_pending'))
                <div class="alert" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">
                    {{ session('registration_pending') }}
                </div>
            @endif

            @if(session('account_denied'))
                <div class="alert" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">
                    {{ session('account_denied') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required autofocus value="{{ old('email') }}">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center;">
                    <input type="checkbox" id="remember" name="remember" style="margin-right: 8px;">
                    <label for="remember" style="margin: 0; font-weight: 400;">Remember Me</label>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>
            </form>
            
            <div class="login-footer">
                Don't have an account? <a href="{{ route('register') }}">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>
