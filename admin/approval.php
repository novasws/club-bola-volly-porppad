<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Approve
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $admin_id = $_SESSION['user_id'];
    $query = "UPDATE members SET status='approved', approved_at=NOW(), approved_by='$admin_id' WHERE id='$id'";
    
    if (mysqli_query($conn, $query)) {
        $success = "✅ Anggota berhasil disetujui!";
    } else {
        $error = "❌ Gagal menyetujui anggota!";
    }
}

// Handle Reject
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    
    if (mysqli_query($conn, "UPDATE members SET status='rejected' WHERE id='$id'")) {
        $success = "❌ Pendaftaran ditolak!";
    } else {
        $error = "Gagal menolak pendaftaran!";
    }
}

// Get pending members
$pending = $conn->query("SELECT m.*, u.username 
                         FROM members m 
                         LEFT JOIN users u ON m.user_id = u.id 
                         WHERE m.status = 'pending' 
                         ORDER BY m.created_at DESC");

// Get approved members (recent)
$approved = $conn->query("SELECT m.*, u.username, a.username as approved_by_name
                          FROM members m 
                          LEFT JOIN users u ON m.user_id = u.id 
                          LEFT JOIN users a ON m.approved_by = a.id
                          WHERE m.status = 'approved' 
                          ORDER BY m.approved_at DESC LIMIT 5");

// Get rejected members (recent)
$rejected = $conn->query("SELECT m.*, u.username 
                          FROM members m 
                          LEFT JOIN users u ON m.user_id = u.id 
                          WHERE m.status = 'rejected' 
                          ORDER BY m.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
   <meta name="viewport" content="width=1200">
    <title>Persetujuan Anggota - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        
        /* NAVBAR IMPROVED */
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar h2 {
            font-size: 1.3rem;
            white-space: nowrap;
        }
        
        .navbar nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .navbar nav a {
            color: white;
            text-decoration: none;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            transition: background 0.3s;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .navbar nav a:hover {
            background: #34495e;
        }
        
        .navbar nav a.active {
            background: #e74c3c;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card h3 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.4rem;
        }
        
        /* APPROVAL CARD IMPROVED */
        .member-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            border-left: 4px solid #ffc107;
            transition: transform 0.3s;
        }
        
        .member-card:hover {
            transform: translateY(-3px);
        }
        
        .member-card.approved {
            border-left-color: #28a745;
        }
        
        .member-card.rejected {
            border-left-color: #dc3545;
        }
        
        .member-card h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #000;
        }
        
        .badge-approved {
            background: #28a745;
            color: white;
        }
        
        .badge-rejected {
            background: #dc3545;
            color: white;
        }
        
        table {
            width: 100%;
            font-size: 0.9rem;
        }
        
        table td {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        table td:first-child {
            font-weight: 600;
            color: #555;
            width: 200px;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        .tabs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .tab {
            padding: 1.5rem 1rem;
            background: white;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tab:hover {
            transform: translateY(-3px);
        }
        
        .tab .count {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        /* RESPONSIVE IMPROVEMENTS */
        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }
            
            .card {
                padding: 1.5rem 1rem;
            }
            
            .member-card {
                padding: 1rem;
            }
            
            .navbar {
                flex-direction: column;
                text-align: center;
            }
            
            .navbar nav {
                width: 100%;
            }
            
            table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.85rem;
                display: block;
                width: 100%;
            }
            
            table td:first-child {
                width: 100%;
                font-size: 0.8rem;
                padding-bottom: 0.25rem;
                border-bottom: none;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                padding: 0.7rem 1rem;
                font-size: 0.9rem;
                text-align: center;
            }
            
            .tabs {
                grid-template-columns: 1fr;
            }
            
            .tab .count {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .navbar nav a {
                font-size: 0.8rem;
                padding: 0.5rem 0.8rem;
            }
            
            .member-card h4 {
                font-size: 1.1rem;
            }
            
            .card h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🏐 PORPPAD Admin</h2>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="approval.php" class="active">
                Persetujuan 
                <?php if ($pending->num_rows > 0): ?>
                    <span style="background: red; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;"><?= $pending->num_rows ?></span>
                <?php endif; ?>
            </a>
            <a href="members.php">Anggota</a>
            <a href="kas.php">Kas</a>
            <a href="trophies.php">Prestasi</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="tabs">
            <div class="tab">
                <div class="count" style="color: #ffc107;"><?= $pending->num_rows ?></div>
                <div>⏳ Pending</div>
            </div>
            <div class="tab">
                <div class="count" style="color: #28a745;"><?= $conn->query("SELECT COUNT(*) as t FROM members WHERE status='approved'")->fetch_assoc()['t'] ?></div>
                <div>✅ Approved</div>
            </div>
            <div class="tab">
                <div class="count" style="color: #dc3545;"><?= $conn->query("SELECT COUNT(*) as t FROM members WHERE status='rejected'")->fetch_assoc()['t'] ?></div>
                <div>❌ Rejected</div>
            </div>
        </div>

        <!-- Pending List -->
        <div class="card">
            <h3>⏳ Menunggu Persetujuan (<?= $pending->num_rows ?>)</h3>
            
            <?php if ($pending->num_rows > 0): ?>
                <?php while ($member = $pending->fetch_assoc()): ?>
                    <div class="member-card">
                        <h4>
                            <?= htmlspecialchars($member['nama']) ?> 
                            <span class="badge badge-pending">Pending</span>
                        </h4>
                        
                        <table>
                            <tr>
                                <td><strong>Tempat, Tanggal Lahir:</strong></td>
                                <td>
                                    <?= htmlspecialchars($member['tempat_lahir'] ?? '-') ?>, 
                                    <?= $member['tanggal_lahir'] ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-' ?> 
                                    (<?= $member['umur'] ?> tahun)
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Gender:</strong></td>
                                <td><?= htmlspecialchars($member['gender']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Posisi:</strong></td>
                                <td><?= htmlspecialchars($member['posisi']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>WhatsApp:</strong></td>
                                <td>
                                    <a href="https://wa.me/62<?= ltrim($member['wa'], '0') ?>" target="_blank">
                                        📱 <?= htmlspecialchars($member['wa']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Alamat:</strong></td>
                                <td><?= htmlspecialchars($member['alamat']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td><?= htmlspecialchars($member['username']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Catatan:</strong></td>
                                <td><?= nl2br(htmlspecialchars($member['catatan'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Daftar:</strong></td>
                                <td><?= date('d M Y H:i', strtotime($member['created_at'])) ?></td>
                            </tr>
                        </table>
                        
                        <div class="actions">
                            <a href="?approve=<?= $member['id'] ?>" class="btn btn-approve" onclick="return confirm('Setujui pendaftaran <?= htmlspecialchars($member['nama']) ?>?')">
                                ✅ Setujui
                            </a>
                            <a href="?reject=<?= $member['id'] ?>" class="btn btn-reject" onclick="return confirm('Tolak pendaftaran <?= htmlspecialchars($member['nama']) ?>?')">
                                ❌ Tolak
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>✅ Tidak ada pendaftaran pending</h3>
                    <p>Semua pendaftaran sudah diproses</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recently Approved -->
        <?php if ($approved->num_rows > 0): ?>
            <div class="card">
                <h3>✅ Baru Disetujui (5 Terakhir)</h3>
                
                <?php while ($member = $approved->fetch_assoc()): ?>
                    <div class="member-card approved">
                        <h4>
                            <?= htmlspecialchars($member['nama']) ?> 
                            <span class="badge badge-approved">Approved</span>
                        </h4>
                        <p>
                            <small>
                                Disetujui: <?= date('d M Y H:i', strtotime($member['approved_at'])) ?>
                                <?= $member['approved_by_name'] ? ' oleh ' . htmlspecialchars($member['approved_by_name']) : '' ?>
                            </small>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- Recently Rejected -->
        <?php if ($rejected->num_rows > 0): ?>
            <div class="card">
                <h3>❌ Baru Ditolak (5 Terakhir)</h3>
                
                <?php while ($member = $rejected->fetch_assoc()): ?>
                    <div class="member-card rejected">
                        <h4>
                            <?= htmlspecialchars($member['nama']) ?> 
                            <span class="badge badge-rejected">Rejected</span>
                        </h4>
                        <p>
                            <small>Ditolak: <?= date('d M Y H:i', strtotime($member['created_at'])) ?></small>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>