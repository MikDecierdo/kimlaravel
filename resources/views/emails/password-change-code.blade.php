<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Change Code</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 8px; padding: 32px; }
        h2 { color: #1a202c; margin-top: 0; }
        .code { font-size: 32px; letter-spacing: 8px; text-align: center; color: #2b6cb0; font-weight: bold; margin: 24px 0; }
        .note { color: #718096; font-size: 14px; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Change Request</h2>
        <p>Hi {{ $name }},</p>
        <p>You requested to change your password. Use the verification code below to proceed:</p>
        <div class="code">{{ $code }}</div>
        <p>This code will expire in <strong>10 minutes</strong>.</p>
        <p class="note">If you did not request this, please ignore this email. No changes have been made to your account.</p>
    </div>
</body>
</html>
