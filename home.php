<?php
require_once 'config.php';

// Get statistics
$total_members = $conn->query("SELECT COUNT(*) as total FROM members")->fetch_assoc()['total'];
$total_trophies = $conn->query("SELECT COUNT(*) as total FROM trophies")->fetch_assoc()['total'];

// Get trophies with photos
$trophies_list = $conn->query("SELECT * FROM trophies ORDER BY id DESC LIMIT 6");

// Check if user already registered as member
$is_member = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT * FROM members WHERE user_id = '$user_id'");
    $is_member = $check->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Volley Club – PORPPAD Surabaya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&display=swap" rel="stylesheet" />
    
    <style>
/* ===== IMPORT FONTS ===== */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Inter:wght@400;500;600;700&display=swap');

/* ===== DISABLE ZOOM ON MOBILE ===== */
html {
    touch-action: manipulation;
    -webkit-text-size-adjust: 100%;
}

* {
    -webkit-tap-highlight-color: transparent;
}

/* ===== RESET & BASE ===== */
:root {
    --primary: #2c5282;
    --primary-dark: #1e3a5f;
    --primary-light: #4a7ba7;
    --gold: #c9a961;
    --gold-light: #e0c488;
}

* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}

body { 
    font-family: 'Inter', sans-serif; 
    padding-top: 80px;
    overflow-x: hidden;
}

/* ===== NAVBAR - MOBILE OPTIMIZED ===== */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    z-index: 1000;
    padding: 1rem 0;
}

.logo-img { 
    width: 50px; 
    height: 50px; 
    border-radius: 8px;
    object-fit: cover;
}

.brand-title { 
    font-weight: 700; 
    font-size: 1.3rem; 
    color: var(--primary-dark); 
    line-height: 1.2;
}

.brand-sub { 
    font-size: 0.85rem; 
    color: #6c757d; 
}

.nav-link {
    font-size: 1.05rem;
    padding: 0.75rem 1rem !important;
    color: #333;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-link:hover, .nav-link.active { 
    color: var(--primary); 
    background: rgba(44, 82, 130, 0.1);
    border-radius: 8px;
}

.btn-rounded { 
    border-radius: 20px; 
    padding: 0.5rem 1.2rem; 
    font-size: 0.95rem;
    font-weight: 500;
}

.btn-gold { 
    background: linear-gradient(90deg, var(--gold), var(--gold-light)); 
    color: #111; 
    border: none;
}

/* ===== HERO SECTION - Professional Navy Blue ===== */
.hero-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, 
        #1e3a5f 0%,
        #2c5282 35%,
        #4a7ba7 70%,
        #5a8bb8 100%
    );
    position: relative;
    padding: 2rem 1rem;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(201, 169, 97, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
    pointer-events: none;
}

.hero-section::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 120px;
    background: linear-gradient(to top, #f8fafc, transparent);
    pointer-events: none;
}

/* Hero Typography - Professional Fonts */
.hero-section h1:first-of-type {
    font-family: 'Inter', sans-serif !important;
    font-size: clamp(1.5rem, 4vw, 2.5rem) !important;
    font-weight: 600 !important;
    letter-spacing: 0.05em !important;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
    color: rgba(255, 255, 255, 0.95);
    text-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
}

.orbitron-title {
    font-family: 'Playfair Display', serif !important;
    font-size: clamp(3rem, 10vw, 7rem) !important;
    font-weight: 900 !important;
    letter-spacing: 0.08em !important;
    background: linear-gradient(135deg, #c9a961 0%, #e0c488 50%, #d4af37 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1.5rem;
    text-shadow: none;
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-top: 2rem;
    max-width: 100%;
    padding: 0 1rem;
}

.hero-stats .stat {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(20px);
    padding: 1.25rem 0.75rem;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.25);
    text-align: center;
    transition: all 0.3s;
}

.hero-stats .stat:hover {
    background: rgba(255,255,255,0.22);
    transform: translateY(-5px);
}

.hero-stats .stat h3 {
    font-size: clamp(1.75rem, 5vw, 3rem);
    margin: 0 0 0.25rem 0;
    color: #fff;
    font-weight: 900;
    text-shadow: 0 3px 10px rgba(0,0,0,0.5);
}

.hero-stats small {
    font-size: clamp(0.85rem, 2vw, 1rem);
    color: #fff;
    text-shadow: 0 2px 5px rgba(0,0,0,0.3);
    font-weight: 500;
}

/* ===== SECTIONS ===== */
section { 
    padding: clamp(3rem, 6vw, 5rem) 0; 
}

.section-title {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title h3 {
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 0.5rem;
}

.section-title p {
    font-size: clamp(0.95rem, 2vw, 1.1rem);
    color: #6c757d;
}

/* ===== ABOUT SECTION - Light Blue to White ===== */
#about {
    background: linear-gradient(180deg, 
        #f8fafc 0%,
        #ffffff 50%,
        #fafbfc 100%
    ) !important;
}

/* ===== VISI MISI - Pure White ===== */
#visi-misi {
    background: #ffffff !important;
}

#visi-misi .row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.visi-misi-card {
    background: white;
    padding: 2.5rem;
    border-radius: 15px;
    border: 1px solid #e8eef5;
    box-shadow: 0 4px 20px rgba(30, 58, 95, 0.06);
    height: 100%;
    border-left: 5px solid var(--primary);
    transition: transform 0.3s;
}

.visi-misi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(30, 58, 95, 0.12);
}

.vm-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
}

.visi-misi-card h4 {
    color: var(--primary-dark);
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.visi-misi-card p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #555;
}

.vm-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.vm-list li {
    padding: 0.75rem 0;
    padding-left: 2rem;
    position: relative;
    font-size: 1rem;
    line-height: 1.6;
    color: #555;
}

.vm-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--primary);
    font-weight: bold;
    font-size: 1.3rem;
}

/* ===== FASILITAS - Soft Blue Background ===== */
#fasilitas {
    background: linear-gradient(180deg, 
        #f0f4f8 0%,
        #f5f8fa 50%,
        #fafbfc 100%
    ) !important;
}

#fasilitas .row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.facility-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    border: 1px solid #e8eef5;
    box-shadow: 0 3px 15px rgba(30, 58, 95, 0.05);
    text-align: center;
    height: 100%;
    transition: all 0.3s;
}

.facility-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 6px 25px rgba(30, 58, 95, 0.12);
    border-color: rgba(201, 169, 97, 0.3);
}

.facility-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    color: var(--primary-light);
}

.facility-card h5 {
    color: var(--primary-dark);
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.facility-card p {
    font-size: 1rem;
    color: #666;
    line-height: 1.6;
}

/* ===== TROPHY CARDS - Light Grey ===== */
#prestasi {
    background: linear-gradient(180deg,
        #fafbfc 0%,
        #f8f9fa 100%
    ) !important;
}

.trophy-card {
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #e8eef5;
    box-shadow: 0 2px 12px rgba(30, 58, 95, 0.06);
    transition: all 0.3s;
    height: 100%;
}

.trophy-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 25px rgba(30, 58, 95, 0.12);
}

.trophy-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: white;
}

.trophy-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.trophy-content {
    padding: 1.5rem;
}

.trophy-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.badge-prestasi { background: #ffeaa7; color: #d63031; }
.badge-turnamen { background: #74b9ff; color: #0984e3; }
.badge-kejuaraan { background: #a29bfe; color: #6c5ce7; }
.badge-penghargaan { background: #fd79a8; color: #e84393; }

.trophy-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.trophy-desc {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.trophy-date {
    font-size: 0.85rem;
    color: #95a5a6;
}

/* ===== TEAM CARDS - White Background ===== */
#team {
    background: #ffffff !important;
}

.team-card {
    color: white;
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, #2c5282 0%, #4a7ba7 100%);
    border: none;
    box-shadow: 0 4px 20px rgba(30, 58, 95, 0.15);
    transition: all 0.3s;
}

.team-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(30, 58, 95, 0.25);
}

.team-photo {
    height: 200px;
    overflow: hidden;
    background: linear-gradient(180deg, #e9f0ff, #fff);
    display: flex;
    align-items: center;
    justify-content: center;
}

.team-photo img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover;
}

.team-card h5 { 
    font-size: 1.1rem;
    font-weight: 600;
}

/* ===== GALLERY - Soft Blue ===== */
#galeri {
    background: linear-gradient(180deg,
        #f5f8fa 0%,
        #f0f4f8 100%
    ) !important;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.gallery-item {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e8eef5;
    box-shadow: 0 2px 12px rgba(30, 58, 95, 0.06);
    transition: all 0.3s;
    aspect-ratio: 1;
    cursor: pointer;
}

.gallery-item:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 25px rgba(30, 58, 95, 0.15);
    border-color: rgba(201, 169, 97, 0.3);
}

.gallery-item img { 
    width: 100%; 
    height: 100%;
    object-fit: cover;
}

/* ===== SCHEDULE TABLE - White ===== */
#jadwal {
    background: #ffffff !important;
}

.schedule-table { 
    font-size: 0.95rem;
    border: 1px solid #e8eef5;
    box-shadow: 0 4px 20px rgba(30, 58, 95, 0.08);
    border-radius: 10px;
    overflow: hidden;
}

.schedule-table th, 
.schedule-table td { 
    padding: 1rem;
    vertical-align: middle;
}

.schedule-table thead {
    background: linear-gradient(135deg, #2c5282 0%, #4a7ba7 100%);
    color: white;
}

.schedule-table tbody tr:hover {
    background: #f8fafc;
}

/* ===== CTA SECTION - Blue Gradient ===== */
.bg-primary {
    background: linear-gradient(135deg, #2c5282 0%, #4a7ba7 100%) !important;
}

/* ===== FOOTER ===== */
footer { 
    background: #1a1a2e;
    color: #cfe0ff; 
    padding: 3rem 0;
    font-size: 0.95rem;
}

/* ===== SOCIAL MEDIA ===== */
.social-media {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.social-media a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #34495e;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 1.3rem;
}

.social-media a:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.social-instagram:hover { 
    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); 
}
.social-youtube:hover { background: #FF0000; }
.social-tiktok:hover { background: #000000; }
.social-whatsapp:hover { background: #25D366; }
.social-twitter:hover { background: #1DA1F2; }

/* ===== MOBILE RESPONSIVE ===== */
@media (max-width: 768px) {
    .hero-section {
        background-attachment: scroll;
    }
    
    .hero-stats {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.6rem;
    }
    
    #visi-misi .row {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    #fasilitas .row {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .facility-card {
        padding: 1.5rem 1rem;
    }
    
    .facility-icon {
        font-size: 2.5rem;
    }
}

@media (max-width: 576px) {
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
}
</style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <img src="assets/img/logo/logo.png" alt="logo" class="me-2 logo-img" onerror="this.style.display='none'" />
                <div>
                    <div class="brand-title">Volley Club</div>
                    <small class="brand-sub">PORPPAD</small>
                </div>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link active" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="#visi-misi">Visi & Misi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#prestasi">Prestasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team">Pelatih</a></li>
                    <li class="nav-item"><a class="nav-link" href="timpa.php">Tim</a></li>
                    <li class="nav-item"><a class="nav-link" href="#galeri">Galeri</a></li>
                    <li class="nav-item"><a class="nav-link" href="#jadwal">Jadwal</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item ms-2">
                                <a href="admin/dashboard.php" class="btn btn-primary btn-rounded">
                                    <i class="fa fa-dashboard"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item ms-1">
                            <a href="logout.php" class="btn btn-outline-danger btn-rounded">
                                <i class="fa fa-sign-out me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a href="index.php" class="btn btn-primary btn-rounded">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <header id="home" class="hero-section text-center text-light">
        <div class="hero-overlay"></div>
        <div class="container" data-aos="fade-up">
            <h1>CLUB BOLA VOLLY SURABAYA</h1>
            <h1 class="orbitron-title">PORPPAD</h1>
            
            <?php if (isLoggedIn()): ?>
                <p class="lead">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username']) ?></strong>! 🎉</p>
            <?php else: ?>
                <p class="lead">Bergabunglah dengan klub voli terbaik di Surabaya</p>
            <?php endif; ?>
            
            <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
                <a href="#about" class="btn btn-outline-light btn-rounded">Pelajari Lebih</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if ($is_member): ?>
                        <a href="timpa.php" class="btn btn-gold btn-rounded">
                            <i class="fa fa-users me-1"></i> Lihat Tim
                        </a>
                    <?php else: ?>
                        <button class="btn btn-gold btn-rounded" data-bs-toggle="modal" data-bs-target="#daftarModal">
                            <i class="fa fa-user-plus me-1"></i> Daftar Anggota
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-gold btn-rounded">Login / Gabung</a>
                <?php endif; ?>
            </div>
            
            <div class="hero-stats">
                <div class="stat" data-aos="zoom-in" data-aos-delay="100">
                    <h3><?= $total_members ?></h3>
                    <small>Anggota</small>
                </div>
                <div class="stat" data-aos="zoom-in" data-aos-delay="200">
                    <h3><?= $total_trophies ?></h3>
                    <small>Prestasi</small>
                </div>
                <div class="stat" data-aos="zoom-in" data-aos-delay="300">
                    <h3>2025</h3>
                    <small>Tahun</small>
                </div>
            </div>
        </div>
    </header>

    <!-- ABOUT -->
    <section id="about" style="background: linear-gradient(135deg, rgba(240, 248, 255, 0.8) 0%, rgba(230, 240, 250, 0.9) 100%);">
        <div class="container" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <img src="assets/img/logo/logo.png" class="img-fluid rounded shadow" alt="latihan" style="max-width: 300px; display: block; margin: 0 auto;" />
                </div>
                <div class="col-lg-6">
                    <h2 class="mb-3">Tentang Klub</h2>
                    <p class="text-muted" style="font-size: 1.05rem; line-height: 1.8;">Wadah pengembangan bakat voli dengan fokus pelatihan berkala, turnamen, dan pembentukan karakter atlet muda.</p>
                    <ul class="list-unstyled mt-3" style="font-size: 1rem;">
                        <li class="mb-2"><i class="fa fa-check-circle text-primary me-2"></i>Latihan 5x/minggu dengan pelatih bersertifikat</li>
                        <li class="mb-2"><i class="fa fa-check-circle text-primary me-2"></i>Pendaftaran & manajemen digital</li>
                        <li><i class="fa fa-check-circle text-primary me-2"></i>Program pembinaan usia junior</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- VISI & MISI -->
    <section id="visi-misi" style="background: white;">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>🎯 Visi & Misi</h3>
                <p>Arah dan tujuan Club Bola Voli PORPPAD</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="visi-misi-card">
                        <div class="vm-icon">🔭</div>
                        <h4>Visi</h4>
                        <p>Menjadi klub bola voli terkemuka di Surabaya yang menghasilkan atlet berprestasi, berkarakter, dan profesional dengan mengedepankan sportivitas, disiplin, dan kerja sama tim.</p>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left">
                    <div class="visi-misi-card">
                        <div class="vm-icon">🎯</div>
                        <h4>Misi</h4>
                        <ul class="vm-list">
                            <li>Menyelenggarakan pelatihan rutin dengan standar profesional</li>
                            <li>Mengikuti berbagai kompetisi tingkat lokal hingga nasional</li>
                            <li>Membentuk karakter atlet yang disiplin dan sportif</li>
                            <li>Membangun komunitas pecinta bola voli yang solid</li>
                            <li>Mengembangkan bakat atlet muda sejak dini</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FASILITAS -->
    <section id="fasilitas" style="background: linear-gradient(to bottom, #f8f9fa 0%, #e3f2fd 100%);">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>⚡ Fasilitas & Program</h3>
                <p>Apa yang kami tawarkan untuk anggota</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="0">
                    <div class="facility-card">
                        <div class="facility-icon">🏐</div>
                        <h5>Peralatan Lengkap</h5>
                        <p>Bola, net, dan equipment standar internasional untuk latihan optimal</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="facility-card">
                        <div class="facility-icon">👨‍🏫</div>
                        <h5>Pelatih Bersertifikat</h5>
                        <p>Tim pelatih berpengalaman dengan sertifikasi nasional dan internasional</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="facility-card">
                        <div class="facility-icon">📅</div>
                        <h5>Jadwal Teratur</h5>
                        <p>Latihan 5x seminggu dengan program terstruktur dan terukur</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="facility-card">
                        <div class="facility-icon">🏆</div>
                        <h5>Program Kompetisi</h5>
                        <p>Kesempatan mengikuti turnamen lokal hingga nasional</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                    <div class="facility-card">
                        <div class="facility-icon">💪</div>
                        <h5>Physical Training</h5>
                        <p>Program latihan fisik dan conditioning untuk performa maksimal</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="500">
                    <div class="facility-card">
                        <div class="facility-icon">👥</div>
                        <h5>Komunitas Solid</h5>
                        <p>Bergabung dengan keluarga besar pecinta voli yang suportif</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRESTASI -->
    <section id="prestasi" class="bg-light">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>🏆 Prestasi & Turnamen</h3>
                <p>Pencapaian gemilang klub kami</p>
            </div>
            
            <div class="row g-4">
                <?php if ($trophies_list->num_rows > 0):
                    $delay = 0;
                    while ($trophy = $trophies_list->fetch_assoc()): ?>
                        <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                            <div class="trophy-card">
                                <div class="trophy-img">
                                    <?php if ($trophy['foto'] && file_exists("uploads/trophies/" . $trophy['foto'])): ?>
                                        <img src="uploads/trophies/<?= htmlspecialchars($trophy['foto']) ?>" alt="<?= htmlspecialchars($trophy['judul']) ?>" />
                                    <?php else: ?>
                                        🏆
                                    <?php endif; ?>
                                </div>
                                <div class="trophy-content">
                                    <span class="trophy-badge badge-<?= strtolower($trophy['badge']) ?>">
                                        <?= htmlspecialchars($trophy['badge']) ?>
                                    </span>
                                    <div class="trophy-title"><?= htmlspecialchars($trophy['judul']) ?></div>
                                    <div class="trophy-desc"><?= htmlspecialchars(substr($trophy['deskripsi'], 0, 60)) . '...' ?></div>
                                    <div class="trophy-date">📅 <?= htmlspecialchars($trophy['tanggal']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $delay += 50;
                    endwhile;
                else: ?>
                    <div class="col-12 text-center py-4">
                        <p class="text-muted">Belum ada prestasi yang ditambahkan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- PELATIH -->
    <section id="team">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>👨‍🏫 Pelatih PORPPAD</h3>
                <p>Tim pelatih profesional kami</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-6 col-md-4" data-aos="zoom-in">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih2.png" alt="Om Agus" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-3">
                            <h5>Om Agus</h5>
                            <small class="text-light">Ketua & Pelatih</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih2.png" alt="Om Nasir" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-3">
                            <h5>Om Nasir</h5>
                            <small class="text-light">Pelatih</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih2.png" alt="Anton" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-3">
                            <h5>Anton Wijaya</h5>
                            <small class="text-light">Asisten Pelatih</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- GALLERY -->
    <section id="galeri" class="bg-light">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>📸 Galeri</h3>
                <p>Momen latihan & pertandingan</p>
            </div>
            
            <div class="gallery-grid" data-aos="fade-up" id="galleryGrid"></div>
            
            <div class="text-center mt-4" id="loadMoreContainer" style="display: none;">
                <button class="btn btn-primary btn-rounded" id="loadMoreBtn" onclick="loadMorePhotos()">
                    <i class="fa fa-images me-2"></i>Lihat Lebih Banyak
                </button>
            </div>
            
            <div class="text-center mt-4" id="loadingIndicator" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </section>

    <!-- JADWAL -->
    <section id="jadwal">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>📅 Jadwal Latihan</h3>
                <p>Jadwal rutin latihan mingguan</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10" data-aos="fade-up">
                    <div class="table-responsive">
                        <table class="schedule-table table table-bordered mb-0 text-center">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Waktu</th>
                                    <th>Materi</th>
                                    <th>Tempat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Selasa</strong></td>
                                    <td>19:00-22:00</td>
                                    <td>Fisik & Teknik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                                <tr>
                                    <td><strong>Rabu</strong></td>
                                    <td>18:00-22:00</td>
                                    <td>Drill & Game</td>
                                    <td>GOR FASHA</td>
                                </tr>
                                <tr>
                                    <td><strong>Kamis</strong></td>
                                    <td>19:00-22:00</td>
                                    <td>Fisik & Teknik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumat</strong></td>
                                    <td>18:00-22:00</td>
                                    <td>Drill & Game</td>
                                    <td>GOR FASHA</td>
                                </tr>
                                <tr>
                                    <td><strong>Sabtu</strong></td>
                                    <td>14:30-Selesai</td>
                                    <td>Drill & Game</td>
                                    <td>Lap.Al-Irsyad</td>
                                </tr>
                                <tr>
                                    <td><strong>Minggu</strong></td>
                                    <td>Tentatif</td>
                                    <td>Fisik & Teknik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5 bg-primary text-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 text-center text-md-start" data-aos="fade-right">
                    <h4 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Gabung dengan kami!</h4>
                    <p class="mb-0" style="font-size: 1rem;">Hubungi admin untuk info latihan dan event</p>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0" data-aos="fade-left">
                    <?php if (isLoggedIn() && !$is_member): ?>
                        <button class="btn btn-outline-light btn-rounded" data-bs-toggle="modal" data-bs-target="#daftarModal">
                            <i class="fa fa-user-plus me-1"></i> Daftar Anggota
                        </button>
                    <?php else: ?>
                        <a href="<?= isLoggedIn() ? 'timpa.php' : 'login.php' ?>" class="btn btn-outline-light btn-rounded">
                            <i class="fa fa-<?= isLoggedIn() ? 'users' : 'user-plus' ?> me-1"></i> <?= isLoggedIn() ? 'Lihat Tim' : 'Login / Gabung' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container"> 
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="fw-bold">Volley Club PORPPAD</h5>
                    <p class="mb-2" style="font-size: 0.9rem; line-height: 1.6;">
                        GOR FASHA – Surabaya<br>
                        📞 Telp: 0812-3456-7890<br>
                        📧 Email: clubvolley@gmail.com
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold mb-3">Ikuti Kami</h6>
                    <div class="social-media">
                        <a href="https://instagram.com" target="_blank" class="social-instagram" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="social-youtube" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://tiktok.com" target="_blank" class="social-tiktok" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="https://wa.me/6281234567890" target="_blank" class="social-whatsapp" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com" target="_blank" class="social-twitter" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <small style="font-size: 0.85rem; color: #95a5a6;">
                    © 2025 Volley Club PORPPAD. All rights reserved.
                </small>
            </div>
        </div>
    </footer>

    <!-- MODAL DAFTAR ANGGOTA -->
    <div class="modal fade" id="daftarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-user-plus me-2"></i>Form Pendaftaran Anggota
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form id="formDaftar" action="proses_daftar.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info" style="font-size: 0.9rem;">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Setelah submit, data Anda akan dikirim ke admin via Telegram untuk diverifikasi.
                        </div>

                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold">Foto Profil <span class="text-danger">*</span></label>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border: 2px dashed #dee2e6;">
                                <div class="mb-3">
                                    <img id="photoPreview" 
                                         src="assets/img/default-profile.jpg" 
                                         alt="Preview" 
                                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd;"
                                         onerror="this.src='https://via.placeholder.com/150'">
                                </div>
                                
                                <input type="file" 
                                       name="foto" 
                                       id="fotoInput" 
                                       class="form-control" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif"
                                       required
                                       style="max-width: 400px; margin: 0 auto;">
                                
                                <small class="text-muted d-block mt-2">
                                    <i class="fa fa-info-circle"></i> Format: JPG, PNG, GIF (Max: 2MB)
                                </small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap sesuai KTP" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" name="tempat_lahir" class="form-control" placeholder="Surabaya" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control" id="tanggal_lahir_modal" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Umur <span class="text-danger">*</span></label>
                                <input type="number" name="umur" class="form-control" id="umur_modal" min="10" max="60" placeholder="25" required readonly>
                                <small class="text-muted">Otomatis dari tanggal lahir</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Pilih Gender</option>
                                    <option value="Putra">Putra</option>
                                    <option value="Putri">Putri</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Posisi <span class="text-danger">*</span></label>
                                <select name="posisi" class="form-select" required>
                                    <option value="">Pilih Posisi</option>
                                    <option value="Spiker">Spiker</option>
                                    <option value="Setter">Setter</option>
                                    <option value="Libero">Libero</option>
                                    <option value="Blocker">Blocker</option>
                                    <option value="Server">Server</option>
                                    <option value="All-rounder">All-rounder</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="wa" class="form-control" placeholder="08xxxxxxxxxx" pattern="[0-9]{10,13}" required>
                                <small class="text-muted">Format: 08xxxxxxxxxx</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                <input type="text" name="alamat" class="form-control" placeholder="Kota, Kecamatan" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Alasan Ingin Bergabung <span class="text-danger">*</span></label>
                                <textarea name="alasan" class="form-control" rows="3" placeholder="Ceritakan alasan Anda..." required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Pengalaman Bermain Voli</label>
                                <textarea name="pengalaman" class="form-control" rows="2" placeholder="Ceritakan pengalaman Anda (opsional)"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Riwayat Cedera (Opsional)</label>
                                <textarea name="riwayat_cedera" class="form-control" rows="2" placeholder="Riwayat cedera yang perlu kami ketahui?"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa fa-times me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane me-1"></i> Kirim Pendaftaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({ 
            once: true, 
            duration: 600,
            easing: 'ease-out'
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offsetTop = target.offsetTop - 70;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto calculate age
        document.getElementById('tanggal_lahir_modal')?.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            document.getElementById('umur_modal').value = age;
        });

        // Preview Photo
        document.getElementById('fotoInput')?.addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('photoPreview');
            
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('⚠️ Ukuran foto terlalu besar! Maksimal 2MB');
                    this.value = '';
                    preview.src = 'assets/img/default-profile.jpg';
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('⚠️ Format tidak valid! Gunakan JPG, PNG, atau GIF');
                    this.value = '';
                    preview.src = 'assets/img/default-profile.jpg';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Gallery System
        let currentPhotoIndex = 5;
        let allPhotos = [];
        let isLoading = false;

        async function fetchGalleryPhotos() {
            try {
                const response = await fetch('get_gallery.php');
                const data = await response.json();
                
                if (data.success) {
                    allPhotos = data.photos;
                    renderInitialPhotos();
                    
                    if (allPhotos.length > 5) {
                        document.getElementById('loadMoreContainer').style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error fetching photos:', error);
            }
        }

        function renderInitialPhotos() {
            const grid = document.getElementById('galleryGrid');
            grid.innerHTML = '';
            
            const initialPhotos = allPhotos.slice(0, 5);
            initialPhotos.forEach(photo => {
                grid.appendChild(createPhotoElement(photo));
            });
        }

        function loadMorePhotos() {
            if (isLoading) return;
            
            isLoading = true;
            const loadingIndicator = document.getElementById('loadingIndicator');
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            
            loadingIndicator.style.display = 'block';
            loadMoreBtn.style.display = 'none';
            
            setTimeout(() => {
                const grid = document.getElementById('galleryGrid');
                const nextPhotos = allPhotos.slice(currentPhotoIndex, currentPhotoIndex + 5);
                
                nextPhotos.forEach((photo, index) => {
                    setTimeout(() => {
                        const photoElement = createPhotoElement(photo);
                        photoElement.style.opacity = '0';
                        grid.appendChild(photoElement);
                        
                        setTimeout(() => {
                            photoElement.style.transition = 'opacity 0.5s';
                            photoElement.style.opacity = '1';
                        }, 50);
                    }, index * 100);
                });
                
                currentPhotoIndex += 5;
                
                loadingIndicator.style.display = 'none';
                
                if (currentPhotoIndex < allPhotos.length) {
                    loadMoreBtn.style.display = 'inline-block';
                } else {
                    document.getElementById('loadMoreContainer').innerHTML = 
                        '<p class="text-muted">Semua foto sudah ditampilkan 🎉</p>';
                }
                
                isLoading = false;
            }, 500);
        }

        function createPhotoElement(photo) {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            div.innerHTML = `
                <img src="uploads/gallery/${photo.filename}" 
                     alt="${photo.caption || 'Gallery'}" 
                     onerror="this.src='https://via.placeholder.com/300'" 
                     onclick="openPhotoModal('${photo.filename}', '${photo.caption || ''}')" />
            `;
            return div;
        }

        function openPhotoModal(filename, caption) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                padding: 2rem;
            `;
            
            modal.innerHTML = `
                <div style="max-width: 90%; max-height: 90%; text-align: center;">
                    <img src="uploads/gallery/${filename}" 
                         style="max-width: 100%; max-height: 80vh; border-radius: 10px;" 
                         alt="${caption}" />
                    ${caption ? `<p style="color: white; margin-top: 1rem; font-size: 1.1rem;">${caption}</p>` : ''}
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">
                        Tutup
                    </button>
                </div>
            `;
            
            modal.onclick = (e) => {
                if (e.target === modal) modal.remove();
            };
            
            document.body.appendChild(modal);
        }

        document.addEventListener('DOMContentLoaded', fetchGalleryPhotos);
    </script>
</body>
</html>