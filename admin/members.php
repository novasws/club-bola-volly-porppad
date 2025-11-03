<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete member
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Get member data first to delete photo
    $stmt = $conn->prepare("SELECT foto FROM members WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
    
    if ($member) {
        // Delete photo file if exists
        if (!empty($member['foto']) && file_exists("../uploads/members/" . $member['foto'])) {
            unlink("../uploads/members/" . $member['foto']);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Anggota berhasil dihapus!";
    }
    header("Location: members.php");
    exit();
}

// Handle status update
if (isset($_GET['update_status'])) {
    $member_id = intval($_GET['id']);
    $new_status = $_GET['update_status'];
    
    $stmt = $conn->prepare("UPDATE members SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $member_id);
    $stmt->execute();
    
    $_SESSION['success'] = "Status anggota berhasil diupdate!";
    header("Location: members.php");
    exit();
}

// Search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(nama LIKE ? OR posisi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(' AND ', $where_conditions);
}

// Get members
$sql = "SELECT * FROM members $where_sql ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$members_result = $stmt->get_result();

// Statistics
$total_members = $conn->query("SELECT COUNT(*) as total FROM members")->fetch_assoc()['total'];
$approved_members = $conn->query("SELECT COUNT(*) as total FROM members WHERE status='approved'")->fetch_assoc()['total'];
$pending_members = $conn->query("SELECT COUNT(*) as total FROM members WHERE status='pending'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Admin PORPPAD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
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
        }
        
        .navbar nav a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }
        
        .navbar nav a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .page-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-title {
            font-size: 2rem;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e67e22;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-weight: 600;
        }
        
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .notification-toast {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .notification-toast.success {
            background: #27ae60;
        }
        
        .notification-toast.show {
            transform: translateX(0);
        }
        
        @media (max-width: 768px) {
            .navbar-container {
                flex-direction: column;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <h2>🏐 PORPPAD Admin</h2>
            <nav>
                <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
                <a href="approval.php"><i class="fa fa-clock"></i> Persetujuan</a>
                <a href="members.php" class="active"><i class="fa fa-users"></i> Anggota</a>
                <a href="kas.php"><i class="fa fa-wallet"></i> Kas</a>
                <a href="trophies.php"><i class="fa fa-trophy"></i> Prestasi</a>
                <a href="gallery.php"><i class="fa fa-images"></i> Galeri</a>
                <a href="../home.php" style="background: #27ae60;"><i class="fa fa-globe"></i> Home</a>
                <a href="logout.php" style="background: #e74c3c;"><i class="fa fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title"><i class="fa fa-users"></i> Kelola Anggota</h1>
            <a href="add_member.php" class="btn btn-primary">
                <i class="fa fa-user-plus"></i> Tambah Anggota
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_members ?></div>
                <div class="stat-label">Total Anggota</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $approved_members ?></div>
                <div class="stat-label">Disetujui</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pending_members ?></div>
                <div class="stat-label">Menunggu</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="filters">
            <div class="form-group">
                <label for="search">Cari Anggota</label>
                <input type="text" id="search" name="search" class="form-control" 
                       placeholder="Nama atau posisi..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="approved" <?= $status_filter == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                    <i class="fa fa-search"></i> Filter
                </button>
                <a href="members.php" class="btn" style="background: #95a5a6; color: white; margin-left: 0.5rem;">
                    <i class="fa fa-refresh"></i> Reset
                </a>
            </div>
        </form>

        <!-- Members Table -->
        <div class="table-container">
            <?php if ($members_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Posisi</th>
                            <th>Umur</th>
                            <th>Status</th>
                            <th>Tanggal Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($member = $members_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($member['foto'])): ?>
                                        <img src="../uploads/members/<?= htmlspecialchars($member['foto']) ?>" 
                                             alt="<?= htmlspecialchars($member['nama']) ?>" 
                                             class="member-avatar"
                                             onerror="this.src='../assets/img/default-profile.jpg'">
                                    <?php else: ?>
                                        <img src="../assets/img/default-profile.jpg" alt="Default" class="member-avatar">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($member['nama']) ?></td>
                                <td><?= htmlspecialchars($member['posisi']) ?></td>
                                <td><?= htmlspecialchars($member['umur']) ?> tahun</td>
                                <td>
                                    <span class="status-badge status-<?= $member['status'] ?>">
                                        <?= $member['status'] == 'approved' ? 'Disetujui' : 'Menunggu' ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($member['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_member.php?id=<?= $member['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <?php if ($member['status'] == 'pending'): ?>
                                            <a href="members.php?update_status=approved&id=<?= $member['id'] ?>" 
                                               class="btn btn-success btn-sm">
                                                <i class="fa fa-check"></i> Setujui
                                            </a>
                                        <?php endif; ?>
                                        <a href="members.php?delete_id=<?= $member['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <h3>Tidak ada anggota</h3>
                    <p>Belum ada data anggota yang tercatat.</p>
                    <a href="add_member.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fa fa-user-plus"></i> Tambah Anggota Pertama
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification" class="notification-toast"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script>
    function showNotification(message, type = 'success') {
        const toast = document.getElementById('notification');
        toast.textContent = message;
        toast.className = `notification-toast ${type} show`;
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Show success message from PHP session
    <?php if (isset($_SESSION['success'])): ?>
        showNotification('<?= $_SESSION['success'] ?>', 'success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    // Auto refresh every 5 minutes
    setInterval(() => {
        window.location.reload();
    }, 300000);
    </script>
</body>
</html>