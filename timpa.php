<?php
require_once 'config.php';

// Get gender filter
$gender_filter = $_GET['gender'] ?? 'Putra';
$search = $_GET['search'] ?? '';

// Build query with filters
$query = "SELECT * FROM members WHERE status='approved'";

if (!empty($gender_filter)) {
    $query .= " AND gender = '" . mysqli_real_escape_string($conn, $gender_filter) . "'";
}

if (!empty($search)) {
    $search_clean = mysqli_real_escape_string($conn, $search);
    $query .= " AND nama LIKE '%$search_clean%'";
}

$query .= " ORDER BY created_at DESC";

// Execute query
$members_result = mysqli_query($conn, $query);

// Count members by gender
$count_putra = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender = 'Putra' AND status='approved'")->fetch_assoc()['total'] ?? 0;
$count_putri = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender = 'Putri' AND status='approved'")->fetch_assoc()['total'] ?? 0;

// Function to calculate age from birth date
function calculateAge($birthDate) {
    if (empty($birthDate)) return '-';
    
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    $age = $today->diff($birth)->y;
    
    return $age;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Tim Kami - Volley Club PORPPAD</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-dark: #5a67d8;
            --text-dark: #1a202c;
            --text-light: #718096;
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.15);
            --radius-sm: 12px;
            --radius-md: 16px;
            --radius-lg: 24px;
        }
        
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
            color: var(--text-dark);
        }
        
        /* NAVBAR */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }
        
        .logo-img {
            width: 45px;
            height: 45px;
            border-radius: var(--radius-sm);
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover .logo-img {
            transform: scale(1.05) rotate(2deg);
        }
        
        .brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .brand-sub {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--text-dark);
            transition: all 0.3s ease;
            padding: 0.6rem 1rem !important;
            border-radius: var(--radius-sm);
        }
        
        .nav-link:hover {
            color: var(--primary-dark);
            background: rgba(102, 126, 234, 0.05);
        }
        
        .nav-link.active {
            color: white !important;
            background: var(--primary-gradient);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-rounded {
            border-radius: 100px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline-danger {
            border: 2px solid #ef4444;
            color: #ef4444;
        }
        
        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }
        
        /* HEADER */
        .page-header {
            background: var(--primary-gradient);
            padding: 5rem 0 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .page-header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 800;
            margin-bottom: 1rem;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .page-header p {
            font-size: clamp(1.1rem, 2vw, 1.4rem);
            color: rgba(255, 255, 255, 0.95);
            position: relative;
            z-index: 1;
        }
        
        /* GENDER TABS */
        .gender-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .gender-tab {
            padding: 2rem;
            border-radius: var(--radius-lg);
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: var(--text-dark);
            border: 2px solid rgba(0, 0, 0, 0.08);
            background: white;
            box-shadow: var(--shadow-md);
        }
        
        .gender-tab:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            color: var(--text-dark);
        }
        
        .gender-tab.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            box-shadow: var(--shadow-xl);
            transform: translateY(-8px) scale(1.02);
        }
        
        .gender-tab .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.1));
        }
        
        .gender-tab .count {
            font-size: 3rem;
            font-weight: 800;
            font-family: 'Space Grotesk', sans-serif;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .gender-tab .label {
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* SEARCH BOX */
        .search-box {
            position: relative;
            margin-bottom: 3rem;
        }
        
        .search-box input {
            padding-left: 3.5rem;
            border-radius: var(--radius-md);
            border: 2px solid rgba(0, 0, 0, 0.08);
            padding-top: 1rem;
            padding-bottom: 1rem;
            font-size: 1.05rem;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .search-box i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.25rem;
        }
        
        .search-box .btn {
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
        }
        
        /* TABLE CONTAINER */
        .table-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 3rem;
        }
        
        .table-container h4 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.75rem;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 100px;
            font-size: 1.05rem;
            font-weight: 600;
        }
        
        .badge.bg-primary {
            background: var(--primary-gradient) !important;
        }
        
        /* DESKTOP TABLE */
        .desktop-table {
            display: block;
        }
        
        .table {
            font-size: 1rem;
        }
        
        .table thead {
            background: linear-gradient(135deg, #f8fafc 0%, #e3f2fd 100%);
        }
        
        .table thead th {
            font-weight: 700;
            color: var(--text-dark);
            padding: 1.25rem 1rem;
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }
        
        .table tbody td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.03);
            transform: scale(1.005);
        }
        
        .member-photo-sm {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .member-photo-sm:hover {
            transform: scale(1.1);
        }
        
        .badge-position {
            padding: 0.4rem 1rem;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 600;
            background: var(--primary-gradient);
            color: white;
        }
        
        .badge-info {
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            color: white;
        }
        
        /* MOBILE CARDS */
        .mobile-cards {
            display: none;
        }
        
        .member-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .member-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-gradient);
        }
        
        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .member-card-header {
            display: flex;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        
        .member-card img {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-md);
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .member-card-info {
            flex: 1;
        }
        
        .member-card-name {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        
        .member-card-position {
            display: inline-block;
            background: var(--primary-gradient);
            color: white;
            padding: 0.35rem 1rem;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .member-card-details {
            font-size: 0.95rem;
            color: var(--text-light);
        }
        
        .member-card-details > div {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: start;
        }
        
        .member-card-details i {
            width: 24px;
            margin-right: 0.75rem;
            color: var(--primary-dark);
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        
        /* FOOTER */
        footer {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: #cbd5e0;
            padding: 3rem 0 1.5rem 0;
            margin-top: 5rem;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .gender-tabs {
                gap: 1rem;
            }
            
            .gender-tab {
                padding: 1.5rem 1rem;
            }
            
            .gender-tab .icon {
                font-size: 2.5rem;
            }
            
            .gender-tab .count {
                font-size: 2.5rem;
            }
            
            .gender-tab .label {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .table-container {
                padding: 1.25rem;
            }
            
            .desktop-table {
                display: none;
            }
            
            .mobile-cards {
                display: block;
            }
            
            .search-box input {
                padding-left: 3rem;
                font-size: 1rem;
            }
            
            .search-box .btn {
                padding: 0.875rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg shadow-sm">
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
                    <li class="nav-item"><a class="nav-link" href="home.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" href="timpa.php">Tim Kami</a></li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item ms-2">
                            <a href="logout.php" class="btn btn-outline-danger btn-rounded">
                                <i class="fa fa-sign-out me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a href="login.php" class="btn btn-primary btn-rounded">
                                <i class="fa fa-user-plus me-1"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <section class="page-header text-center text-white">
        <div class="container">
            <h1>🏐 Tim Kami</h1>
            <p>Anggota Klub Bola Voli PORPPAD Surabaya</p>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="py-5">
        <div class="container">
            <!-- Gender Filter Tabs -->
            <div class="gender-tabs">
                <a href="?gender=Putra" class="gender-tab <?= $gender_filter === 'Putra' ? 'active' : '' ?>">
                    <div class="icon">👨</div>
                    <div class="count"><?= $count_putra ?></div>
                    <div class="label">Tim Putra</div>
                </a>
                <a href="?gender=Putri" class="gender-tab <?= $gender_filter === 'Putri' ? 'active' : '' ?>">
                    <div class="icon">👩</div>
                    <div class="count"><?= $count_putri ?></div>
                    <div class="label">Tim Putri</div>
                </a>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <form method="GET" class="d-flex gap-2">
                    <div class="flex-grow-1 position-relative">
                        <i class="fas fa-search"></i>
                        <input type="hidden" name="gender" value="<?= htmlspecialchars($gender_filter) ?>">
                        <input 
                            type="text" 
                            name="search" 
                            class="form-control" 
                            placeholder="🔍 Cari nama anggota..." 
                            value="<?= htmlspecialchars($search) ?>"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <?php if ($search): ?>
                        <a href="?gender=<?= $gender_filter ?>" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Members Display -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Daftar Anggota Tim <?= $gender_filter ?>
                    </h4>
                    <span class="badge bg-primary">
                        <?= mysqli_num_rows($members_result) ?> Anggota
                    </span>
                </div>

                <?php if (mysqli_num_rows($members_result) > 0): ?>
                    <!-- Desktop Table View -->
                    <div class="desktop-table table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="90">Foto</th>
                                    <th>Nama</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                    <th width="90">Umur</th>
                                    <th>Posisi</th>
                                    <th>Alamat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($members_result, 0);
                                while ($member = mysqli_fetch_assoc($members_result)): 
                                    $current_age = calculateAge($member['tanggal_lahir']);
                                ?>
                                    <tr>
                                        <td>
                                            <img 
                                                src="<?= !empty($member['foto']) ? 'uploads/members/' . htmlspecialchars($member['foto']) : 'assets/img/default-profile.jpg' ?>" 
                                                alt="<?= htmlspecialchars($member['nama']) ?>"
                                                class="member-photo-sm"
                                                onerror="this.src='assets/img/default-profile.jpg'"
                                            />
                                        </td>
                                        <td>
                                            <strong style="font-weight: 600; font-size: 1.05rem;"><?= htmlspecialchars($member['nama']) ?></strong>
                                        </td>
                                        <td>
                                            <?= !empty($member['tempat_lahir']) ? htmlspecialchars($member['tempat_lahir']) : '-' ?>
                                        </td>
                                        <td>
                                            <?= !empty($member['tanggal_lahir']) ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= $current_age ?> th</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-position">
                                                <?= htmlspecialchars($member['posisi']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?= !empty($member['alamat']) ? htmlspecialchars($member['alamat']) : '-' ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="mobile-cards">
                        <?php 
                        mysqli_data_seek($members_result, 0);
                        while ($member = mysqli_fetch_assoc($members_result)): 
                            $current_age = calculateAge($member['tanggal_lahir']);
                        ?>
                            <div class="member-card">
                                <div class="member-card-header">
                                    <img 
                                        src="<?= !empty($member['foto']) ? 'uploads/members/' . htmlspecialchars($member['foto']) : 'assets/img/default-profile.jpg' ?>" 
                                        alt="<?= htmlspecialchars($member['nama']) ?>"
                                        onerror="this.src='assets/img/default-profile.jpg'"
                                    />
                                    <div class="member-card-info">
                                        <div class="member-card-name"><?= htmlspecialchars($member['nama']) ?></div>
                                        <span class="member-card-position"><?= htmlspecialchars($member['posisi']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="member-card-details">
                                    <div>
                                        <i class="fas fa-birthday-cake"></i>
                                        <span>
                                            <?= !empty($member['tempat_lahir']) ? htmlspecialchars($member['tempat_lahir']) : '-' ?>, 
                                            <?= !empty($member['tanggal_lahir']) ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-' ?>
                                        </span>
                                    </div>
                                    <div>
                                        <i class="fas fa-user-clock"></i>
                                        <span><?= $current_age ?> tahun</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= !empty($member['alamat']) ? htmlspecialchars($member['alamat']) : '-' ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa fa-users fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                        <h4 class="text-muted mb-3">
                            <?= $search ? 'Tidak ada hasil untuk "' . htmlspecialchars($search) . '"' : 'Belum ada anggota di tim ' . $gender_filter ?>
                        </h4>
                        <?php if ($search): ?>
                            <a href="?gender=<?= $gender_filter ?>" class="btn btn-primary btn-rounded mt-3">Lihat Semua</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="fw-bold mb-2" style="font-family: 'Space Grotesk', sans-serif;">Volley Club PORPPAD</h5>
                    <p class="small text-muted mb-0">
                        GOR FASHA • Surabaya • Telp: 0812-3456-7890 • Email: clubvolley@gmail.com
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">© 2025 Volley Club. All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>