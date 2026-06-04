@extends('layouts.guest')

@section('title', 'Login — InvestaPremier')

@section('body')
<style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:'Poppins',sans-serif}
.auth-wrap{min-height:100vh;display:flex}

/* ── Left panel ── */
.auth-left{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:48px 40px;background:#fff;
}
.auth-form-box{width:100%;max-width:400px}

.auth-logo{display:flex;align-items:center;gap:10px;margin-bottom:36px}
.auth-logo-mark{
  width:40px;height:40px;border-radius:10px;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  display:grid;place-items:center;color:#fff;
  box-shadow:0 2px 12px rgba(22,163,74,.3);
  flex-shrink:0;
}
.auth-logo-mark svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.auth-logo-name{font-weight:700;font-size:15px;color:#0f172a;line-height:1.2}
.auth-logo-name small{display:block;font-size:10px;font-weight:500;color:#64748b;text-transform:uppercase;letter-spacing:.06em}

.auth-title{font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin-bottom:4px}
.auth-sub{font-size:14px;color:#64748b;margin-bottom:28px}

/* ── Form fields ── */
.field{margin-bottom:18px}
.field label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px}
.field input{
  width:100%;padding:11px 14px;border-radius:8px;
  border:1.5px solid #e2e8f0;background:#fff;
  font-family:'Poppins',sans-serif;font-size:14px;color:#0f172a;
  outline:none;transition:border-color .2s,box-shadow .2s;
}
.field input:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
.field input::placeholder{color:#94a3b8}
.field-pw{position:relative}
.field-pw input{padding-right:44px}
.pw-toggle{
  position:absolute;top:50%;right:12px;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;
  display:flex;align-items:center;
}
.pw-toggle:hover{color:#16a34a}
.pw-toggle svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

.field-error{font-size:12px;color:#ef4444;margin-top:4px}

.row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.remember{display:flex;align-items:center;gap:7px;font-size:13px;color:#475569;cursor:pointer}
.remember input[type=checkbox]{
  width:15px;height:15px;border-radius:4px;
  accent-color:#16a34a;cursor:pointer;
}
.forgot{font-size:13px;color:#16a34a;font-weight:600;text-decoration:none}
.forgot:hover{color:#15803d;text-decoration:underline}

/* ── Submit ── */
.btn-submit{
  width:100%;padding:12px;border-radius:8px;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:14px;
  border:none;cursor:pointer;transition:all .2s;
  box-shadow:0 4px 14px rgba(22,163,74,.3);
}
.btn-submit:hover{background:linear-gradient(135deg,#15803d,#16a34a);box-shadow:0 6px 20px rgba(22,163,74,.4);transform:translateY(-1px)}

/* ── Divider ── */
.divider{display:flex;align-items:center;gap:12px;margin:20px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e2e8f0}
.divider span{font-size:12px;color:#94a3b8;font-weight:500}

/* ── Google ── */
.btn-google{
  width:100%;display:flex;align-items:center;justify-content:center;gap:10px;
  padding:11px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;
  font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;color:#0f172a;
  cursor:pointer;text-decoration:none;transition:all .2s;
}
.btn-google:hover{border-color:#d1d5db;background:#f9fafb;transform:translateY(-1px)}
.btn-google svg{width:18px;height:18px;flex-shrink:0}

.auth-footer{text-align:center;margin-top:24px;font-size:13px;color:#64748b}
.auth-footer a{color:#16a34a;font-weight:600;text-decoration:none}
.auth-footer a:hover{text-decoration:underline}

/* ── Alert ── */
.alert-success{
  padding:10px 14px;border-radius:8px;background:#f0fdf4;
  border:1px solid #bbf7d0;color:#15803d;font-size:13px;font-weight:500;
  margin-bottom:16px;
}

/* ── Right panel ── */
.auth-right{
  display:none;flex:1;
  background:linear-gradient(145deg,#0f172a 0%,#1a2744 100%);
  padding:60px 56px;align-items:center;justify-content:center;
  position:relative;overflow:hidden;
}
@media(min-width:1024px){.auth-right{display:flex}}
.auth-right::before{
  content:'';position:absolute;
  width:500px;height:500px;border-radius:50%;
  background:radial-gradient(circle,rgba(22,163,74,.18) 0%,transparent 65%);
  right:-100px;top:-120px;pointer-events:none;
}
.auth-right::after{
  content:'';position:absolute;
  width:300px;height:300px;border-radius:50%;
  background:radial-gradient(circle,rgba(22,163,74,.1) 0%,transparent 70%);
  left:-60px;bottom:-60px;pointer-events:none;
}
.auth-right-inner{max-width:400px;color:#fff;position:relative;z-index:1}
.auth-right-icon{
  width:56px;height:56px;border-radius:14px;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  display:grid;place-items:center;margin-bottom:24px;
  box-shadow:0 4px 20px rgba(22,163,74,.4);
}
.auth-right-icon svg{width:26px;height:26px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.auth-right h2{font-size:clamp(24px,3vw,34px);font-weight:800;line-height:1.14;letter-spacing:-.02em}
.auth-right h2 em{font-style:normal;color:#4ade80}
.auth-right p{font-size:15px;line-height:1.8;color:#94a3b8;margin-top:14px}
.auth-right-features{margin-top:28px;display:flex;flex-direction:column;gap:12px}
.arf{display:flex;align-items:center;gap:10px;font-size:14px;color:#cbd5e1}
.arf-dot{width:20px;height:20px;border-radius:6px;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.25);display:grid;place-items:center;flex-shrink:0}
.arf-dot svg{width:11px;height:11px;stroke:#4ade80;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
.auth-right-badge{
  margin-top:32px;display:inline-flex;align-items:center;gap:8px;
  padding:8px 16px;border-radius:999px;
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
  font-size:12px;color:#94a3b8;
}
.badge-dot{width:7px;height:7px;border-radius:50%;background:#4ade80;animation:blink 2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}

/* recaptcha */
.g-recaptcha{margin-top:16px}

@media(max-width:640px){
  .auth-left{padding:32px 24px}
}
</style>

<div class="auth-wrap">
  {{-- Form side --}}
  <div class="auth-left">
    <div class="auth-form-box">

      <a href="{{ route('index') }}" class="auth-logo">
        <div class="auth-logo-mark">
          <svg viewBox="0 0 24 24"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
        <div class="auth-logo-name">InvestaPremier <small>WealthOS</small></div>
      </a>

      <h1 class="auth-title">Selamat Datang</h1>
      <p class="auth-sub">Masuk ke dashboard InvestaPremier Anda</p>

      @if(session('status'))
        <div class="alert-success">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com"/>
          @error('email')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="field-pw">
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"/>
            <button type="button" class="pw-toggle" onclick="togglePw('password','eye1','eyeoff1')">
              <svg id="eye1" viewBox="0 0 24 24"><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="eyeoff1" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
          @error('password')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" name="remember"/>
            Ingat saya
          </label>
          @if(Route::has('password.request'))
            <a class="forgot" href="{{ route('password.request') }}">Lupa password?</a>
          @endif
        </div>

        @if(config('captcha.sitekey'))
          <div class="g-recaptcha">
            {!! NoCaptcha::display() !!}
            @error('g-recaptcha-response')<div class="field-error">{{ $message }}</div>@enderror
          </div>
        @endif

        <button type="submit" class="btn-submit">Masuk</button>

        <div class="divider"><span>atau lanjutkan dengan</span></div>

        <a href="{{ route('auth.google') }}" class="btn-google">
          <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
          Masuk dengan Google
        </a>

        <p class="auth-footer">Belum punya akun? <a href="{{ route('register') }}">Daftar Gratis</a></p>
      </form>
    </div>
  </div>

  {{-- Visual side --}}
  <div class="auth-right">
    <div class="auth-right-inner">
      <div class="auth-right-icon">
        <svg viewBox="0 0 24 24"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
      </div>
      <h2>One Family,<br><em>One Financial</em><br>Cockpit.</h2>
      <p>Kelola portofolio, proteksi, pendidikan, legacy, dan review advisor dalam satu platform yang elegan dan terstruktur.</p>
      <div class="auth-right-features">
        @foreach(['Portfolio 360° & Asset Allocation', 'AI-Powered Analysis', 'Goal-Based Planning', 'Secure Document Vault'] as $f)
        <div class="arf">
          <div class="arf-dot"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
          {{ $f }}
        </div>
        @endforeach
      </div>
      <div class="auth-right-badge">
        <span class="badge-dot"></span>
        Dipercaya 100+ nasabah prioritas
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function togglePw(id, eyeId, eyeOffId) {
  const input = document.getElementById(id);
  const show  = input.type === 'password';
  input.type  = show ? 'text' : 'password';
  document.getElementById(eyeId).style.display    = show ? 'none'  : '';
  document.getElementById(eyeOffId).style.display = show ? ''      : 'none';
}
</script>
@if(config('captcha.sitekey'))
{!! NoCaptcha::renderJs() !!}
@endif
@endsection
