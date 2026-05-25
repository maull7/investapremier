<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>InvestaPremier WealthOS - Presentation Mockup</title>
  <style>
    :root{--bg:#f7fafc;--dark:#0b1324;--muted:#475569;--line:#e2e8f0;--white:#fff;--accent:#0f766e;--gold:#c9a227}
    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{margin:0;font-family:Inter,Segoe UI,Arial,sans-serif;background:linear-gradient(180deg,#f8fbfd 0%,#ffffff 50%,#f8fbfd 100%);color:#0f172a}
    a{text-decoration:none;color:inherit}
    .toolbar{position:sticky;top:0;z-index:10;background:rgba(255,255,255,.9);backdrop-filter:blur(10px);border-bottom:1px solid rgba(226,232,240,.8);padding:14px 24px;display:flex;justify-content:space-between;align-items:center;gap:16px}
    .toolbar-brand{display:flex;align-items:center;gap:12px;font-weight:800}
    .toolbar-brand small{display:block;color:#64748b;font-weight:500;margin-top:2px}
    .toolbar-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .toolbar a,.toolbar button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:10px 16px;border-radius:8px;border:1px solid var(--line);background:#fff;color:#0f172a;font-weight:700;cursor:pointer;text-decoration:none}
    .toolbar .primary{background:var(--dark);border-color:var(--dark);color:#fff}
    .deck{max-width:1280px;margin:22px auto;padding:0 16px 40px}
    .slide{aspect-ratio:16/9;background:#fff;border:1px solid var(--line);border-radius:8px;box-shadow:0 14px 40px rgba(15,23,42,.10);padding:42px;display:flex;flex-direction:column;justify-content:space-between;margin-bottom:26px;page-break-after:always}
    .cover{background:linear-gradient(135deg,#ffffff 0%,#f8fbfd 100%)}
    .brand{display:flex;align-items:center;gap:12px}.mark{width:44px;height:44px;border-radius:8px;background:#0b1324;color:#fff;display:grid;place-items:center;font-weight:800;box-shadow:0 12px 28px rgba(15,23,42,.15)}
    .mark svg,.toolbar svg,.icon svg{width:20px;height:20px;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;fill:none}
    .kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid var(--line);border-radius:999px;font-size:12px;font-weight:700;background:#fff}
    h1{font-size:54px;line-height:1.04;margin:18px 0 0;letter-spacing:0} h2{font-size:38px;line-height:1.12;margin:0;letter-spacing:0} h3{font-size:24px;margin:0 0 10px}
    p{margin:0;color:#475569;line-height:1.7;font-size:18px}.small{font-size:14px}.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
    .card{border:1px solid var(--line);border-radius:8px;padding:22px;background:#fff}.soft{background:#f8fafc}.dark{background:#0b1324;color:#fff}.dark p{color:#cbd5e1}.muted{color:#475569}
    .dark .card{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1);box-shadow:none}.dark .card h3{color:#fff}
    ul{margin:0;padding-left:20px} li{margin:8px 0;color:#334155;font-size:18px;line-height:1.6}
    .dark li{color:#e2e8f0}
    .metric{font-size:28px;font-weight:800}.footer{display:flex;justify-content:space-between;color:#64748b;font-size:14px}
    .bar{height:12px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-top:8px}.fill{height:100%;background:#0b1324;border-radius:999px}.teal{background:#0f766e}
    .table{width:100%;border-collapse:collapse}.table th,.table td{border-bottom:1px solid var(--line);padding:12px 10px;text-align:left;font-size:16px}.table th{color:#64748b;font-weight:700}
    .badge{display:inline-flex;align-items:center;justify-content:center;padding:7px 12px;border-radius:8px;background:#f1f5f9;font-size:12px;font-weight:700}
    .hero-box{border:1px solid var(--line);border-radius:8px;padding:26px;background:rgba(255,255,255,.88)}
    .icon{width:44px;height:44px;border-radius:8px;background:var(--dark);color:#fff;display:grid;place-items:center;margin-bottom:16px}
    .dark .icon{background:rgba(255,255,255,.1)}
    @media print{body{background:#fff}.toolbar{display:none}.deck{max-width:none;margin:0;padding:0}.slide{margin:0;border:none;border-radius:0;box-shadow:none;break-after:page}}
    @media (max-width:900px){.toolbar{align-items:flex-start;flex-direction:column;padding:14px 16px}.toolbar-actions{width:100%;justify-content:stretch}.toolbar a,.toolbar button{flex:1}.grid-2,.grid-3,.grid-4{grid-template-columns:1fr} .slide{aspect-ratio:auto;min-height:100vh;padding:24px} h1{font-size:40px} h2{font-size:30px} }
    @media (max-width:520px){.deck{padding:0 12px 28px}.slide{padding:20px}.toolbar a,.toolbar button{width:100%;flex:none}.toolbar-brand small{display:none}h1{font-size:32px}h2{font-size:26px}h3{font-size:20px}p,li{font-size:16px}.metric{font-size:24px}}
  </style>
</head>
<body>
  <div class="toolbar">
    <div class="toolbar-brand"><span class="mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 4v10l-7 4-7-4V7l7-4z"></path><path d="M12 8v8"></path><path d="M8.5 10.5l3.5 2 3.5-2"></path></svg></span><div>InvestaPremier<small>WealthOS Presentation</small></div></div>
    <div class="toolbar-actions"><a href="{{route('index')}}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>Landing Page</a><button class="primary" onclick="window.print()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M6 14h12v8H6z"></path></svg>Print / PDF</button></div>
  </div>

  <div class="deck">
    <section class="slide cover">
      <div>
        <div class="brand"><div class="mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 4v10l-7 4-7-4V7l7-4z"></path><path d="M12 8v8"></path><path d="M8.5 10.5l3.5 2 3.5-2"></path></svg></div><div><strong>InvestaPremier</strong><div class="small muted">WealthOS</div></div></div>
        <div style="margin-top:42px"><span class="kicker">Private Wealth Advisory Platform</span><h1>One Family,<br>One Financial Cockpit.</h1><p style="max-width:760px;margin-top:20px">Platform wealth advisory premium untuk membantu nasabah prioritas dan private mengelola portofolio investasi, proteksi, pendidikan, legacy, pajak, dan kolaborasi dengan advisor dalam satu ekosistem digital.</p></div>
      </div>
      <div class="footer"><span>Deck overview</span><span>01</span></div>
    </section>

    <section class="slide">
      <div>
        <span class="kicker">Problem Statement</span>
        <h2 style="margin-top:16px">Mengapa WealthOS dibutuhkan</h2>
        <div class="grid-2" style="margin-top:28px">
          <div class="card soft"><h3>Masalah Nasabah</h3><ul><li>Aset tersebar di banyak instrumen dan institusi</li><li>Tujuan keuangan belum terhubung dengan portofolio</li><li>Dokumen penting keluarga tidak terpusat</li><li>Proteksi, beneficiary, dan legacy sering belum rapi</li></ul></div>
          <div class="card soft"><h3>Masalah Advisor</h3><ul><li>Review klien masih manual dan tidak konsisten</li><li>Rekomendasi dan tindak lanjut tercecer</li><li>Tidak ada workspace tunggal untuk wealth planning</li><li>Dokumen dan histori interaksi belum auditable</li></ul></div>
        </div>
      </div>
      <div class="footer"><span>Problem & pain points</span><span>02</span></div>
    </section>

    <section class="slide dark">
      <div>
        <span class="kicker" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:#fff">Solution</span>
        <h2 style="margin-top:16px">Satu platform untuk wealth planning keluarga dan workflow advisory</h2>
        <div class="grid-4" style="margin-top:28px">
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-9-9v9z"></path><path d="M21 12A9 9 0 0 0 12 3"></path></svg></div><h3>Portfolio 360</h3><p>Net worth, allocation, liquidity, maturity, dan watchlist review.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5z"></path><path d="M6 12v5c3 2 9 2 12 0v-5"></path></svg></div><h3>Goal Planner</h3><p>Pendidikan, pensiun, legacy fund, dan kebutuhan keluarga.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"></path></svg></div><h3>Protection Hub</h3><p>Polis, premi, beneficiary, dan coverage gap.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V9"></path><path d="M19 21V9"></path><path d="M3 9l9-6 9 6"></path><path d="M9 21v-8h6v8"></path></svg></div><h3>Legacy Desk</h3><p>Asset map, beneficiary map, dan checklist warisan.</p></div>
        </div>
      </div>
      <div class="footer"><span>Core solution</span><span>03</span></div>
    </section>

    <section class="slide">
      <div>
        <span class="kicker">Dashboard Preview</span>
        <h2 style="margin-top:16px">Tampilan utama nasabah</h2>
        <div class="hero-box" style="margin-top:24px">
          <div class="grid-4">
            <div class="card soft"><div class="small muted">Net Worth</div><div class="metric">Rp 18,5 M</div></div>
            <div class="card soft"><div class="small muted">Investable Assets</div><div class="metric">Rp 11,2 M</div></div>
            <div class="card soft"><div class="small muted">Liquidity</div><div class="metric">Rp 1,4 M</div></div>
            <div class="card soft"><div class="small muted">Next Review</div><div class="metric">24 Mar 2026</div></div>
          </div>
          <div class="grid-2" style="margin-top:18px">
            <div class="card"><h3>Asset Allocation</h3><p class="small">Portofolio keluarga per kategori aset</p><div style="margin-top:18px"><div class="small">Saham 30%</div><div class="bar"><div class="fill" style="width:30%"></div></div><div class="small" style="margin-top:10px">Obligasi 25%</div><div class="bar"><div class="fill" style="width:25%"></div></div><div class="small" style="margin-top:10px">Reksa Dana 20%</div><div class="bar"><div class="fill" style="width:20%"></div></div></div></div>
            <div class="card"><h3>Goal Progress</h3><p class="small">Status target keluarga</p><div style="margin-top:18px"><div class="small">Pendidikan Anak 72%</div><div class="bar"><div class="fill teal" style="width:72%"></div></div><div class="small" style="margin-top:10px">Pensiun 54%</div><div class="bar"><div class="fill teal" style="width:54%"></div></div><div class="small" style="margin-top:10px">Legacy Fund 38%</div><div class="bar"><div class="fill teal" style="width:38%"></div></div></div></div>
          </div>
        </div>
      </div>
      <div class="footer"><span>Client cockpit</span><span>04</span></div>
    </section>

    <section class="slide">
      <div>
        <span class="kicker">Modules</span>
        <h2 style="margin-top:16px">Modul produk</h2>
        <div class="grid-3" style="margin-top:28px">
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-9-9v9z"></path><path d="M21 12A9 9 0 0 0 12 3"></path></svg></div><h3>Portfolio Command Center</h3><p>Ringkasan aset, alokasi, performa, likuiditas, dan alert jatuh tempo.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5z"></path><path d="M6 12v5c3 2 9 2 12 0v-5"></path></svg></div><h3>Goal Planner</h3><p>Funding gap, progress target, kontribusi berkala, dan scenario planning.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"></path></svg></div><h3>Protection Hub</h3><p>Daftar polis, premi, coverage, beneficiary, dan review proteksi.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V9"></path><path d="M19 21V9"></path><path d="M3 9l9-6 9 6"></path><path d="M9 21v-8h6v8"></path></svg></div><h3>Legacy Desk</h3><p>Family structure, beneficiary map, asset transfer plan, checklist.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z"></path><path d="M8 8h8"></path><path d="M8 12h8"></path><path d="M8 16h5"></path></svg></div><h3>Tax Desk</h3><p>Kalender pajak, dokumen, checklist, dan catatan tindak lanjut.</p></div>
          <div class="card"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.9"></path><path d="M16 3.1a4 4 0 0 1 0 7.8"></path></svg></div><h3>Advisor Collaboration</h3><p>Review session, recommendation workflow, notes, tasks, dan reports.</p></div>
        </div>
      </div>
      <div class="footer"><span>Modules overview</span><span>05</span></div>
    </section>

    <section class="slide">
      <div>
        <span class="kicker">Target Users</span>
        <h2 style="margin-top:16px">Siapa yang akan menggunakan platform ini</h2>
        <div class="grid-4" style="margin-top:28px">
          <div class="card soft"><h3>Nasabah Prioritas</h3><p>Keluarga affluent yang butuh dashboard wealth yang menyeluruh.</p></div>
          <div class="card soft"><h3>Nasabah Private</h3><p>Klien HNW yang memerlukan legacy readiness dan privasi tinggi.</p></div>
          <div class="card soft"><h3>Advisor / RM</h3><p>Penasihat yang memerlukan workflow review dan rekomendasi yang rapi.</p></div>
          <div class="card soft"><h3>Boutique Firm</h3><p>Firma advisory yang membutuhkan client workspace premium.</p></div>
        </div>
      </div>
      <div class="footer"><span>User segments</span><span>06</span></div>
    </section>

    <section class="slide">
      <div>
        <span class="kicker">Value Proposition</span>
        <h2 style="margin-top:16px">Keunggulan utama WealthOS</h2>
        <div class="grid-2" style="margin-top:28px">
          <div class="card"><ul><li>Bukan aplikasi trading, tetapi command center wealth keluarga</li><li>Fokus pada advisory, governance, dan perencanaan tujuan</li><li>Memadukan investasi, proteksi, legacy, dan pajak</li></ul></div>
          <div class="card"><ul><li>Kolaborasi advisor-client terdokumentasi dengan rapi</li><li>Document vault dan audit trail memperkuat kepercayaan</li><li>Siap dikembangkan menjadi family office lite platform</li></ul></div>
        </div>
      </div>
      <div class="footer"><span>Strategic value</span><span>07</span></div>
    </section>

    <section class="slide">
      <div>
        <span class="kicker">Workflow</span>
        <h2 style="margin-top:16px">Alur penggunaan</h2>
        <div class="grid-4" style="margin-top:28px">
          <div class="card"><div class="badge">01</div><h3 style="margin-top:14px">Onboarding</h3><p>Masukkan profil, keluarga, tujuan, aset, dan dokumen inti.</p></div>
          <div class="card"><div class="badge">02</div><h3 style="margin-top:14px">Monitoring</h3><p>Pantau wealth summary, funding gap, proteksi, dan deadline penting.</p></div>
          <div class="card"><div class="badge">03</div><h3 style="margin-top:14px">Review</h3><p>Advisor menyusun review, notes, dan rekomendasi.</p></div>
          <div class="card"><div class="badge">04</div><h3 style="margin-top:14px">Execution Follow-up</h3><p>Task, dokumen, dan tindak lanjut tercatat dalam sistem.</p></div>
        </div>
      </div>
      <div class="footer"><span>User journey</span><span>08</span></div>
    </section>

    <section class="slide dark">
      <div>
        <span class="kicker" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:#fff">Governance</span>
        <h2 style="margin-top:16px">Keamanan, privasi, dan kontrol akses</h2>
        <div class="grid-2" style="margin-top:28px">
          <div class="card"><h3>Security by design</h3><p>RBAC, audit trail, session control, document access logs, dan approval flow untuk data sensitif.</p></div>
          <div class="card"><h3>Operational trust</h3><p>Didesain untuk membangun rasa aman bagi nasabah prioritas/private yang membawa data keluarga dan kekayaan yang sensitif.</p></div>
        </div>
      </div>
      <div class="footer"><span>Governance layer</span><span>09</span></div>
    </section>

    <section class="slide">
      <div>
        <span class="kicker">Roadmap</span>
        <h2 style="margin-top:16px">Tahapan implementasi produk</h2>
        <table class="table" style="margin-top:28px">
          <thead><tr><th>Phase</th><th>Fokus</th><th>Output</th></tr></thead>
          <tbody>
            <tr><td>MVP</td><td>Onboarding, portfolio, goals, documents, advisor workflow</td><td>Client cockpit dasar siap pilot</td></tr>
            <tr><td>Phase 2</td><td>Protection, legacy, tax desk, reports</td><td>Wealth planning lebih menyeluruh</td></tr>
            <tr><td>Phase 3</td><td>IPS, scenario engine, integrations, white-label</td><td>Advisory suite lebih matang</td></tr>
          </tbody>
        </table>
      </div>
      <div class="footer"><span>Implementation roadmap</span><span>10</span></div>
    </section>

    <section class="slide cover">
      <div>
        <span class="kicker">Closing</span>
        <h2 style="margin-top:16px">InvestaPremier WealthOS</h2>
        <p style="margin-top:18px;max-width:760px">Menyatukan investasi, proteksi, legacy, pajak, dan advisory workflow ke dalam satu pengalaman digital premium untuk keluarga modern.</p>
      </div>
      <div class="footer"><span>End of mockup</span><span>11</span></div>
    </section>
  </div>
</body>
</html>
