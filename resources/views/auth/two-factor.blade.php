<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login — UM Dining Center</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/login.css'])
    <style>
        .twofa-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2.8rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .twofa-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #c0392b, #8e1a11);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem;
        }
        .twofa-icon i { color: white; font-size: 1.6rem; }
        .twofa-title { font-size: 1.4rem; font-weight: 800; color: #2d3436; margin-bottom: 0.4rem; }
        .twofa-subtitle { font-size: 0.88rem; color: #636e72; margin-bottom: 2rem; line-height: 1.5; }
        .otp-inputs { display: flex; gap: 0.6rem; justify-content: center; margin-bottom: 1.5rem; }
        .otp-inputs input {
            width: 50px; height: 58px;
            text-align: center; font-size: 1.5rem; font-weight: 800;
            border: 2px solid #e0e0e0; border-radius: 12px;
            outline: none; transition: border 0.2s;
            font-family: 'Segoe UI', sans-serif;
            color: #2d3436 !important;
            background: #ffffff !important;
        }
        .otp-inputs input:focus { border-color: #c0392b; box-shadow: 0 0 0 3px rgba(192,57,43,0.12); }
        .otp-hidden { display: none; }
        .verify-btn {
            width: 100%; padding: 0.85rem;
            background: linear-gradient(135deg, #c0392b, #8e1a11);
            color: white; border: none; border-radius: 50px;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
        }
        .verify-btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .resend-link { margin-top: 1.2rem; font-size: 0.85rem; color: #636e72; }
        .resend-link a { color: #c0392b; font-weight: 600; text-decoration: none; }
        .resend-link a:hover { text-decoration: underline; }
        .alert-otp { margin-bottom: 1rem; font-size: 0.85rem; border-radius: 10px; }
        .timer-text { font-size: 0.8rem; color: #b2bec3; margin-top: 0.5rem; }
        .timer-text span { font-weight: 700; color: #c0392b; }
    </style>
</head>
<body>
    <div class="login-container" style="justify-content: center; align-items: center; display: flex; min-height: 100vh;">
        <div class="twofa-card">
            <div class="twofa-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="twofa-title">Two-Factor Verification</div>
            <p class="twofa-subtitle">
                We sent a 6-digit code to your registered email address.<br>
                Enter it below to complete your login.
            </p>

            {{-- Alerts --}}
            @if($errors->any())
                <div class="alert alert-danger alert-otp">{{ $errors->first() }}</div>
            @endif
            @if(session('resent'))
                <div class="alert alert-success alert-otp">{{ session('resent') }}</div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}" id="otpForm">
                @csrf
                {{-- Hidden input that holds the combined OTP --}}
                <input type="hidden" name="otp" id="otpHidden">

                {{-- 6 individual digit boxes --}}
                <div class="otp-inputs">
                    @for($i = 1; $i <= 6; $i++)
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                            class="otp-digit" id="otp-{{ $i }}" autocomplete="off">
                    @endfor
                </div>

                <div class="timer-text">Code expires in <span id="countdown">05:00</span></div>

                <button type="submit" class="verify-btn mt-3">
                    <i class="fa-solid fa-check me-2"></i>Verify Code
                </button>
            </form>

            <div class="resend-link">
                Didn't receive it?
                <form method="POST" action="{{ route('2fa.resend') }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none;border:none;padding:0;cursor:pointer;color:#c0392b;font-weight:600;font-size:0.85rem;">Resend Code</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ===== OTP digit box auto-advance =====
        const digits = document.querySelectorAll('.otp-digit');
        digits.forEach((input, idx) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 1);
                if (input.value && idx < digits.length - 1) {
                    digits[idx + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    digits[idx - 1].focus();
                }
            });
        });

        // ===== Combine digits into hidden input on submit =====
        document.getElementById('otpForm').addEventListener('submit', (e) => {
            const combined = Array.from(digits).map(d => d.value).join('');
            document.getElementById('otpHidden').value = combined;
        });

        // ===== Countdown Timer (5 min) =====
        let seconds = 300;
        const countdownEl = document.getElementById('countdown');
        const timer = setInterval(() => {
            seconds--;
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            countdownEl.textContent = `${m}:${s}`;
            if (seconds <= 0) {
                clearInterval(timer);
                countdownEl.textContent = 'Expired';
                countdownEl.style.color = '#b2bec3';
            }
        }, 1000);
    </script>
</body>
</html>
