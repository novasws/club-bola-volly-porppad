<?php
require_once 'config.php';

// Get gender filter
$gender_filter = $_GET['gender'] ?? 'Putra';
$search = $_GET['search'] ?? '';

// Build query with filters - SORTED BY YOUNGEST (umur ASC)
$query = "SELECT * FROM members WHERE status='approved'";

if (!empty($gender_filter)) {
    $query .= " AND gender = '" . mysqli_real_escape_string($conn, $gender_filter) . "'";
}

if (!empty($search)) {
    $search_clean = mysqli_real_escape_string($conn, $search);
    $query .= " AND nama LIKE '%$search_clean%'";
}

// ORDER BY umur ASC (termuda di atas)
$query .= " ORDER BY umur ASC, nama ASC";

// Execute query
$members_result = mysqli_query($conn, $query);

// Count members by gender
$count_putra = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender = 'Putra' AND status='approved'")->fetch_assoc()['total'] ?? 0;
$count_putri = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender = 'Putri' AND status='approved'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
            --radius-lg: 24px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
            color: var(--text-dark);
        }
        
        /* NAVBAR */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }
        
        .logo-img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
        }
        
        .brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--text-dark);
            padding: 0.6rem 1rem !important;
            border-radius: 12px;
            transition: all 0.3s;
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
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        /* HEADER */
        .page-header {
            background: var(--primary-gradient);
            padding: 5rem 0 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .page-header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            font-size: clamp(1.1rem, 2vw, 1.4rem);
            color: rgba(255, 255, 255, 0.95);
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
            transition: all 0.4s;
            text-decoration: none;
            color: var(--text-dark);
            border: 2px solid rgba(0, 0, 0, 0.08);
            background: white;
            box-shadow: var(--shadow-md);
        }
        
        .gender-tab:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        
        .gender-tab.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-8px) scale(1.02);
        }
        
        .gender-tab .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .gender-tab .count {
            font-size: 3rem;
            font-weight: 800;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        /* TABLE */
        .table-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 3rem;
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
        }
        
        .table tbody td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.03);
        }
        
        .member-photo-sm {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .badge-position {
            padding: 0.4rem 1rem;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 600;
            background: var(--primary-gradient);
            color: white;
        }
        
        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }
            
            .table thead th {
                padding: 1rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .table tbody td {
                padding: 1rem 0.5rem;
            }
            
            .member-photo-sm {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo/logo.png" alt="logo" class="me-2 logo-img" onerror="this.style.display='none'" />
                <div>
                    <div class="brand-title">Volley Club</div>
                    <small style="font-size: 0.8rem; color: var(--text-light);">PORPPAD</small>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" href="timpa.php">Tim Kami</a></li>
                    <li class="nav-item ms-2">
                        <a href="login.php" class="btn btn-admin btn-rounded">
                            <i class="fa fa-user-shield me-1"></i> Admin
                        </a>
                    </li>
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
                    <div style="font-size: 1.1rem; font-weight: 600; text-transform: uppercase;">Tim Putra</div>
                </a>
                <a href="?gender=Putri" class="gender-tab <?= $gender_filter === 'Putri' ? 'active' : '' ?>">
                    <div class="icon">👩</div>
                    <div class="count"><?= $count_putri ?></div>
                    <div style="font-size: 1.1rem; font-weight: 600; text-transform: uppercase;">Tim Putri</div>
                </a>
            </div>

            <!-- Search Box -->
            <div style="margin-bottom: 3rem;">
                <form method="GET" class="d-flex gap-2">
                    <div class="flex-grow-1 position-relative">
                        <input type="hidden" name="gender" value="<?= htmlspecialchars($gender_filter) ?>">
                        <input 
                            type="text" 
                            name="search" 
                            class="form-control" 
                            placeholder="🔍 Cari nama anggota..." 
                            value="<?= htmlspecialchars($search) ?>"
                            style="padding: 1rem; border-radius: 16px; border: 2px solid rgba(0,0,0,0.08);"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; border-radius: 16px;">Cari</button>
                    <?php if ($search): ?>
                        <a href="?gender=<?= $gender_filter ?>" class="btn btn-secondary" style="padding: 1rem 2rem; border-radius: 16px;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Members Table -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0" style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 1.75rem;">
                        <i class="fas fa-users me-2"></i>
                        Tim <?= $gender_filter ?>
                    </h4>
                    <span class="badge" style="background: var(--primary-gradient); padding: 0.5rem 1rem; border-radius: 100px; font-size: 1.05rem;">
                        <?= mysqli_num_rows($members_result) ?> Anggota
                    </span>
                </div>

                <?php if (mysqli_num_rows($members_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="80">No</th>
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
                                $no = 1;
                                while ($member = mysqli_fetch_assoc($members_result)): 
                                ?>
                                    <tr>
                                        <td><strong><?= $no++ ?></strong></td>
                                        <td>
                                            <img 
                                                src="<?= !empty($member['foto']) ? 'uploads/members/' . htmlspecialchars($member['foto']) : 'assets/img/default-profile.jpg' ?>" 
                                                alt="<?= htmlspecialchars($member['nama']) ?>"
                                                class="member-photo-sm"
                                                onerror="this.src='assets/img/default-profile.jpg'"
                                            />
                                        </td>
                                        <td>
                                            <strong style="font-weight: 600; font-size: 1.05rem;">
                                                <?= htmlspecialchars($member['nama']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= !empty($member['tempat_lahir']) ? htmlspecialchars($member['tempat_lahir']) : '-' ?>
                                        </td>
                                        <td>
                                            <?= !empty($member['tanggal_lahir']) ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-' ?>
                                        </td>
                                        <td>
                                            <span style="display: inline-block; padding: 0.4rem 1rem; border-radius: 100px; font-size: 0.85rem; font-weight: 600; background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); color: white;">
                                                <?= $member['umur'] ?> th
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-position">
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
    <footer style="background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%); color: #cbd5e0; padding: 3rem 0 1.5rem 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="fw-bold mb-2" style="font-family: 'Space Grotesk', sans-serif;">Volley Club PORPPAD</h5>
                    <p class="small text-muted mb-0">
                        GOR FASHA • Surabaya • Telp: 0812-3456-7890
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