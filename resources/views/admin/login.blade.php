<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin — SPP Universal School</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
  <style>
    body, html {
        margin: 0;
        padding: 0;
        font-family: 'Outfit', sans-serif;
        background-color: #0b0f19;
        color: #f1f5f9;
        height: 100%;
        overflow: hidden;
    }
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .background-mesh {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: 
            radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.15) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.12) 0px, transparent 50%),
            radial-gradient(at 0% 100%, rgba(236, 72, 153, 0.12) 0px, transparent 50%);
        z-index: 1;
    }
    .blob-1 {
        position: absolute; top: -10%; left: -5%; width: 50vw; height: 50vw;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 12s infinite alternate ease-in-out;
        z-index: 2;
    }
    .blob-2 {
        position: absolute; bottom: -10%; right: -5%; width: 45vw; height: 45vw;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 10s infinite alternate-reverse ease-in-out;
        z-index: 2;
    }
    @keyframes float {
        0% { transform: translateY(0) scale(1); }
        100% { transform: translateY(50px) scale(1.05); }
    }
    .glass-card {
        background: rgba(17, 24, 39, 0.6);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        border-left: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        padding: 3rem 2.5rem;
        border-radius: 28px;
        width: 100%;
        max-width: 420px;
        z-index: 10;
        position: relative;
        transform: translateY(0);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.7);
        border-color: rgba(255, 255, 255, 0.15);
    }
    .brand-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .brand-logo-img {
        width: 85px;
        height: 85px;
        object-fit: contain;
        margin-bottom: 12px;
        filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.3));
        transition: transform 0.3s ease;
    }
    .brand-logo-img:hover {
        transform: scale(1.05) rotate(-2deg);
    }
    .brand-title {
        font-size: 1.875rem;
        font-weight: 700;
        margin: 0 0 0.25rem 0;
        background: linear-gradient(135deg, #f8fafc 0%, #cbd5e1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.02em;
    }
    .brand-subtitle {
        color: #94a3b8;
        font-size: 0.9rem;
        margin: 0;
        font-weight: 300;
    }
    .input-group {
        margin-bottom: 1.25rem;
    }
    .input-group label {
        display: block;
        color: #cbd5e1;
        font-size: 0.8rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .input-field {
        width: 100%;
        box-sizing: border-box;
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #f8fafc;
        border-radius: 14px;
        padding: 0.875rem 1rem;
        font-family: inherit;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    .input-field::placeholder {
        color: #475569;
    }
    .input-field:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        background: rgba(15, 23, 42, 0.8);
        outline: none;
    }
    .pw-wrapper {
        position: relative;
    }
    .pw-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        transition: color 0.2s ease;
        padding: 4px;
    }
    .pw-toggle:hover {
        color: #cbd5e1;
    }
    .remember-row {
        display: flex;
        align-items: center;
        margin-bottom: 1.75rem;
        gap: 8px;
    }
    .custom-checkbox {
        appearance: none;
        width: 18px;
        height: 18px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 5px;
        background: rgba(15, 23, 42, 0.5);
        cursor: pointer;
        position: relative;
        transition: all 0.2s;
    }
    .custom-checkbox:checked {
        background: #6366f1;
        border-color: #6366f1;
    }
    .custom-checkbox:checked::after {
        content: '✓';
        position: absolute;
        color: white;
        font-size: 14px;
        left: 3px;
        top: 0px;
        font-weight: bold;
    }
    .remember-label {
        font-size: 0.875rem;
        color: #94a3b8;
        cursor: pointer;
        user-select: none;
    }
    .btn-submit {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        border: none;
        border-radius: 14px;
        color: white;
        font-weight: 600;
        font-family: inherit;
        font-size: 1rem;
        padding: 1rem 1.5rem;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(168, 85, 247, 0.4);
        filter: brightness(1.05);
    }
    .btn-submit:active {
        transform: translateY(1px);
    }
    .btn-submit svg {
        transition: transform 0.3s ease;
    }
    .btn-submit:hover svg {
        transform: translateX(3px);
    }
    .alert-box {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-left: 3px solid #ef4444;
        color: #fca5a5;
        padding: 0.875rem 1rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    @media (max-width: 480px) {
        .glass-card {
            padding: 2.5rem 1.75rem;
            max-width: 90%;
        }
        .brand-title {
            font-size: 1.5rem;
        }
    }
  </style>
</head>
<body>

<div class="login-container">
    <div class="background-mesh"></div>
    <div class="blob-1"></div>
    <div class="blob-2"></div>

    <div class="glass-card">
        <div class="brand-header">
            <img src="{{ asset('images/logo.png') }}" alt="Universal School Logo" class="brand-logo-img">
            <h1 class="brand-title">Panel Admin</h1>
            <p class="brand-subtitle">SPP Universal School — Sistem Keuangan</p>
        </div>

        @if($errors->any())
            <div class="alert-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $errors->first() }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="input-group">
                <label for="email">Email Administrator</label>
                <input type="email" id="email" name="email" class="input-field" placeholder="Masukan email..." value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="input-group">
                <label for="password">Kata Sandi</label>
                <div class="pw-wrapper">
                    <input type="password" id="pw" name="password" class="input-field" placeholder="••••••••" required>
                    <button type="button" class="pw-toggle" onclick="togglePw()" aria-label="Tampilkan kata sandi">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" class="custom-checkbox">
                <label for="remember" class="remember-label">Ingat sesi saya</label>
            </div>

            <button type="submit" class="btn-submit">
                Masuk Sistem
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>
    </div>
</div>

<script>
function togglePw() {
  const pw = document.getElementById('pw');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
