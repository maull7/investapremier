@extends('layouts.guest')

@section('title', 'Daftar Advisor — InvestaPremier')

@section('body')
<style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:'Poppins',sans-serif}
.auth-wrap{min-height:100vh;display:flex}

.auth-left{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:48px 40px;background:#fff;
}
.auth-form-box{width:100%;max-width:400px}

.auth-logo{display:flex;align-items:center;gap:10px;margin-bottom:36px}
.auth-logo-mark {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #27B556, #32CD5F);
            display: grid;
            place-items: center;
            color: #fff;
            box-shadow: 0 4px 16px rgba(39, 181, 86, .3);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .auth-logo-mark::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,.3), transparent);
            transform: translateX(-100%);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) }
            50%, 100% { transform: translateX(100%) }
        }

        .auth-logo-mark svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            position: relative;
            z-index: 1;
        }

        .auth-logo-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            position: relative;
            z-index: 1;
        }
.auth-logo-name{font-weight:700;font-size:15px;color:#0f172a;line-height:1.2}
.auth-logo-name small{display:block;font-size:10px;font-weight:500;color:#64748b;text-transform:uppercase;letter-spacing:.06em}

.auth-title{font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin-bottom:4px}
.auth-sub{font-size:14px;color:#64748b;margin-bottom:28px}

.field{margin-bottom:16px}
.field label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px}
.field input{
  width:100%;padding:11px 14px;border-radius:8px;
  border:1.5px solid #e2e8f0;background:#fff;
  font-family:'Poppins',sans-serif;font-size:14px;color:#0f172a;
  outline:none;transition:border-color .2s,box-shadow .2s;
}
.field input:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
.field input::placeholder{color:#94a3b8}
.field-error{font-size:12px;color:#ef4444;margin-top:4px}

.btn-submit{
  width:100%;padding:12px;border-radius:8px;margin-top:22px;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:14px;
  border:none;cursor:pointer;transition:all .2s;
  box-shadow:0 4px 14px rgba(22,163,74,.3);
}
.btn-submit:hover{background:linear-gradient(135deg,#15803d,#16a34a);box-shadow:0 6px 20px rgba(22,163,74,.4);transform:translateY(-1px)}

.divider{display:flex;align-items:center;gap:12px;margin:20px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e2e8f0}
.divider span{font-size:12px;color:#94a3b8;font-weight:500}

.btn-google{
  width:100%;display:flex;align-items:center;justify-content:center;gap:10px;
  padding:11px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;
  font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;color:#0f172a;
  cursor:pointer;text-decoration:none;transition:all .2s;
}
.btn-google:hover{border-color:#d1d5db;background:#f9fafb;transform:translateY(-1px)}
.btn-google svg{width:18px;height:18px;flex-shrink:0}

.auth-footer{text-align:center;margin-top:22px;font-size:13px;color:#64748b}
.auth-footer a{color:#16a34a;font-weight:600;text-decoration:none}
.auth-footer a:hover{text-decoration:underline}

.alert-info{
  padding:12px 14px;border-radius:8px;background:#eff6ff;
  border:1px solid #bfdbfe;color:#1e40af;font-size:13px;font-weight:500;
  margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;
}

.phone-prefix{
  display:flex;gap:8px;
}
.phone-prefix .country-code{
  width:90px;flex-shrink:0;padding:11px 14px;border-radius:8px;
  border:1.5px solid #e2e8f0;background:#f8fafc;
  font-family:'Poppins',sans-serif;font-size:14px;color:#0f172a;
  outline:none;text-align:center;font-weight:600;
}
.phone-prefix .field{flex:1;margin-bottom:0}

/* Right panel */
.auth-right{
  display:none;flex:1;order:-1;
  background:linear-gradient(145deg,#0f172a 0%,#1a2744 100%);
  padding:60px 56px;align-items:center;justify-content:center;
  position:relative;overflow:hidden;
}
@media(min-width:1024px){.auth-right{display:flex}}
.auth-right::before{
  content:'';position:absolute;
  width:500px;height:500px;border-radius:50%;
  background:radial-gradient(circle,rgba(22,163,74,.18) 0%,transparent 65%);
  left:-100px;top:-120px;pointer-events:none;
}
.auth-right::after{
  content:'';position:absolute;
  width:300px;height:300px;border-radius:50%;
  background:radial-gradient(circle,rgba(22,163,74,.1) 0%,transparent 70%);
  right:-60px;bottom:-60px;pointer-events:none;
}
.auth-right-inner{max-width:380px;color:#fff;position:relative;z-index:1}
.auth-right-icon{
  width:56px;height:56px;border-radius:14px;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  display:grid;place-items:center;margin-bottom:24px;
  box-shadow:0 4px 20px rgba(22,163,74,.4);
}
.auth-right-icon svg{width:26px;height:26px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.auth-right h2{font-size:clamp(22px,2.8vw,32px);font-weight:800;line-height:1.14;letter-spacing:-.02em}
.auth-right h2 em{font-style:normal;color:#4ade80}
.auth-right p{font-size:14px;line-height:1.8;color:#94a3b8;margin-top:14px}

.steps-mini{margin-top:28px;display:flex;flex-direction:column;gap:14px}
.sm-step{display:flex;align-items:flex-start;gap:12px}
.sm-num{
  width:28px;height:28px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  color:#fff;font-size:12px;font-weight:800;
  display:grid;place-items:center;
  box-shadow:0 2px 8px rgba(22,163,74,.3);margin-top:1px;
}
.sm-text h4{font-size:13px;font-weight:700;color:#e2e8f0;margin-bottom:2px}
.sm-text p{font-size:12px;color:#64748b;line-height:1.5}

.auth-right-link{
  display:block;margin-top:28px;font-size:13px;color:#64748b;
}
.auth-right-link a{color:#4ade80;font-weight:600;text-decoration:none}
.auth-right-link a:hover{text-decoration:underline}

@media(max-width:640px){
  .auth-left{padding:32px 20px}
}
</style>

<div class="auth-wrap">
  {{-- Visual side --}}
  <div class="auth-right">
    <div class="auth-right-inner">
      <div class="auth-right-icon">
        <svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      </div>
      <h2>Daftar Menjadi<br><em>Financial Advisor</em></h2>
      <p>Kelola portofolio klien, berikan rekomendasi investasi, dan pantau perkembangan keuangan mereka dalam satu platform.</p>
      <div class="steps-mini">
        <div class="sm-step">
          <div class="sm-num">1</div>
          <div class="sm-text"><h4>Daftar akun Advisor</h4><p>Isi data diri Anda, pengajuan akan direview oleh Admin.</p></div>
        </div>
        <div class="sm-step">
          <div class="sm-num">2</div>
          <div class="sm-text"><h4>Tunggu persetujuan</h4><p>Admin akan menyetujui pendaftaran Anda dalam 1x24 jam.</p></div>
        </div>
        <div class="sm-step">
          <div class="sm-num">3</div>
          <div class="sm-text"><h4>Kelola klien</h4><p>Akses penuh ke dashboard advisor, portfolio klien, dan alat analisa.</p></div>
        </div>
      </div>
      <div class="auth-right-link">Sudah punya akun advisor? <a href="{{ route('login') }}">Masuk sekarang</a></div>
    </div>
  </div>

  {{-- Form side --}}
  <div class="auth-left">
    <div class="auth-form-box">

      <a href="{{ route('index') }}" class="auth-logo">
        <div class="auth-logo-mark">
            <img src="{{ asset('favicon.png') }}" class="w-full h-full object-contain" alt="Logo InvestaPremier"
                loading="lazy" />
        </div>
        <div class="auth-logo-name">InvestaPremier <small>Advisor Portal</small></div>
    </a>

      <h1 class="auth-title">Daftar Advisor</h1>
      <p class="auth-sub">Isi data diri untuk mengajukan pendaftaran sebagai advisor</p>

      <div class="alert-info">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Setelah mendaftar, akun Anda akan direview oleh Admin. Anda akan mendapat notifikasi setelah disetujui.</span>
      </div>

      <form method="POST" action="{{ route('register.advisor') }}">
        @csrf

        <div class="field">
          <label for="name">Nama Lengkap</label>
          <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama Anda"/>
          @error('name')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com"/>
          @error('email')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="phone">Nomor Telepon</label>
          <div class="phone-prefix">
            <input type="text" class="country-code" value="+62" readonly>
            <div class="field">
              <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="81234567890"/>
            </div>
          </div>
          @error('phone')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter"/>
          @error('password')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="password_confirmation">Konfirmasi Password</label>
          <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password"/>
          @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div style="margin-top:8px">
          {!! NoCaptcha::display() !!}
          @error('g-recaptcha-response')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-submit">Ajukan Pendaftaran Advisor</button>

        <div class="divider"><span>atau lanjutkan dengan</span></div>

        <a href="{{ route('auth.google') }}" class="btn-google">
          <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
          Daftar dengan Google
        </a>

        <p class="auth-footer lg-hide">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        <p class="auth-footer" style="margin-top:8px"><a href="{{ route('register') }}">Daftar sebagai nasabah biasa</a></p>
      </form>
    </div>
  </div>
</div>
@endsection
