<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Data_Anggota_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Get all approved members
$query = "SELECT m.*, u.username 
          FROM members m 
          LEFT JOIN users u ON m.user_id = u.id 
          WHERE m.status = 'approved'
          ORDER BY m.created_at DESC";
$members = mysqli_query($conn, $query);

// Calculate total
$total_members = mysqli_num_rows($members);
$count_putra = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender='Putra' AND status='approved'")->fetch_assoc()['total'];
$count_putri = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender='Putri' AND status='approved'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Anggota PORPPAD</title>
    <style>
        table { 
            border-collapse: collapse; 
            width: 100%; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #4472C4; 
            color: white; 
            font-weight: bold; 
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .male { background-color: #E7F3FF; }
        .female { background-color: #FFE7F3; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>📊 DATA ANGGOTA CLUB BOLA VOLI PORPPAD</h2>
        <p>Diekspor pada: <?= date('d F Y H:i:s') ?></p>
    </div>

    <!-- Summary Statistics -->
    <table class="summary">
        <tr>
            <th>Total Anggota</th>
            <th>Tim Putra</th>
            <th>Tim Putri</th>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold; font-size: 18px;"><?= $total_members ?></td>
            <td style="text-align: center; font-weight: bold; font-size: 18px;"><?= $count_putra ?></td>
            <td style="text-align: center; font-weight: bold; font-size: 18px;"><?= $count_putri ?></td>
        </tr>
    </table>

    <br>

    <!-- Members Data -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Tempat Lahir</th>
                <th>Tanggal Lahir</th>
                <th>Umur</th>
                <th>Gender</th>
                <th>Posisi</th>
                <th>WhatsApp</th>
                <th>Alamat</th>
                <th>Username</th>
                <th>Terdaftar</th>
                <th>Disetujui</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($member = mysqli_fetch_assoc($members)): 
                $row_class = $member['gender'] === 'Putra' ? 'male' : 'female';
            ?>
                <tr class="<?= $row_class ?>">
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($member['nama']) ?></td>
                    <td><?= htmlspecialchars($member['tempat_lahir'] ?? '-') ?></td>
                    <td><?= $member['tanggal_lahir'] ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-' ?></td>
                    <td><?= $member['umur'] ?></td>
                    <td><?= $member['gender'] ?></td>
                    <td><?= $member['posisi'] ?></td>
                    <td><?= $member['wa'] ?? '-' ?></td>
                    <td><?= htmlspecialchars($member['alamat'] ?? '-') ?></td>
                    <td><?= $member['username'] ?? '-' ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($member['created_at'])) ?></td>
                    <td><?= $member['approved_at'] ? date('d-m-Y H:i', strtotime($member['approved_at'])) : '-' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>

    <!-- Footer -->
    <p style="text-align: center; color: #666; font-size: 12px;">
        © <?= date('Y') ?> Club Bola Voli PORPPAD - Surabaya
    </p>
</body>
</html>