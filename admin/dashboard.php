<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get statistics
$total_members = $conn->query("SELECT COUNT(*) as total FROM members WHERE status='approved'")->fetch_assoc()['total'];
$total_trophies = $conn->query("SELECT COUNT(*) as total FROM trophies")->fetch_assoc()['total'];

// Kas statistics
$total_pemasukan = $conn->query("SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pemasukan'")->fetch_assoc()['total'] ?? 0;
$total_pengeluaran = $conn->query("SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pengeluaran'")->fetch_assoc()['total'] ?? 0;
$saldo = $total_pemasukan - $total_pengeluaran;

// Recent members
$recent_members = $conn->query("SELECT * FROM members WHERE status='approved' ORDER BY created_at DESC LIMIT 5");

// Recent transactions
$recent_kas = $conn->query("SELECT * FROM kas ORDER BY tanggal DESC LIMIT 5");

// Recent trophies
$recent_trophies = $conn->query("SELECT * FROM trophies ORDER BY id DESC LIMIT 3");

// Position statistics
$position_stats = $conn->query("SELECT posisi, COUNT(*) as jumlah FROM members WHERE status='approved' GROUP BY posisi");

// Pending count for notification
$pending_count = $conn->query("SELECT COUNT(*) as total FROM members WHERE status = 'pending'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel PORPPAD</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f7fa;
        line-height: 1.6;
    }
    
    /* ===== NAVBAR - PROFESSIONAL ===== */
    .navbar {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 1.2rem 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .navbar-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .navbar h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .navbar nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .navbar nav a {
        color: white;
        text-decoration: none;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        transition: all 0.3s;
        font-size: 1rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
    }
    
    .navbar nav a:hover {
        background: rgba(255,255,255,0.15);
        transform: translateY(-2px);
    }
    
    .navbar nav a.active {
        background: rgba(255,255,255,0.2);
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .badge-notification {
        background: #e74c3c;
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.25rem;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }
    
    /* ===== WELCOME SECTION ===== */
    .welcome {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2.5rem;
        border-radius: 15px;
        margin-bottom: 2.5rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .welcome h1 {
        font-size: 2.25rem;
        margin-bottom: 0.75rem;
        font-weight: 700;
    }
    
    .welcome p {
        opacity: 0.95;
        font-size: 1.1rem;
    }
    
    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.75rem;
        margin-bottom: 2.5rem;
    }
    
    .stat-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: all 0.3s;
        border-left: 5px solid;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.25rem;
        flex-shrink: 0;
    }
    
    .stat-card.members {
        border-left-color: #667eea;
    }
    
    .stat-card.members .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .stat-card.kas {
        border-left-color: #f093fb;
    }
    
    .stat-card.kas .stat-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .stat-card.trophies {
        border-left-color: #4facfe;
    }
    
    .stat-card.trophies .stat-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .stat-card.saldo {
        border-left-color: #43e97b;
    }
    
    .stat-card.saldo .stat-icon {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    
    .stat-info h3 {
        color: #7f8c8d;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-info .number {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    /* ===== CONTENT GRID ===== */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2.5rem;
    }
    
    .card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.75rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .card-header h3 {
        color: #2c3e50;
        font-size: 1.4rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .card-header a {
        color: #3498db;
        text-decoration: none;
        font-size: 1rem;
        transition: color 0.3s;
        font-weight: 500;
    }
    
    .card-header a:hover {
        color: #2980b9;
    }
    
    .export-btn {
        background: #27ae60;
        color: white !important;
        padding: 0.65rem 1.25rem;
        border-radius: 8px;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
        font-weight: 600;
    }
    
    .export-btn:hover {
        background: #229954;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }
    
    /* ===== MEMBER LIST ===== */
    .member-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .member-item {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1rem;
        border-radius: 10px;
        background: #f8f9fa;
        transition: all 0.3s;
    }
    
    .member-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .member-avatar {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .member-info {
        flex: 1;
    }
    
    .member-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        font-size: 1.05rem;
    }
    
    .member-position {
        font-size: 0.9rem;
        color: #7f8c8d;
    }
    
    /* ===== TRANSACTION ITEM ===== */
    .transaction-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.3s;
    }
    
    .transaction-item:hover {
        background: #f8f9fa;
    }
    
    .transaction-item:last-child {
        border-bottom: none;
    }
    
    .transaction-info .type {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        font-size: 1.05rem;
    }
    
    .transaction-info .date {
        font-size: 0.9rem;
        color: #7f8c8d;
    }
    
    .transaction-amount {
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .transaction-amount.in {
        color: #27ae60;
    }
    
    .transaction-amount.out {
        color: #e74c3c;
    }
    
    /* ===== TROPHY SHOWCASE ===== */
    .trophy-showcase {
        display: grid;
        gap: 1rem;
    }
    
    .trophy-item {
        display: flex;
        gap: 1.25rem;
        padding: 1.25rem;
        border-radius: 10px;
        background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
        transition: transform 0.3s;
    }
    
    .trophy-item:hover {
        transform: translateX(5px);
    }
    
    .trophy-icon {
        font-size: 3rem;
        flex-shrink: 0;
    }
    
    .trophy-details h4 {
        color: #2c3e50;
        margin-bottom: 0.25rem;
        font-size: 1.05rem;
        font-weight: 600;
    }
    
    .trophy-details p {
        font-size: 0.9rem;
        color: #555;
    }
    
    /* ===== POSITION STATS ===== */
    .position-stats {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .position-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    .position-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .position-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.05rem;
    }
    
    .position-count {
        background: #3498db;
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 1rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }
    
    .empty-state-icon {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    /* ===== MOBILE RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
    
   /* ===== MOBILE RESPONSIVE ===== */
@media (max-width: 768px) {
    .navbar {
        padding: 0.75rem 0;
    }
    
    .navbar-container {
        flex-direction: row;           /* UBAH: tetap horizontal */
        align-items: center;           /* UBAH: rata tengah vertikal */
        padding: 0 1rem;
        overflow-x: auto;              /* TAMBAH: bisa scroll horizontal */
        white-space: nowrap;
    }
    
    .navbar h2 {
        font-size: 1.2rem;
        text-align: left;              /* UBAH: rata kiri */
        margin-right: 1rem;            /* TAMBAH: kasih jarak */
        flex-shrink: 0;                /* TAMBAH: jangan mengecil */
    }
    
    .navbar nav {
        display: flex;
        flex-wrap: nowrap;             /* UBAH: jangan wrap */
        gap: 0.5rem;
        padding-bottom: 0.5rem;        /* TAMBAH: space untuk scroll */
    }
    
    .navbar nav a {
        font-size: 0.85rem;
        padding: 0.6rem 0.8rem;
        width: auto;                   /* UBAH: lebar sesuai konten */
        text-align: center;
        flex-shrink: 0;                /* TAMBAH: jangan mengecil */
        white-space: nowrap;
    }
    
    /* Sembunyikan scrollbar untuk tampilan bersih */
    .navbar-container::-webkit-scrollbar {
        display: none;
    }
    
    .navbar-container {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
}
        
        .container {
            padding: 0 1rem;
            margin: 1rem auto;
        }
        
        .welcome {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .welcome h1 {
            font-size: 1.5rem;
        }
        
        .welcome p {
            font-size: 0.95rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            font-size: 1.75rem;
        }
        
        .stat-info .number {
            font-size: 1.75rem;
        }
        
        .content-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .card {
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .card-header h3 {
            font-size: 1.2rem;
        }
        
        .export-btn {
            width: 100%;
            justify-content: center;
            padding: 0.75rem;
        }
        
        .member-item {
            padding: 0.75rem;
            gap: 1rem;
        }
        
        .member-avatar {
            width: 45px;
            height: 45px;
        }
        
        .member-name {
            font-size: 1rem;
        }
        
        .transaction-item {
            padding: 1rem;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .trophy-item {
            padding: 1rem;
            gap: 1rem;
        }
        
        .trophy-icon {
            font-size: 2rem;
        }
        
        .position-item {
            padding: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .navbar h2 {
            font-size: 1.2rem;
        }
        
        .navbar nav a {
            font-size: 0.85rem;
            padding: 0.75rem;
        }
        
        .container {
            padding: 0 0.75rem;
        }
        
        .welcome {
            padding: 1.25rem;
        }
        
        .welcome h1 {
            font-size: 1.3rem;
        }
        
        .stat-card {
            padding: 1.25rem;
        }
        
        .stat-info .number {
            font-size: 1.5rem;
        }
        
        .card {
            padding: 1.25rem;
        }
        
        .member-avatar {
            width: 40px;
            height: 40px;
        }
        
        .empty-state {
            padding: 2rem 1rem;
        }
        
        .empty-state-icon {
            font-size: 2.5rem;
        }
    }
</style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <h2>🏐 PORPPAD Admin</h2>
            <nav>
                <a href="dashboard.php" class="active">
                    <i class="fa fa-home"></i> Dashboard
                </a>
                <a href="approval.php">
                    <i class="fa fa-clock"></i> Persetujuan
                    <?php if ($pending_count > 0): ?>
                        <span class="badge-notification"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="members.php">
                    <i class="fa fa-users"></i> Anggota
                </a>
                <a href="kas.php">
                    <i class="fa fa-wallet"></i> Kas
                </a>
                <a href="trophies.php">
                    <i class="fa fa-trophy"></i> Prestasi
                </a>
                <a href="gallery.php">
                    <i class="fa fa-images"></i> Galeri
                </a>
                <a href="../home.php" style="background: #27ae60;">
                    <i class="fa fa-globe"></i> Home
                </a>
                <a href="logout.php" style="background: #e74c3c;">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </div>

    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome">
            <h1>👋 Selamat Datang Admin PORPPAD</h1>
            <p>Kelola data club volley Anda dengan mudah. Berikut adalah ringkasan sistem.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card members">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>Total Anggota</h3>
                    <div class="number"><?= $total_members ?></div>
                </div>
            </div>
            
            <div class="stat-card trophies">
                <div class="stat-icon">🏆</div>
                <div class="stat-info">
                    <h3>Total Prestasi</h3>
                    <div class="number"><?= $total_trophies ?></div>
                </div>
            </div>
            
            <div class="stat-card kas">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Total Pemasukan</h3>
                    <div class="number">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="stat-card saldo">
                <div class="stat-icon">💵</div>
                <div class="stat-info">
                    <h3>Saldo Kas</h3>
                    <div class="number">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Left Column -->
            <div>
                <!-- Recent Members -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3><i class="fa fa-users"></i> Anggota Terbaru</h3>
                        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                            <a href="export_members.php" class="export-btn" target="_blank">
                                📊 Export Excel
                            </a>
                            <a href="members.php">Lihat Semua →</a>
                        </div>
                    </div>
                    
                    <?php if ($recent_members->num_rows > 0): ?>
                        <div class="member-list">
                            <?php while ($member = $recent_members->fetch_assoc()): ?>
                                <div class="member-item">
                                    <?php if ($member['foto']): ?>
                                        <img src="../uploads/members/<?= $member['foto'] ?>" alt="<?= $member['nama'] ?>" class="member-avatar" onerror="this.src='../assets/img/default-profile.jpg'">
                                    <?php else: ?>
                                        <img src="../assets/img/default-profile.jpg" alt="Default" class="member-avatar">
                                    <?php endif; ?>
                                    <div class="member-info">
                                        <div class="member-name"><?= $member['nama'] ?></div>
                                        <div class="member-position"><?= $member['posisi'] ?> • <?= $member['umur'] ?> tahun</div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <p>Belum ada anggota</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Transactions -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa fa-money-bill-wave"></i> Transaksi Terbaru</h3>
                        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                            <a href="export_kas.php" class="export-btn" target="_blank">
                                📊 Export Excel
                            </a>
                            <a href="kas.php">Lihat Semua →</a>
                        </div>
                    </div>
                    
                    <?php if ($recent_kas->num_rows > 0): ?>
                        <?php while ($kas = $recent_kas->fetch_assoc()): ?>
                            <div class="transaction-item">
                                <div class="transaction-info">
                                    <div class="type"><?= $kas['deskripsi'] ?></div>
                                    <div class="date">📅 <?= date('d M Y', strtotime($kas['tanggal'])) ?></div>
                                </div>
                                <div class="transaction-amount <?= $kas['jenis'] == 'Pemasukan' ? 'in' : 'out' ?>">
                                    <?= $kas['jenis'] == 'Pemasukan' ? '+' : '-' ?> Rp <?= number_format($kas['jumlah'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">💰</div>
                            <p>Belum ada transaksi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Position Statistics -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3><i class="fa fa-chart-bar"></i> Statistik Posisi</h3>
                    </div>
                    
                    <?php if ($position_stats->num_rows > 0): ?>
                        <div class="position-stats">
                            <?php while ($pos = $position_stats->fetch_assoc()): ?>
                                <div class="position-item">
                                    <span class="position-name"><?= $pos['posisi'] ?></span>
                                    <span class="position-count"><?= $pos['jumlah'] ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <p>Belum ada data</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Trophies -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa fa-trophy"></i> Prestasi Terbaru</h3>
                        <a href="trophies.php">Lihat Semua →</a>
                    </div>
                    
                    <?php if ($recent_trophies->num_rows > 0): ?>
                        <div class="trophy-showcase">
                            <?php while ($trophy = $recent_trophies->fetch_assoc()): ?>
                                <div class="trophy-item">
                                    <div class="trophy-icon">🏆</div>
                                    <div class="trophy-details">
                                        <h4><?= $trophy['judul'] ?></h4>
                                        <p>📅 <?= $trophy['tanggal'] ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🏆</div>
                            <p>Belum ada prestasi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</body>
</html>