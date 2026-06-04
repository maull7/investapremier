<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>InvestaPremier WealthOS — Private Wealth Advisory</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet"/>
<style>
/* ═══════════ TOKENS ═══════════ */
:root{
  --g:       #16a34a;
  --g-light: #22c55e;
  --g-dim:   rgba(22,163,74,.1);
  --g-mid:   rgba(22,163,74,.18);
  --g-bord:  rgba(22,163,74,.25);
  --black:   #0f172a;
  --dark:    #1e293b;
  --body:    #334155;
  --muted:   #64748b;
  --light:   #f1f5f9;
  --border:  #e2e8f0;
  --white:   #ffffff;
  --r:       10px;
  --r2:      16px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;-webkit-font-smoothing:antialiased}
body{font-family:'Poppins',sans-serif;background:var(--white);color:var(--body);line-height:1.6}
a{text-decoration:none;color:inherit}

.container{max-width:1180px;margin:0 auto;padding:0 28px}

/* ═══════════ BUTTONS ═══════════ */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:11px 22px;border-radius:var(--r);font-family:'Poppins',sans-serif;
  font-weight:600;font-size:14px;border:1.5px solid transparent;
  cursor:pointer;transition:all .2s;white-space:nowrap;
}
.btn svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.btn-green{background:var(--g);color:#fff;border-color:var(--g);box-shadow:0 2px 12px rgba(22,163,74,.3)}
.btn-green:hover{background:#15803d;border-color:#15803d;box-shadow:0 4px 20px rgba(22,163,74,.4);transform:translateY(-1px)}
.btn-outline{background:transparent;color:var(--black);border-color:var(--border)}
.btn-outline:hover{border-color:var(--g);color:var(--g);background:var(--g-dim)}
.btn-sm{padding:9px 18px;font-size:13px}

/* ═══════════ PILL BADGE ═══════════ */
.pill{
  display:inline-flex;align-items:center;gap:7px;
  padding:5px 14px;border-radius:999px;
  background:var(--g-dim);border:1px solid var(--g-bord);
  font-size:11px;font-weight:600;color:var(--g);letter-spacing:.06em;text-transform:uppercase;
}
.pill-dot{width:6px;height:6px;border-radius:50%;background:var(--g-light);animation:blink 2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}

/* ═══════════ NAVBAR ═══════════ */
.nav{
  position:sticky;top:0;z-index:100;
  background:rgba(255,255,255,.95);
  backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
}
.nav-inner{height:70px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:relative}

.logo{display:flex;align-items:center;gap:10px;flex-shrink:0}
.logo-mark{
  width:38px;height:38px;border-radius:10px;
  background:linear-gradient(135deg,var(--g),var(--g-light));
  display:grid;place-items:center;color:#fff;
  box-shadow:0 2px 12px rgba(22,163,74,.35);
}
.logo-mark svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.logo-name{font-weight:700;font-size:15px;color:var(--black);letter-spacing:-.01em}
.logo-name small{display:block;font-size:10px;font-weight:500;color:var(--muted);letter-spacing:.04em;text-transform:uppercase;margin-top:1px}

.nav-links{display:flex;gap:2px;align-items:center}
.nav-links a{
  position:relative;padding:8px 14px;border-radius:8px;
  font-size:13.5px;font-weight:500;color:var(--body);
  transition:color .2s,background .2s;
}
.nav-links a:hover{color:var(--g);background:var(--g-dim)}
.nav-links a.active{color:var(--g);font-weight:600}
.nav-links a.active::after{
  content:'';position:absolute;bottom:-1px;left:14px;right:14px;
  height:2px;border-radius:999px;background:var(--g);
}

.nav-actions{display:flex;gap:8px;align-items:center}

/* hamburger */
.ham{
  display:none;align-items:center;justify-content:center;
  width:40px;height:40px;border-radius:8px;
  background:var(--light);border:1px solid var(--border);
  cursor:pointer;color:var(--black);
}
.ham svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.ham .ic-close,.ham.open .ic-open{display:none}
.ham.open .ic-close{display:block}
.mob-btns{display:none;flex-direction:column;gap:8px;padding-top:12px;border-top:1px solid var(--border);margin-top:6px}

/* ═══════════ HERO ═══════════ */
.hero{
  background:linear-gradient(165deg,#f8fff8 0%,#fff 50%,#f0fdf4 100%);
  padding:96px 0 80px;border-bottom:1px solid var(--border);
  position:relative;overflow:hidden;
}
.hero::before{
  content:'';position:absolute;
  width:600px;height:600px;border-radius:50%;
  background:radial-gradient(circle,rgba(22,163,74,.08) 0%,transparent 65%);
  right:-150px;top:-150px;pointer-events:none;
}
.hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center;position:relative;z-index:1}
.hero-h1{font-size:clamp(36px,4.8vw,58px);font-weight:800;line-height:1.08;letter-spacing:-.03em;color:var(--black);margin-top:16px}
.hero-h1 em{font-style:normal;color:var(--g)}
.hero-sub{font-size:16px;line-height:1.8;color:var(--body);margin-top:16px;max-width:500px}
.hero-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}

.hero-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:36px}
.hs{padding:16px;border-radius:var(--r);background:#fff;border:1px solid var(--border);box-shadow:0 1px 4px rgba(0,0,0,.04)}
.hs b{display:block;font-size:20px;font-weight:700;color:var(--black);margin-bottom:3px}
.hs span{font-size:11px;color:var(--muted)}

/* Dashboard mockup */
.dash{
  border-radius:var(--r2);background:#fff;
  border:1px solid var(--border);
  box-shadow:0 24px 64px rgba(0,0,0,.1),0 4px 16px rgba(0,0,0,.06);
  overflow:hidden;
}
.dash-bar{
  height:44px;display:flex;align-items:center;justify-content:space-between;
  padding:0 18px;background:var(--light);border-bottom:1px solid var(--border);
}
.dash-dots{display:flex;gap:5px}
.dash-dots span{width:10px;height:10px;border-radius:50%}
.dash-dots span:nth-child(1){background:#f87171}
.dash-dots span:nth-child(2){background:#fbbf24}
.dash-dots span:nth-child(3){background:#4ade80}
.dash-tag{padding:4px 10px;border-radius:999px;background:var(--g-dim);border:1px solid var(--g-bord);font-size:10px;font-weight:700;color:var(--g);text-transform:uppercase;letter-spacing:.06em}
.dash-body{padding:18px}
.dash-row{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px}
.dm{padding:14px;border-radius:var(--r);background:var(--light);border:1px solid var(--border)}
.dm-l{font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em}
.dm-v{font-size:18px;font-weight:700;color:var(--black);margin-top:4px}
.dm-v.g{color:var(--g)}
.alloc-title{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px}
.abar{margin-bottom:8px}
.abar-row{display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:3px}
.abar-bg{height:5px;background:var(--border);border-radius:999px;overflow:hidden}
.abar-fill{height:100%;background:linear-gradient(90deg,var(--g),var(--g-light));border-radius:999px}
.dash-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:12px}
.dchip{padding:4px 10px;border-radius:6px;background:var(--light);border:1px solid var(--border);font-size:10px;color:var(--body);font-weight:500}

/* ═══════════ TICKER ═══════════ */
.ticker{background:var(--black);padding:12px 0;overflow:hidden}
.ticker-track{display:flex;gap:0;width:max-content;animation:tick 28s linear infinite}
@keyframes tick{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.ti{display:inline-flex;align-items:center;gap:8px;padding:0 28px;font-size:12px;font-weight:500;color:#94a3b8;white-space:nowrap;border-right:1px solid rgba(255,255,255,.07)}
.ti .tv{color:#fff;font-weight:600}
.ti .tu{color:#4ade80;font-size:11px}
.ti .td{color:#f87171;font-size:11px}

/* ═══════════ SECTIONS ═══════════ */
.sec{padding:88px 0}
.sec-gray{background:var(--light)}
.sec-green-soft{background:linear-gradient(180deg,#f0fdf4 0%,#fff 100%)}

.sec-head{max-width:620px}
.sec-head h2{font-size:clamp(26px,3.5vw,40px);font-weight:800;line-height:1.14;letter-spacing:-.025em;color:var(--black);margin-top:12px}
.sec-head p{font-size:15px;line-height:1.8;color:var(--body);margin-top:12px}
.sec-gray .sec-head h2,.sec-green-soft .sec-head h2{color:var(--black)}

/* ═══════════ FEATURES ═══════════ */
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:36px}
.feat{
  padding:26px;border-radius:var(--r2);background:#fff;
  border:1px solid var(--border);transition:all .22s;
}
.feat:hover{border-color:var(--g-bord);transform:translateY(-4px);box-shadow:0 16px 40px rgba(22,163,74,.1)}
.feat-icon{
  width:48px;height:48px;border-radius:10px;
  background:var(--g-dim);border:1px solid var(--g-bord);
  display:grid;place-items:center;color:var(--g);margin-bottom:16px;
}
.feat-icon svg{width:21px;height:21px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.feat h3{font-size:15px;font-weight:700;color:var(--black);margin-bottom:7px}
.feat p{font-size:13px;line-height:1.75;color:var(--body)}

/* ═══════════ STEPS ═══════════ */
.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:36px}
.step{
  padding:26px 22px;border-radius:var(--r2);background:#fff;
  border:1px solid var(--border);transition:all .22s;position:relative;
}
.step:hover{border-color:var(--g-bord);transform:translateY(-4px);box-shadow:0 16px 40px rgba(22,163,74,.1)}
/* connector line */
.step:not(:last-child)::after{
  content:'';position:absolute;top:45px;right:-8px;
  width:16px;height:2px;background:var(--border);
  display:none;
}
.step-num{
  width:40px;height:40px;border-radius:50%;
  background:linear-gradient(135deg,var(--g),var(--g-light));
  color:#fff;font-size:16px;font-weight:800;
  display:grid;place-items:center;margin-bottom:16px;
  box-shadow:0 4px 12px rgba(22,163,74,.3);
}
.step h3{font-size:15px;font-weight:700;color:var(--black);margin-bottom:7px}
.step p{font-size:13px;line-height:1.75;color:var(--body)}

/* ═══════════ GLOSARIUM ═══════════ */
.glos-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:36px}
.glos{
  padding:18px 20px;border-radius:var(--r);background:#fff;
  border:1px solid var(--border);transition:all .2s;
}
.glos:hover{border-color:var(--g-bord);box-shadow:0 6px 20px rgba(22,163,74,.09)}
.glos-term{font-size:13px;font-weight:700;color:var(--g);margin-bottom:6px;display:flex;align-items:center;gap:7px}
.glos-term::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--g);flex-shrink:0}
.glos-def{font-size:12.5px;line-height:1.72;color:var(--body)}

/* ═══════════ SECURITY ═══════════ */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:52px;align-items:start}
.check-list{margin-top:22px;display:flex;flex-direction:column;gap:11px}
.ci{display:flex;gap:10px;align-items:flex-start}
.ci-dot{width:20px;height:20px;border-radius:6px;background:var(--g-dim);border:1px solid var(--g-bord);display:grid;place-items:center;color:var(--g);flex-shrink:0;margin-top:2px}
.ci-dot svg{width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
.ci span{font-size:13.5px;line-height:1.65;color:var(--body)}

/* ═══════════ CTA ═══════════ */
.cta{
  border-radius:var(--r2);overflow:hidden;position:relative;
  background:linear-gradient(135deg,var(--black) 0%,#1a2744 100%);
  padding:56px 52px;box-shadow:0 24px 64px rgba(15,23,42,.18);
}
.cta::before{
  content:'';position:absolute;
  width:500px;height:500px;border-radius:50%;
  background:radial-gradient(circle,rgba(34,197,94,.15) 0%,transparent 65%);
  right:-80px;top:-120px;pointer-events:none;
}
.cta-inner{display:grid;grid-template-columns:1.3fr .7fr;gap:28px;align-items:center;position:relative;z-index:1}
.cta h2{font-size:clamp(24px,3vw,38px);font-weight:800;line-height:1.14;color:#fff;margin-top:12px;letter-spacing:-.02em}
.cta p{font-size:14px;line-height:1.8;color:#94a3b8;margin-top:10px}
.cta-btns{display:flex;flex-direction:column;gap:10px}
.btn-white{background:#fff;color:var(--black);border-color:#fff}
.btn-white:hover{background:#f0fdf4;border-color:#f0fdf4;transform:translateY(-1px)}
.btn-ghost-white{background:transparent;color:#fff;border-color:rgba(255,255,255,.25)}
.btn-ghost-white:hover{background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.4)}

/* ═══════════ FOOTER ═══════════ */
footer{background:var(--black);border-top:1px solid rgba(255,255,255,.06)}
.foot-inner{padding:40px 0 28px;display:grid;grid-template-columns:1.4fr 1fr;gap:40px;align-items:start}
.foot-logo{font-size:15px;font-weight:700;color:#fff}
.foot-logo p{font-size:12.5px;color:#64748b;margin-top:6px;font-weight:400}
.foot-copy{font-size:11.5px;color:#475569;margin-top:14px}
.foot-cols{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
.foot-col h4{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px}
.foot-col a{display:block;font-size:13px;color:#64748b;padding:3px 0;transition:color .2s}
.foot-col a:hover{color:var(--g-light)}

/* ═══════════ RESPONSIVE ═══════════ */
@media(max-width:1024px){
  .hero-grid{grid-template-columns:1fr}
  .dash-visual{display:none}
  .feat-grid{grid-template-columns:repeat(2,1fr)}
  .steps-grid{grid-template-columns:repeat(2,1fr)}
  .glos-grid{grid-template-columns:repeat(2,1fr)}
  .two-col{grid-template-columns:1fr}
  .cta-inner{grid-template-columns:1fr}
  .cta-btns{flex-direction:row;flex-wrap:wrap}
  .foot-inner{grid-template-columns:1fr}
  .ham{display:flex}
  .nav-actions,.nav-links{display:none}
  .nav-links{
    position:absolute;top:calc(100% + 8px);left:16px;right:16px;
    background:rgba(255,255,255,.98);backdrop-filter:blur(20px);
    border:1px solid var(--border);border-radius:14px;
    flex-direction:column;padding:12px;gap:2px;
    box-shadow:0 16px 48px rgba(0,0,0,.12);
    opacity:0;pointer-events:none;transform:translateY(-6px);
    transition:opacity .2s,transform .2s;
  }
  .nav-links.open{opacity:1;pointer-events:auto;transform:translateY(0);display:flex}
  .nav-links a{padding:11px 14px;font-size:14px;border-radius:8px}
  .nav-links a.active::after{display:none}
  .nav-links a.active{background:var(--g-dim)}
  .mob-btns{display:flex}
}
@media(max-width:768px){
  .hero{padding:68px 0 56px}
  .sec{padding:60px 0}
  .hero-stats{grid-template-columns:repeat(2,1fr)}
  .cta{padding:36px 24px}
}
@media(max-width:540px){
  .feat-grid,.steps-grid,.glos-grid{grid-template-columns:1fr}
  .hero-actions{flex-direction:column}
  .hero-actions .btn{justify-content:center}
  .cta-btns{flex-direction:column}
  .ticker{display:none}
  .foot-cols{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- ════════ NAVBAR ════════ -->
<header class="nav">
  <div class="container nav-inner">
    <a href="#" class="logo">
      <div class="logo-mark">
        <svg viewBox="0 0 24 24"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
      </div>
      <div class="logo-name">InvestaPremier <small>WealthOS</small></div>
    </a>

    <nav id="nav-menu" class="nav-links">
      <a href="#fitur" data-ml>Fitur</a>
      <a href="#cara-kerja" data-ml>Cara Kerja</a>
      <a href="#glosarium" data-ml>Glosarium</a>
      <a href="#keamanan" data-ml>Keamanan</a>
      <div class="mob-btns">
        <a class="btn btn-outline btn-sm" href="{{ route('login') }}">Masuk</a>
        <a class="btn btn-green btn-sm" href="{{ route('register') }}">Daftar Gratis</a>
      </div>
    </nav>

    <div class="nav-actions">
      <a class="btn btn-outline btn-sm" href="{{ route('login') }}">
        <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
        Masuk
      </a>
      <a class="btn btn-green btn-sm" href="{{ route('register') }}">
        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
        Daftar Gratis
      </a>
    </div>

    <button id="ham" class="ham" aria-expanded="false" aria-label="Menu">
      <svg class="ic-open" viewBox="0 0 24 24"><path d="M3 12h18"/><path d="M3 6h18"/><path d="M3 18h18"/></svg>
      <svg class="ic-close" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
  </div>
</header>


<!-- ════════ HERO ════════ -->
<section class="hero" id="hero">
  <div class="container">
    <div class="hero-grid">
      <div>
        <span class="pill"><span class="pill-dot"></span>Private Wealth Advisory Platform</span>
        <h1 class="hero-h1">Satu Platform,<br><em>Seluruh Kekayaan</em><br>Keluarga.</h1>
        <p class="hero-sub">Kelola portofolio, proteksi, perencanaan, pajak, dan review advisor dalam satu ekosistem digital yang elegan dan terstruktur.</p>
        <div class="hero-actions">
          <a class="btn btn-green" href="{{ route('register') }}">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
            Mulai Gratis
          </a>
          <a class="btn btn-outline" href="{{ route('login') }}">
            <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
            Masuk ke Akun
          </a>
        </div>
        <div class="hero-stats">
          <div class="hs"><b>360°</b><span>Wealth Visibility</span></div>
          <div class="hs"><b>AI Ready</b><span>Smart Analysis</span></div>
          <div class="hs"><b>Multi-Goal</b><span>Family Planning</span></div>
          <div class="hs"><b>Auditable</b><span>Doc Governance</span></div>
        </div>
      </div>

      <div class="dash-visual">
        <div class="dash">
          <div class="dash-bar">
            <div class="dash-dots"><span></span><span></span><span></span></div>
            <span class="dash-tag">Live Dashboard</span>
          </div>
          <div class="dash-body">
            <div class="dash-row">
              <div class="dm"><div class="dm-l">Net Worth</div><div class="dm-v">Rp 18,5 M</div></div>
              <div class="dm"><div class="dm-l">Return YTD</div><div class="dm-v g">+12,4%</div></div>
              <div class="dm"><div class="dm-l">Investable</div><div class="dm-v">Rp 11,2 M</div></div>
              <div class="dm"><div class="dm-l">Likuiditas</div><div class="dm-v">Rp 1,4 M</div></div>
            </div>
            <div class="alloc-title">Alokasi Aset</div>
            <div class="abar"><div class="abar-row"><span>Saham</span><span>30%</span></div><div class="abar-bg"><div class="abar-fill" style="width:30%"></div></div></div>
            <div class="abar"><div class="abar-row"><span>Obligasi</span><span>25%</span></div><div class="abar-bg"><div class="abar-fill" style="width:25%"></div></div></div>
            <div class="abar"><div class="abar-row"><span>Reksa Dana</span><span>20%</span></div><div class="abar-bg"><div class="abar-fill" style="width:20%"></div></div></div>
            <div class="abar"><div class="abar-row"><span>Unit Link</span><span>15%</span></div><div class="abar-bg"><div class="abar-fill" style="width:15%"></div></div></div>
            <div class="abar"><div class="abar-row"><span>Kas</span><span>10%</span></div><div class="abar-bg"><div class="abar-fill" style="width:10%"></div></div></div>
            <div class="dash-chips">
              <span class="dchip">Saham ↑</span>
              <span class="dchip">Obligasi ✓</span>
              <span class="dchip">AI Analisa</span>
              <span class="dchip">Goal 72%</span>
              <span class="dchip">Unit Link</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ════════ TICKER ════════ -->
<div class="ticker">
  <div class="ticker-track">
    @php $tks = [
      ['n'=>'IHSG','v'=>'7.284','c'=>'+0,82%','u'=>true],
      ['n'=>'ANTM','v'=>'1.545','c'=>'+2,13%','u'=>true],
      ['n'=>'BBCA','v'=>'9.225','c'=>'-0,54%','u'=>false],
      ['n'=>'TLKM','v'=>'3.180','c'=>'+1,26%','u'=>true],
      ['n'=>'GOTO','v'=>'68','c'=>'+4,62%','u'=>true],
      ['n'=>'FR0100','v'=>'97,55','c'=>'-0,18%','u'=>false],
      ['n'=>'BMRI','v'=>'6.075','c'=>'+0,91%','u'=>true],
      ['n'=>'ASII','v'=>'4.890','c'=>'-0,20%','u'=>false],
      ['n'=>'UNVR','v'=>'2.610','c'=>'+1,56%','u'=>true],
      ['n'=>'SBR013','v'=>'100,12','c'=>'+0,03%','u'=>true],
    ]; @endphp
    @foreach(array_merge($tks,$tks) as $t)
    <span class="ti">{{ $t['n'] }} <span class="tv">{{ $t['v'] }}</span><span class="{{ $t['u'] ? 'tu' : 'td' }}">{{ $t['c'] }}</span></span>
    @endforeach
  </div>
</div>


<!-- ════════ FITUR ════════ -->
<section id="fitur" class="sec">
  <div class="container">
    <div class="sec-head">
      <span class="pill">Fitur Utama</span>
      <h2>Dirancang untuk advisory premium, bukan sekadar aplikasi investasi.</h2>
      <p>Investasi, proteksi, family planning, dan kolaborasi advisor — semua dalam satu ekosistem digital.</p>
    </div>
    <div class="feat-grid">
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-9-9v9z"/><path d="M21 12A9 9 0 0 0 12 3"/></svg></div>
        <h3>Portfolio Command Center</h3>
        <p>Lihat seluruh aset, alokasi, likuiditas, dan progres investasi dalam satu dashboard premium yang terstruktur.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M12 2l9 4.5v9L12 20l-9-4.5v-9L12 2z"/><path d="M12 7v10"/><path d="M7.5 9.5l4.5 3 4.5-3"/></svg></div>
        <h3>AI-Powered Analysis</h3>
        <p>Analisa laporan keuangan emiten, reksa dana, dan obligasi menggunakan AI dengan insight mendalam dan akurat.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg></div>
        <h3>Goal-Based Planner</h3>
        <p>Rencanakan pendidikan, pensiun, dan legacy fund dengan target terukur dan progress tracking otomatis.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg></div>
        <h3>Protection Hub</h3>
        <p>Kelola polis asuransi, beneficiary, premi, dan gap proteksi untuk menjaga stabilitas finansial keluarga.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/></svg></div>
        <h3>Tax & Compliance Desk</h3>
        <p>Pantau kalender pajak, dokumen pendukung, dan tindak lanjut administratif keluarga secara terstruktur.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
        <h3>Secure Document Vault</h3>
        <p>Simpan dokumen investasi, asuransi, pajak, dan legal dengan akses berbasis peran dan jejak audit lengkap.</p>
      </div>
    </div>
  </div>
</section>


<!-- ════════ CARA KERJA ════════ -->
<section id="cara-kerja" class="sec sec-gray">
  <div class="container">
    <div class="sec-head">
      <span class="pill">Cara Kerja</span>
      <h2>Dari data ke keputusan — dalam empat langkah.</h2>
      <p>Setiap langkah dirancang agar nasabah memahami kondisi wealth keluarga dengan lebih mudah.</p>
    </div>
    <div class="steps-grid">
      <div class="step"><div class="step-num">1</div><h3>Konsolidasikan Data</h3><p>Masukkan aset, tujuan, polis, beneficiary, dan dokumen ke satu sistem yang rapi.</p></div>
      <div class="step"><div class="step-num">2</div><h3>Pantau Progres & Risiko</h3><p>Lihat funding gap, jatuh tempo, dan kesiapan tujuan keluarga secara real-time.</p></div>
      <div class="step"><div class="step-num">3</div><h3>Analisa & Putuskan</h3><p>Gunakan AI Analysis dan data pasar untuk keputusan investasi yang lebih cerdas.</p></div>
      <div class="step"><div class="step-num">4</div><h3>Review Bersama Advisor</h3><p>Lakukan review terstruktur dan tindak lanjuti dalam satu platform terpadu.</p></div>
    </div>
  </div>
</section>


<!-- ════════ GLOSARIUM ════════ -->
<section id="glosarium" class="sec">
  <div class="container">
    <div class="sec-head">
      <span class="pill">Glosarium</span>
      <h2>Istilah penting dalam investasi & wealth management.</h2>
      <p>Pahami terminologi kunci dalam analisa portofolio, produk investasi, dan perencanaan keuangan.</p>
    </div>
    @php
    $gl = [
      ['term'=>'NAB (Nilai Aktiva Bersih)','def'=>'Nilai total aset bersih reksa dana dibagi jumlah unit yang beredar. Dasar perhitungan harga unit setiap hari.'],
      ['term'=>'YTM (Yield to Maturity)','def'=>'Imbal hasil total obligasi jika dipegang hingga jatuh tempo, memperhitungkan harga beli, kupon, dan nilai nominal.'],
      ['term'=>'Diversifikasi','def'=>'Strategi menyebarkan investasi ke berbagai instrumen untuk mengurangi risiko konsentrasi portofolio.'],
      ['term'=>'Reksa Dana','def'=>'Wadah penghimpun dana masyarakat yang diinvestasikan dalam portofolio efek oleh manajer investasi profesional.'],
      ['term'=>'Obligasi / Bond','def'=>'Surat utang jangka menengah-panjang. Penerbit wajib membayar kupon berkala dan melunasi pokok pada jatuh tempo.'],
      ['term'=>'Unit Link','def'=>'Produk asuransi jiwa yang menggabungkan manfaat proteksi dan investasi dalam satu kontrak polis.'],
      ['term'=>'Alpha','def'=>'Kinerja portofolio melebihi benchmark pasar. Alpha positif = return di atas pasar.'],
      ['term'=>'Beta','def'=>'Volatilitas aset relatif terhadap pasar. Beta > 1 lebih volatil; Beta < 1 lebih stabil.'],
      ['term'=>'Saham','def'=>'Bukti kepemilikan sebagian perusahaan. Pemegang berhak atas dividen dan capital gain.'],
      ['term'=>'Imbal Hasil (Return)','def'=>'Keuntungan atau kerugian investasi dalam periode tertentu, dinyatakan dalam persentase dari nilai awal.'],
      ['term'=>'Risiko Likuiditas','def'=>'Risiko aset tidak dapat dijual cepat tanpa penurunan harga signifikan saat butuh dana tunai.'],
      ['term'=>'AUM','def'=>'Asset Under Management — total nilai aset yang dikelola manajer investasi atas nama klien.'],
      ['term'=>'Manajer Investasi','def'=>'Perusahaan berizin OJK yang mengelola dana investor secara profesional untuk mencapai tujuan investasi.'],
      ['term'=>'Profil Risiko','def'=>'Tingkat kesediaan investor menanggung risiko. Terbagi: konservatif, moderat, dan agresif.'],
      ['term'=>'Legacy Planning','def'=>'Perencanaan distribusi kekayaan kepada ahli waris termasuk wasiat, trust, dan peta beneficiary.'],
      ['term'=>'Portofolio','def'=>'Kumpulan instrumen investasi yang dimiliki untuk mencapai tujuan finansial tertentu.'],
      ['term'=>'Kupon Obligasi','def'=>'Pembayaran bunga periodik dari penerbit obligasi kepada pemegang sesuai tingkat bunga yang ditetapkan.'],
      ['term'=>'IHSG','def'=>'Indeks Harga Saham Gabungan — indikator pergerakan seluruh harga saham di Bursa Efek Indonesia.'],
    ];
    @endphp
    <div class="glos-grid">
      @foreach($gl as $item)
      <div class="glos">
        <div class="glos-term">{{ $item['term'] }}</div>
        <div class="glos-def">{{ $item['def'] }}</div>
      </div>
      @endforeach
    </div>
  </div>
</section>


<!-- ════════ KEAMANAN ════════ -->
<section id="keamanan" class="sec sec-gray">
  <div class="container two-col">
    <div>
      <div class="sec-head" style="max-width:none">
        <span class="pill">Keamanan & Governance</span>
        <h2>Dibangun untuk privasi dan governance kelas enterprise.</h2>
        <p>Menangani data portofolio keluarga, beneficiary, dan dokumen advisory — keamanan adalah fondasi utama kami.</p>
      </div>
      <div class="check-list">
        @foreach([
          'Role-based access untuk client, advisor, household member, dan admin',
          'Audit trail lengkap untuk login, akses dokumen, dan update data',
          'Document vault dengan kontrol akses, histori versi, dan status dokumen',
          'Notifikasi terstruktur untuk review, jatuh tempo, dan action plan',
          'Data terenkripsi sesuai standar kepatuhan OJK',
        ] as $c)
        <div class="ci">
          <div class="ci-dot"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
          <span>{{ $c }}</span>
        </div>
        @endforeach
      </div>
    </div>
    <div class="feat-grid" style="margin-top:0;grid-template-columns:repeat(2,1fr)">
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
        <h3>Access Control</h3>
        <p>Hak akses berbasis peran dan household keluarga.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></div>
        <h3>Document Audit</h3>
        <p>Setiap dokumen punya status, versi, dan histori akses.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 7 3 9H3c0-2 3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg></div>
        <h3>Smart Alerts</h3>
        <p>Reminder otomatis untuk goal gap, polis, dan review.</p>
      </div>
      <div class="feat">
        <div class="feat-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
        <h3>Operational Trust</h3>
        <p>Dirancang untuk kepercayaan jangka panjang nasabah.</p>
      </div>
    </div>
  </div>
</section>


<!-- ════════ CTA ════════ -->
<section class="sec" style="padding-top:0">
  <div class="container">
    <div class="cta">
      <div class="cta-inner">
        <div>
          <span class="pill" style="background:rgba(34,197,94,.15);border-color:rgba(34,197,94,.3);color:#4ade80">Mulai Sekarang</span>
          <h2>Bangun pengalaman wealth advisory yang lebih terintegrasi.</h2>
          <p>Daftar gratis dan mulai kelola portofolio, analisa investasi, dan review advisor dalam satu platform premium.</p>
        </div>
        <div class="cta-btns">
          <a class="btn btn-green" href="{{ route('register') }}">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
            Daftar Gratis
          </a>
          <a class="btn btn-ghost-white" href="{{ route('login') }}">Sudah punya akun? Masuk</a>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ════════ FOOTER ════════ -->
<footer>
  <div class="container foot-inner">
    <div>
      <div class="foot-logo">
        InvestaPremier WealthOS
        <p>Premium wealth advisory platform for modern private clients.</p>
      </div>
      <div class="foot-copy">&copy; {{ date('Y') }} InvestaPremier. All rights reserved.</div>
    </div>
    <div class="foot-cols">
      <div class="foot-col">
        <h4>Platform</h4>
        <a href="#fitur">Fitur</a>
        <a href="#cara-kerja">Cara Kerja</a>
        <a href="#glosarium">Glosarium</a>
        <a href="#keamanan">Keamanan</a>
      </div>
      <div class="foot-col">
        <h4>Akun</h4>
        <a href="{{ route('login') }}">Login</a>
        <a href="{{ route('register') }}">Daftar Gratis</a>
      </div>
    </div>
  </div>
</footer>


<script>
(function(){
  const ham  = document.getElementById('ham');
  const menu = document.getElementById('nav-menu');
  if(!ham||!menu) return;

  // Mobile menu toggle
  const setOpen = o => {
    menu.classList.toggle('open', o);
    ham.classList.toggle('open', o);
    ham.setAttribute('aria-expanded', o);
  };
  ham.addEventListener('click', e => { e.stopPropagation(); setOpen(!menu.classList.contains('open')); });
  menu.querySelectorAll('[data-ml]').forEach(l => l.addEventListener('click', () => setOpen(false)));
  document.addEventListener('click', e => {
    if(menu.classList.contains('open') && !menu.contains(e.target) && !ham.contains(e.target)) setOpen(false);
  });
  document.addEventListener('keydown', e => { if(e.key==='Escape') setOpen(false); });

  // Active nav link on scroll
  const sections = document.querySelectorAll('section[id], div[id]');
  const links    = document.querySelectorAll('.nav-links a[href^="#"]');
  const activate = () => {
    let current = '';
    sections.forEach(s => {
      if(window.scrollY >= s.offsetTop - 100) current = s.id;
    });
    links.forEach(l => {
      l.classList.toggle('active', l.getAttribute('href') === '#' + current);
    });
  };
  window.addEventListener('scroll', activate, {passive:true});
  activate();
})();
</script>
</body>
</html>
