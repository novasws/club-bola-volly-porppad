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
    <meta name="viewport" content="width=1200">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar h2 {
            font-size: 1.5rem;
        }
        
        .navbar nav a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
            position: relative;
        }
        
        .navbar nav a:hover {
            background: #34495e;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .welcome h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .welcome p {
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .stat-icon.members {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-icon.kas {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-icon.trophies {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-icon.saldo {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stat-info h3 {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .stat-info .number {
            font-size: 1.75rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .card-header h3 {
            color: #2c3e50;
            font-size: 1.25rem;
        }
        
        .card-header a {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .card-header a:hover {
            color: #2980b9;
        }
        
        /* EXPORT BUTTON STYLES */
        .export-btn {
            background: #27ae60;
            color: white !important;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .export-btn:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(39, 174, 96, 0.3);
        }
        
        .export-btn i {
            font-size: 1rem;
        }
        
        .member-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .member-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            background: #f8f9fa;
            transition: background 0.3s;
        }
        
        .member-item:hover {
            background: #e9ecef;
        }
        
        .member-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .member-position {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-info .type {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .transaction-info .date {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .transaction-amount {
            font-weight: bold;
        }
        
        .transaction-amount.in {
            color: #27ae60;
        }
        
        .transaction-amount.out {
            color: #e74c3c;
        }
        
        .trophy-showcase {
            display: grid;
            gap: 1rem;
        }
        
        .trophy-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
        }
        
        .trophy-icon {
            font-size: 2.5rem;
        }
        
        .trophy-details h4 {
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .trophy-details p {
            font-size: 0.85rem;
            color: #555;
        }
        
        .position-stats {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .position-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .position-name {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .position-count {
            background: #3498db;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #999;
        }
        
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .navbar nav {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🏐 PORPPAD Admin</h2>
       <nav>
            <a href="dashboard.php" style="background: #34495e;">Dashboard</a>
            <a href="approval.php">
                Persetujuan 
                <?php if ($pending_count > 0): ?>
                    <span style="background: red; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;"><?= $pending_count ?></span>
                <?php endif; ?>
            </a>
            <a href="members.php">Anggota</a>
            <a href="kas.php">Kas</a>
            <a href="trophies.php">Prestasi</a>
            <a href="gallery.php">📸 Galeri</a>
            <a href="../home.php" style="background: #27ae60;">🏠 Home</a>
            <a href="logout.php" style="background: #e74c3c;">🚪 Logout</a>
        </nav>
    </div>

    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome">
            <h1>👋 Selamat Datang Admin PORPPAD</h1>
            <p>Kelola data club volley Anda dengan mudah. Berikut adalah ringkasan sistem.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon members">👥</div>
                <div class="stat-info">
                    <h3>Total Anggota</h3>
                    <div class="number"><?= $total_members ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon trophies">🏆</div>
                <div class="stat-info">
                    <h3>Total Prestasi</h3>
                    <div class="number"><?= $total_trophies ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon kas">💰</div>
                <div class="stat-info">
                    <h3>Total Pemasukan</h3>
                    <div class="number">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon saldo">💵</div>
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
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>👥 Anggota Terbaru</h3>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
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
                        <div class="empty-state">Belum ada anggota</div>
                    <?php endif; ?>
                </div>

                <!-- Recent Transactions -->
                <div class="card">
                    <div class="card-header">
                        <h3>💰 Transaksi Terbaru</h3>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
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
                                    <div class="date"><?= date('d M Y', strtotime($kas['tanggal'])) ?></div>
                                </div>
                                <div class="transaction-amount <?= $kas['jenis'] == 'Pemasukan' ? 'in' : 'out' ?>">
                                    <?= $kas['jenis'] == 'Pemasukan' ? '+' : '-' ?> Rp <?= number_format($kas['jumlah'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">Belum ada transaksi</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Position Statistics -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>📊 Statistik Posisi</h3>
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
                        <div class="empty-state">Belum ada data</div>
                    <?php endif; ?>
                </div>

                <!-- Recent Trophies -->
                <div class="card">
                    <div class="card-header">
                        <h3>🏆 Prestasi Terbaru</h3>
                        <a href="trophies.php">Lihat Semua →</a>
                    </div>
                    
                    <?php if ($recent_trophies->num_rows > 0): ?>
                        <div class="trophy-showcase">
                            <?php while ($trophy = $recent_trophies->fetch_assoc()): ?>
                                <div class="trophy-item">
                                    <div class="trophy-icon">🏆</div>
                                    <div class="trophy-details">
                                        <h4><?= $trophy['judul'] ?></h4>
                                        <p><?= $trophy['tanggal'] ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">Belum ada prestasi</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>