<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>InvestaPremier WealthOS — Private Wealth Advisory</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
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


<!-- ════════ HARGA LAYANAN ════════ -->
<section id="harga" class="sec sec-green-soft">
  <div class="container">
    <div class="sec-head">
      <span class="pill">Harga Layanan</span>
      <h2>Pilih paket yang sesuai kebutuhan investasi Anda.</h2>
      <p>Nikmati akses penuh ke fitur analisa, rekomendasi, dan konsultasi investasi premium.</p>
    </div>
    <div class="pricing-grid">
      @php
        $pricingPlans = [
          [
            'name' => 'Review Produk Investasi',
            'price' => '100rb',
            'featured' => false,
            'features' => [
              ['label' => 'Kinerja', 'on' => true],
              ['label' => 'Benchmark (All Funds)', 'on' => true],
              ['label' => 'Analisa Pengelolaan Investasi', 'on' => false],
              ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => false],
              ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => false],
              ['label' => 'Kriteria underlying', 'on' => false],
              ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => false],
              ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => false],
              ['label' => 'untuk mencapai Tujuan Investasi', 'on' => false],
            ],
          ],
          [
            'name' => 'Analisa Produk Investasi',
            'price' => '250rb',
            'featured' => true,
            'features' => [
              ['label' => 'Kinerja', 'on' => true],
              ['label' => 'Benchmark (All Funds)', 'on' => true],
              ['label' => 'Analisa Pengelolaan Investasi', 'on' => true],
              ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => true],
              ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => false],
              ['label' => 'Kriteria underlying', 'on' => false],
              ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => false],
              ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => false],
              ['label' => 'untuk mencapai Tujuan Investasi', 'on' => false],
            ],
          ],
          [
            'name' => 'Rekomendasi Produk Investasi',
            'price' => '350rb',
            'featured' => false,
            'features' => [
              ['label' => 'Kinerja', 'on' => true],
              ['label' => 'Benchmark (All Funds)', 'on' => true],
              ['label' => 'Analisa Pengelolaan Investasi', 'on' => true],
              ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => true],
              ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => true],
              ['label' => 'Kriteria underlying', 'on' => true],
              ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => true],
              ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => false],
              ['label' => 'untuk mencapai Tujuan Investasi', 'on' => false],
            ],
          ],
          [
            'name' => 'Penasihat Investasi Komprehensif',
            'price' => '1jt',
            'featured' => true,
            'features' => [
              ['label' => 'Kinerja', 'on' => true],
              ['label' => 'Benchmark (All Funds)', 'on' => true],
              ['label' => 'Analisa Pengelolaan Investasi', 'on' => true],
              ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => true],
              ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => true],
              ['label' => 'Kriteria underlying', 'on' => true],
              ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => true],
              ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => true],
              ['label' => 'untuk mencapai Tujuan Investasi', 'on' => true],
            ],
          ],
        ];
      @endphp
      @foreach ($pricingPlans as $plan)
        <div class="pricing-card {{ $plan['featured'] ? 'featured' : '' }}">
          @if ($plan['featured'])
            <span class="pricing-badge">Populer</span>
          @endif
          <div class="pricing-name">{{ $plan['name'] }}</div>
          <div class="pricing-price"><em>{{ $plan['price'] }}</em> <span>/bln</span></div>
          <div class="pricing-divider"></div>
          <ul class="pricing-features">
            @foreach ($plan['features'] as $f)
              <li>
                <span class="pf-icon {{ $f['on'] ? 'pf-on' : 'pf-off' }}">
                  @if ($f['on'])
                    <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                  @else
                    <svg viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                  @endif
                </span>
                {{ $f['label'] }}
              </li>
            @endforeach
          </ul>
          <div class="mt-auto pt-4">
            <button onclick="openPricingModal('{{ $plan['name'] }}')" class="btn btn-green btn-sm w-full justify-center">
              <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
              Langganan Sekarang
            </button>
          </div>
        </div>
      @endforeach
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


<!-- ════════ MODAL SEGERA HADIR ════════ -->
<div id="pricingModal" class="modal-overlay" onclick="if(event.target===this)closePricingModal()">
  <div class="modal-box">
    <div class="modal-icon">
      <svg viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/><circle cx="12" cy="12" r="4"/></svg>
    </div>
    <h3 id="modal-plan-name">Segera Hadir</h3>
    <p>Fitur langganan <strong id="modal-plan-label"></strong> masih dalam tahap pengembangan. Kami akan memberitahu Anda begitu tersedia!</p>
    <button onclick="closePricingModal()" class="modal-close">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
      Saya Mengerti
    </button>
  </div>
</div>

<script>
function openPricingModal(planName){
  document.getElementById('modal-plan-label').textContent = planName;
  document.getElementById('pricingModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closePricingModal(){
  document.getElementById('pricingModal').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closePricingModal(); });

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
