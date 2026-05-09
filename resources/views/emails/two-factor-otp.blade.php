<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Code</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Arial, sans-serif; background: #f4f6f9; color: #2d3436; }
        .wrapper { max-width: 520px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #c0392b, #8e1a11); padding: 32px 40px; text-align: center; }
        .header img { height: 48px; margin-bottom: 10px; }
        .header h1 { margin: 0; color: white; font-size: 1.2rem; font-weight: 700; letter-spacing: 0.5px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 1rem; color: #636e72; margin-bottom: 1.2rem; }
        .otp-label { font-size: 0.8rem; font-weight: 700; color: #b2bec3; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; }
        .otp-box { display: block; text-align: center; font-size: 2.8rem; font-weight: 900; letter-spacing: 12px; color: #c0392b; background: #fff5f5; border: 2px solid #fce4e4; border-radius: 12px; padding: 18px 0; margin: 12px 0 20px; }
        .note { font-size: 0.85rem; color: #636e72; background: #f8f9fa; border-left: 4px solid #c0392b; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-bottom: 1.5rem; }
        .note strong { color: #2d3436; }
        .footer { background: #f8f9fa; padding: 20px 40px; text-align: center; font-size: 0.75rem; color: #b2bec3; border-top: 1px solid #f0f0f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>UM Dining Center</h1>
        </div>
        <div class="body">
            <p class="greeting">Hello, <strong>{{ $firstName }}</strong>! Someone is attempting to log in to your account.</p>
            <div class="otp-label">Your One-Time Login Code</div>
            <span class="otp-box">{{ $otp }}</span>
            <div class="note">
                ⏱ This code is valid for <strong>5 minutes</strong>. If you did not request this, please contact your administrator immediately.
            </div>
            <p style="font-size:0.85rem; color:#636e72;">Do not share this code with anyone.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} UM Dining Center Management System. All rights reserved.
        </div>
    </div>
</body>
</html>
