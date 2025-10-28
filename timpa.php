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
 <meta name="viewport" content="width=1200" />
    <title>Tim Kami - Volley Club PORPPAD</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        
        .member-photo-sm {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
            cursor: pointer;
        }
        
        .gender-tabs {
          
            display: flex;
          
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .gender-tab {
            flex: 1;
            padding: 1.5rem;
            border-radius: 3px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #495057;
            border: 2px solid #dee2e6;
            background: white;
        }
        
        .gender-tab:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .gender-tab.active {
            background: linear-gradient(135deg, #59a2f5ff 0%, #205298ff 100%);
            color: white;
            border-color: #667eea;
        }
        
        .gender-tab .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .gender-tab .count {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .search-box input {
            padding-left: 3rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .badge-position {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Responsive Table untuk Mobile */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            .member-photo-sm {
                width: 45px;
                height: 45px;
            }
            
            .gender-tabs {
                flex-direction: column;
            }
            
            .gender-tab {
                padding: 1rem;
            }
            
            .gender-tab .icon {
                font-size: 1.5rem;
            }
            
            .gender-tab .count {
                font-size: 1.2rem;
            }
        }
        
        /* IMPROVED: Mobile Card Layout */
        @media (max-width: 576px) {
            .table-container {
                padding: 1rem;
            }
            
            /* Hide table on small screens */
            .desktop-table {
                display: none;
            }
            
            /* Show card layout on mobile */
            .mobile-cards {
                display: block;
            }
            
            .member-card {
                background: white;
                border-radius: 12px;
                padding: 1rem;
                margin-bottom: 1rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border-left: 4px solid #667eea;
            }
            
            .member-card-header {
                display: flex;
                gap: 1rem;
                margin-bottom: 0.75rem;
            }
            
            .member-card img {
                width: 70px;
                height: 70px;
                border-radius: 10px;
                object-fit: cover;
            }
            
            .member-card-info {
                flex: 1;
            }
            
            .member-card-name {
                font-weight: 600;
                font-size: 1rem;
                color: #2c3e50;
                margin-bottom: 0.25rem;
            }
            
            .member-card-position {
                display: inline-block;
                background: #667eea;
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 15px;
                font-size: 0.75rem;
            }
            
            .member-card-details {
                font-size: 0.85rem;
                color: #555;
            }
            
            .member-card-details > div {
                margin-bottom: 0.5rem;
                display: flex;
                align-items: start;
            }
            
            .member-card-details i {
                width: 20px;
                margin-right: 0.5rem;
                color: #667eea;
                flex-shrink: 0;
            }
        }
        
        /* Show table on larger screens, hide cards */
        @media (min-width: 577px) {
            .mobile-cards {
                display: none;
            }
            
            .desktop-table {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm py-3" style="background: white;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo/logofiks.png" alt="logo" class="me-2" style="width: 40px;" onerror="this.style.display='none'" />
                <div>
                    <div class="fw-bold">Volley Club</div>
                    <small class="text-muted">PORPPAD</small>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="home.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" href="timpa.php">Tim Kami</a></li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item ms-2">
                            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fa fa-sign-out me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-3">
                            <a href="login.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-user-plus me-2"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <section class="py-5" style="background: linear-gradient(135deg, #6494d8ff 0%, #4f94d4ff 100%);">
        <div class="container text-white text-center">
            <h1 class="fw-bold mb-2">🏐 Tim Kami</h1>
            <p class="lead mb-0">Anggota Klub Bola Voli PORPPAD Surabaya</p>
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
                    <div>Tim Putra</div>
                </a>
                <a href="?gender=Putri" class="gender-tab <?= $gender_filter === 'Putri' ? 'active' : '' ?>">
                    <div class="icon">👩</div>
                    <div class="count"><?= $count_putri ?></div>
                    <div>Tim Putri</div>
                </a>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="gender" value="<?= htmlspecialchars($gender_filter) ?>">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control form-control-lg" 
                        placeholder="🔍 Cari nama anggota..." 
                        value="<?= htmlspecialchars($search) ?>"
                    >
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <?php if ($search): ?>
                        <a href="?gender=<?= $gender_filter ?>" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Members Display -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Daftar Anggota Tim <?= $gender_filter ?>
                    </h4>
                    <span class="badge bg-primary" style="font-size: 1rem;">
                        <?= mysqli_num_rows($members_result) ?> Anggota
                    </span>
                </div>

                <?php if (mysqli_num_rows($members_result) > 0): ?>
                    <!-- Desktop Table View -->
                    <div class="desktop-table table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Foto</th>
                                    <th>Nama</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                    <th width="80">Umur</th>
                                    <th>Posisi</th>
                                    <th>Alamat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($members_result, 0); // Reset pointer
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
                                            <strong><?= htmlspecialchars($member['nama']) ?></strong>
                                        </td>
                                        <td>
                                            <?= !empty($member['tempat_lahir']) ? htmlspecialchars($member['tempat_lahir']) : '-' ?>
                                        </td>
                                        <td>
                                            <?= !empty($member['tanggal_lahir']) ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $current_age ?> th</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-position bg-primary">
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
                        mysqli_data_seek($members_result, 0); // Reset pointer
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
                        <i class="fa fa-users fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">
                            <?= $search ? 'Tidak ada hasil untuk "' . htmlspecialchars($search) . '"' : 'Belum ada anggota di tim ' . $gender_filter ?>
                        </h4>
                        <?php if ($search): ?>
                            <a href="?gender=<?= $gender_filter ?>" class="btn btn-primary mt-3">Lihat Semua</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-4 bg-dark text-light mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold">Volley Club PORPPAD</h5>
                    <p class="small text-muted">
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