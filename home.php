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
 <meta name="viewport" content="width=1200">
    <title>Volley Club – PORPPAD Surabaya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&display=swap" rel="stylesheet" />
    
    <style>
 /* ===== RESET & BASE ===== */
:root {
    --primary: #0b3d91;
    --gold: #d4af37;
}

* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}


body { 
    min-width: 1200px; /* Force desktop width */

    font-family: 'Poppins', sans-serif; 
    padding-top: 70px;
    overflow-x: hidden;
    min-width: 320px; /* Minimum width untuk prevent breaking */
}

/* ===== NAVBAR - RESPONSIVE ===== */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: rgba(163, 192, 219, 0.98);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    z-index: 1000;
    padding: 0.6rem 0;
}

.logo-img { 
    width: 40px; 
    height: 40px; 
    border-radius: 8px;
    object-fit: cover;
}

.brand-title { 
    font-weight: 700; 
    font-size: 1.1rem; 
    color: var(--primary); 
    line-height: 1.2;
}

.brand-sub { 
    font-size: 0.75rem; 
    color: #6c757d; 
    line-height: 1;
}

.nav-link {
    font-size: 0.9rem;
    padding: 0.5rem 0.8rem !important;
    position: relative;
    color: #333;
    font-weight: 500;
    transition: color 0.3s;
    white-space: nowrap; /* Prevent text wrap */
}

.nav-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 0;
    height: 2px;
    background: var(--primary);
    transition: width 0.3s;
}

.nav-link:hover { color: var(--primary); }
.nav-link:hover::after, .nav-link.active::after { width: 100%; }

.btn-rounded { 
    border-radius: 20px; 
    padding: 0.4rem 1rem; 
    font-size: 0.85rem;
    font-weight: 500;
    white-space: nowrap;
}

.btn-gold { 
    background: linear-gradient(90deg, var(--gold), #f0c96b); 
    color: #111; 
    border: none;
}

/* ===== HERO SECTION - FULLY RESPONSIVE ===== */
/* ===== HERO SECTION - FULLY RESPONSIVE ===== */
.hero-section {
    min-height: 90vh;
    display: flex;
    align-items: center;
    position: relative;
    padding: 3rem 0 2rem;
    width: 100%;
    
    /* Background DEFAULT (Desktop) */
    background: url('assets/img/bg/bg22nya.png') no-repeat center center;
    background-size: cover;
    background-position: center center;
    background-attachment: fixed;
}


.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.4), rgba(0,0,0,0.2));
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.4), rgba(0,0,0,0.2));
}

.hero-section .container { 
    position: relative; 
    z-index: 2; 
}

.hero-section h1 { 
    font-size: clamp(1.2rem, 4vw, 2rem); /* Responsive font size */
    color: #fff; 
    text-shadow: 0 4px 15px rgba(0,0,0,0.3);
    margin-bottom: 0.5rem;
}

.hero-section .orbitron-title {
    font-family: 'Orbitron', sans-serif;
    font-size: clamp(1.8rem, 6vw, 3rem);
    color: #fff;
    -webkit-text-stroke: 1.5px #667eea;
    text-stroke: 1.5px #667eea;
    font-weight: 900;
    letter-spacing: 2px;
    margin-bottom: 1rem;
}

.hero-section .lead { 
    font-size: clamp(0.85rem, 2.5vw, 1rem); 
    color: #f6f6f6;
    margin-bottom: 1.5rem;
}

.hero-stats { 
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap; /* Allow wrapping on very small screens */
}

.hero-stats .stat {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 1rem 1.5rem;
    border-radius: 12px;
    min-width: 100px;
    text-align: center;
}

.hero-stats .stat h3 { 
    font-size: clamp(1.5rem, 4vw, 2rem); 
    margin: 0; 
    color: #fff;
    font-weight: 700;
}

.hero-stats small { 
    font-size: 0.8rem; 
    color: #f1f1f1;
}

/* ===== SECTIONS - RESPONSIVE PADDING ===== */
section { 
    padding: clamp(2rem, 5vw, 4rem) 0; 
}

section h2, section h3 { 
    font-size: clamp(1.3rem, 3.5vw, 1.8rem); 
    color: var(--primary); 
    margin-bottom: 1.5rem;
    font-weight: 700;
}

section p { 
    font-size: clamp(0.85rem, 2vw, 1rem); 
    line-height: 1.6; 
}

.section-title {
    text-align: center;
    margin-bottom: 2rem;
}

.section-title h3 {
    font-size: clamp(1.4rem, 4vw, 2rem);
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.section-title p {
    font-size: clamp(0.8rem, 2vw, 0.95rem);
    color: #6c757d;
}

/* ===== TROPHY CARDS - RESPONSIVE GRID ===== */
.trophy-card {
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.trophy-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.trophy-img {
    width: 100%;
    height: clamp(150px, 30vw, 200px); /* Responsive height */
    object-fit: cover;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(2rem, 5vw, 3.5rem);
    color: white;
}

.trophy-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.trophy-content {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.trophy-badge {
    display: inline-block;
    padding: 0.25rem 0.7rem;
    border-radius: 15px;
    font-size: clamp(0.65rem, 1.5vw, 0.75rem);
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
    align-self: flex-start;
}

.badge-prestasi { background: #ffeaa7; color: #d63031; }
.badge-turnamen { background: #74b9ff; color: #0984e3; }
.badge-kejuaraan { background: #a29bfe; color: #6c5ce7; }
.badge-penghargaan { background: #fd79a8; color: #e84393; }

.trophy-title {
    font-size: clamp(0.9rem, 2.2vw, 1rem);
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.trophy-desc {
    font-size: clamp(0.75rem, 1.8vw, 0.85rem);
    color: #7f8c8d;
    margin-bottom: 0.5rem;
    line-height: 1.5;
    flex: 1;
}

.trophy-date {
    font-size: clamp(0.7rem, 1.5vw, 0.8rem);
    color: #95a5a6;
}

/* ===== TEAM CARDS - RESPONSIVE ===== */
.team-card {
    color: white;
    border-radius: 10px;
    overflow: hidden;
    background: #9e93f2ff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.team-photo {
    height: clamp(140px, 30vw, 180px);
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
    font-size: clamp(0.9rem, 2vw, 1rem);
    font-weight: 600;
    margin-bottom: 0.3rem;
}

.team-card small { 
    font-size: clamp(0.7rem, 1.5vw, 0.85rem); 
}

/* ===== GALLERY - RESPONSIVE GRID ===== */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(clamp(140px, 40vw, 200px), 1fr));
    gap: clamp(0.5rem, 2vw, 1rem);
}

.gallery-item {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s;
    aspect-ratio: 1;
}

.gallery-item:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.gallery-item img { 
    width: 100%; 
    height: 100%;
    object-fit: cover;
    display: block;
}

/* ===== SCHEDULE TABLE - RESPONSIVE ===== */
.schedule-table { 
    font-size: clamp(0.7rem, 1.8vw, 0.85rem);
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-radius: 10px;
    overflow: hidden;
    width: 100%;
}

.schedule-table th, 
.schedule-table td { 
    padding: clamp(0.5rem, 2vw, 0.8rem);
    vertical-align: middle;
}

.schedule-table thead {
    background: var(--primary);
    color: white;
}

.schedule-table tbody tr:hover {
    background: #f8f9fa;
}

/* ===== SOCIAL MEDIA - RESPONSIVE ===== */
.social-media {
    display: flex;
    gap: 0.8rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.social-media a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: clamp(40px, 10vw, 50px);
    height: clamp(40px, 10vw, 50px);
    border-radius: 50%;
    background: #34495e;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
    font-size: clamp(1rem, 2.5vw, 1.3rem);
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

/* ===== FOOTER - RESPONSIVE ===== */
footer { 
    background: #1a1a2e;
    color: #cfe0ff; 
    padding: clamp(1.5rem, 3vw, 2.5rem) 0;
    font-size: clamp(0.75rem, 1.8vw, 0.9rem);
}

footer h5 {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    margin-bottom: 0.8rem;
}

/* ===== MODAL - RESPONSIVE ===== */
.modal-content {
    border-radius: 15px;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
}

.form-label {
    font-size: clamp(0.8rem, 2vw, 0.9rem);
    font-weight: 600;
    color: #2c3e50;
}

.form-control, .form-select {
    font-size: clamp(0.8rem, 2vw, 0.9rem);
    border-radius: 8px;
}

/* ===== RESPONSIVE BREAKPOINTS ===== */

/* Extra Small Devices (phones, less than 576px) */
@media (max-width: 575.98px) {
    body { padding-top: 60px; }
    
    .navbar { padding: 0.4rem 0; }
    
    .navbar-brand .logo-img { width: 32px; height: 32px; }
    
    .brand-title { font-size: 0.9rem; }
    .brand-sub { font-size: 0.65rem; }
    
    .nav-link { 
        font-size: 0.85rem; 
        padding: 0.4rem 0.6rem !important; 
    }
    
    .btn-rounded { 
        font-size: 0.75rem; 
        padding: 0.35rem 0.8rem; 
    }
    
    .hero-section { 
        min-height: 85vh; 
        padding: 3rem 0 2rem; 
    }
    
    .hero-stats { 
        gap: 0.6rem; 
        margin-top: 1.5rem;
    }
    
    .hero-stats .stat { 
        padding: 0.7rem 1rem; 
        min-width: 80px; 
    }
    
    .gallery-grid { 
        grid-template-columns: repeat(2, 1fr); /* 2 kolom di mobile */
        gap: 0.5rem;
    }
    
    .schedule-table {
        font-size: 0.65rem;
    }
    
    .schedule-table th,
    .schedule-table td {
        padding: 0.4rem 0.3rem;
    }
    
    /* Stack buttons vertically on very small screens */
    .hero-section .d-flex.gap-2 {
        flex-direction: column;
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
    }
    
    .hero-section .btn {
        width: 100%;
    }
}

/* Small Devices (landscape phones, 576px and up) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .gallery-grid { 
        grid-template-columns: repeat(3, 1fr); /* 3 kolom di tablet portrait */
    }
}

/* Medium Devices (tablets, 768px and up) */
@media (min-width: 768px) and (max-width: 991.98px) {
    .gallery-grid { 
        grid-template-columns: repeat(4, 1fr); /* 4 kolom di tablet landscape */
    }
    
    .hero-stats .stat {
        min-width: 110px;
    }
}

/* Large Devices (desktops, 992px and up) */
@media (min-width: 992px) {
    .gallery-grid { 
        grid-template-columns: repeat(5, 1fr); /* 5 kolom di desktop */
    }
}

/* ===== PREVENT HORIZONTAL SCROLL ===== */
.container,
.container-fluid {
    max-width: 100%;
    overflow-x: hidden;
}

.row {
    margin-left: 0;
    margin-right: 0;
}

/* ===== SMOOTH SCROLL ===== */
html {
    scroll-behavior: smooth;
}

/* ===== LOADING OPTIMIZATION ===== */
img {
    max-width: 100%;
    height: auto;
    display: block;
}

/* ===== ACCESSIBILITY ===== */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
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
            
            <div class="d-flex justify-content-center gap-2 mb-3">
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
            <div class="row align-items-center g-3">
                <div class="col-lg-6">
                    <img src="assets/img/logo/logo.png" class="img-fluid rounded shadow" alt="latihan" style="border-radius: 8px;" width="50%" />
                </div>
                <div class="col-lg-6">
                    <h2>Tentang Klub</h2>
                    <p class="text-muted">Wadah pengembangan bakat voli dengan fokus pelatihan berkala, turnamen, dan pembentukan karakter atlet muda.</p>
                    <ul class="list-unstyled" style="font-size: 0.85rem;">
                        <li class="mb-2"><i class="fa fa-check-circle text-primary me-2"></i>Latihan 5x/minggu dengan pelatih bersertifikat</li>
                        <li class="mb-2"><i class="fa fa-check-circle text-primary me-2"></i>Pendaftaran & manajemen digital</li>
                        <li><i class="fa fa-check-circle text-primary me-2"></i>Program pembinaan usia junior</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- PRESTASI -->
    <section id="prestasi" style="background: linear-gradient(to bottom, #f8f9fa 0%, #e3f2fd 100%);">

    <!-- PRESTASI -->
    <section id="prestasi" class="bg-light">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>🏆 Prestasi & Turnamen</h3>
                <p>Pencapaian gemilang klub kami</p>
            </div>
            
            <div class="row g-3">
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

    <!-- PELATIH-->
    <section id="team">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>👨‍🏫 Pelatih PORPPAD</h3>
                <p>Tim pelatih profesional kami</p>
            </div>
            
            <div class="row g-3">
                <div class="col-6 col-md-4" data-aos="zoom-in" >
                    <div class="team-card" >
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih2.png" alt="Om Agus" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-2">
                            <h5>Om Agus</h5>
                            <small class="text-muted">Ketua & Pelatih</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih2.png" alt="Om Nasir" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-2">
                            <h5>Om Nasir</h5>
                            <small class="text-muted">Pelatih</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih2.png" alt="Anton" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-2">
                            <h5>Anton Wijaya</h5>
                            <small class="text-muted">Libero</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

   <!-- GALLERY SECTION - UPDATE BAGIAN INI DI index.php -->
<section id="galeri" class="bg-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h3>📸 Galeri</h3>
            <p>Momen latihan & pertandingan</p>
        </div>
        
        <!-- Gallery Grid -->
        <div class="gallery-grid" data-aos="fade-up" id="galleryGrid">
            <!-- 5 foto pertama akan ditampilkan -->
        </div>
        
        <!-- Load More Button -->
        <div class="text-center mt-4" id="loadMoreContainer" style="display: none;">
            <button class="btn btn-primary btn-rounded" id="loadMoreBtn" onclick="loadMorePhotos()">
                <i class="fa fa-images me-2"></i>Lihat Lebih Banyak
            </button>
        </div>
        
        <!-- Loading Indicator -->
        <div class="text-center mt-4" id="loadingIndicator" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</section>

<script>
// Gallery Load More System
let currentPhotoIndex = 5; // Mulai dari foto ke-6
let allPhotos = [];
let isLoading = false;

// Fetch all photos dari database
async function fetchGalleryPhotos() {
    try {
        const response = await fetch('get_gallery.php');
        const data = await response.json();
        
        if (data.success) {
            allPhotos = data.photos;
            renderInitialPhotos();
            
            // Show/hide load more button
            if (allPhotos.length > 5) {
                document.getElementById('loadMoreContainer').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error fetching photos:', error);
    }
}

// Render 5 foto pertama
function renderInitialPhotos() {
    const grid = document.getElementById('galleryGrid');
    grid.innerHTML = '';
    
    const initialPhotos = allPhotos.slice(0, 5);
    initialPhotos.forEach(photo => {
        grid.appendChild(createPhotoElement(photo));
    });
}

// Load more photos (5 foto setiap kali klik)
function loadMorePhotos() {
    if (isLoading) return;
    
    isLoading = true;
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    
    // Show loading
    loadingIndicator.style.display = 'block';
    loadMoreBtn.style.display = 'none';
    
    // Simulate loading delay (optional)
    setTimeout(() => {
        const grid = document.getElementById('galleryGrid');
        const nextPhotos = allPhotos.slice(currentPhotoIndex, currentPhotoIndex + 5);
        
        // Add new photos with animation
        nextPhotos.forEach((photo, index) => {
            setTimeout(() => {
                const photoElement = createPhotoElement(photo);
                photoElement.style.opacity = '0';
                grid.appendChild(photoElement);
                
                // Fade in animation
                setTimeout(() => {
                    photoElement.style.transition = 'opacity 0.5s';
                    photoElement.style.opacity = '1';
                }, 50);
            }, index * 100); // Staggered animation
        });
        
        currentPhotoIndex += 5;
        
        // Hide loading
        loadingIndicator.style.display = 'none';
        
        // Show/hide load more button
        if (currentPhotoIndex < allPhotos.length) {
            loadMoreBtn.style.display = 'inline-block';
        } else {
            document.getElementById('loadMoreContainer').innerHTML = 
                '<p class="text-muted">Semua foto sudah ditampilkan 🎉</p>';
        }
        
        isLoading = false;
    }, 500);
}

// Create photo element
function createPhotoElement(photo) {
    const div = document.createElement('div');
    div.className = 'gallery-item';
    div.innerHTML = `
        <img src="uploads/gallery/${photo.filename}" 
             alt="${photo.caption || 'Gallery'}" 
             onerror="this.src='https://via.placeholder.com/300'" 
             onclick="openPhotoModal('${photo.filename}', '${photo.caption || ''}')" 
             style="cursor: pointer;" />
    `;
    return div;
}

// Open photo in modal
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
                    style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: white; border: none; border-radius: 5px; cursor: pointer;">
                Tutup
            </button>
        </div>
    `;
    
    modal.onclick = (e) => {
        if (e.target === modal) modal.remove();
    };
    
    document.body.appendChild(modal);
}

// Initialize gallery on page load
document.addEventListener('DOMContentLoaded', fetchGalleryPhotos);
</script>
    <!-- JADWAL -->
    <section id="jadwal">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>📅 Jadwal Latihan</h3>
                <p>Jadwal rutin latihan mingguan</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
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
                                    <td>Fisik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                                <tr>
                                    <td><strong>Rabu</strong></td>
                                    <td>18:00-22:00</td>
                                    <td>Teknik</td>
                                    <td>GOR FASHA</td>
                                </tr>
                                <tr>
                                    <td><strong>Kamis</strong></td>
                                    <td>19:00-22:00</td>
                                    <td>Fisik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumat</strong></td>
                                    <td>18:00-22:00</td>
                                    <td>Teknik</td>
                                    <td>GOR FASHA</td>
                                </tr>
                                <tr>
                                    <td><strong>Sabtu</strong></td>
                                    <td><table>Tentatif</table></td>
                                    <td>Fisik</td>
                                    <td>Lap.Al-Irsyad</td>
                                </tr>
                                <tr>
                                    <td><strong>Minggu</strong></td>
                                    <td>08:00-Selesai</td>
                                    <td>Fisik</td>
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
    <section class="py-4 bg-primary text-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8" data-aos="fade-right">
                    <h4 style="font-size: 1.2rem; margin-bottom: 0.3rem;">Gabung dengan kami!</h4>
                    <p class="mb-0" style="font-size: 0.8rem;">Hubungi admin untuk info latihan dan event</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0" data-aos="fade-left">
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
                    <p class="mb-2" style="font-size: 0.75rem; line-height: 1.6;">
                        GOR FASHA – Surabaya<br>
                        📞 Telp: 0812-3456-7890<br>
                        📧 Email: clubvolley@gmail.com
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold mb-2" style="font-size: 0.9rem;">Ikuti Kami</h6>
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
                <small style="font-size: 0.7rem; color: #95a5a6;">
                    © 2025 Volley Club PORPPAD. All rights reserved.
                </small>
            </div>
        </div>
    </footer>

    <!-- MODAL DAFTAR ANGGOTA -->
 <!-- MODAL DAFTAR ANGGOTA - WITH PHOTO UPLOAD -->
<div class="modal fade" id="daftarModal" tabindex="-1" aria-labelledby="daftarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="daftarModalLabel">
                    <i class="fa fa-user-plus me-2"></i>Form Pendaftaran Anggota
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- PENTING: Tambahkan enctype untuk upload -->
            <form id="formDaftar" action="proses_daftar.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info" style="font-size: 0.85rem;">
                        <i class="fa fa-info-circle me-2"></i>
                        <strong>Informasi:</strong> Setelah submit, data Anda akan dikirim ke admin via Telegram untuk diverifikasi.
                    </div>

                    <!-- FOTO UPLOAD - BARU! -->
                    <div class="mb-4 text-center">
                        <label class="form-label fw-bold">Foto Profil <span class="text-danger">*</span></label>
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border: 2px dashed #dee2e6;">
                            <!-- Preview -->
                            <div class="mb-3">
                                <img id="photoPreview" 
                                     src="assets/img/default-profile.jpg" 
                                     alt="Preview" 
                                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd;"
                                     onerror="this.src='https://via.placeholder.com/150'">
                            </div>
                            
                            <!-- Input File -->
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


<script>
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
        // Validasi ukuran (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('⚠️ Ukuran foto terlalu besar! Maksimal 2MB');
            this.value = '';
            preview.src = 'assets/img/default-profile.jpg';
            return;
        }
        
        // Validasi tipe
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('⚠️ Format tidak valid! Gunakan JPG, PNG, atau GIF');
            this.value = '';
            preview.src = 'assets/img/default-profile.jpg';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Validasi sebelum submit
document.getElementById('formDaftar')?.addEventListener('submit', function(e) {
    const fotoInput = document.getElementById('fotoInput');
    
    if (!fotoInput.files || fotoInput.files.length === 0) {
        e.preventDefault();
        alert('⚠️ Foto profil wajib diupload!');
        fotoInput.focus();
        return false;
    }
});
</script>
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

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offsetTop = target.offsetTop - 60;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Navbar active state on scroll
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const scrollY = window.pageYOffset + 100;

            sections.forEach(section => {
                const sectionHeight = section.offsetHeight;
                const sectionTop = section.offsetTop;
                const sectionId = section.getAttribute('id');
                
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        });

        // Auto calculate age from date of birth
        document.querySelector('input[name="tanggal_lahir"]')?.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            document.querySelector('input[name="umur"]').value = age;
        });
    </script>
    <script>
// Auto calculate age

</body>
</html>