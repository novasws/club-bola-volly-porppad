<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Create HTML table dengan styling
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Anggota PORPPAD</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { 
            text-align: center; 
            background: #2c3e50; 
            color: white; 
            padding: 20px; 
            border-radius: 10px 10px 0 0;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        th { 
            background: #34495e; 
            color: white; 
            padding: 12px; 
            text-align: left;
            border: 1px solid #2c3e50;
        }
        td { 
            padding: 10px; 
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .summary {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏐 DATA ANGGOTA CLUB BOLA VOLI PORPPAD</h1>
        <p>Diekspor pada: ' . date('d F Y H:i:s') . '</p>
    </div>
';

// Get summary statistics
$total_members = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE status='approved'")->fetch_assoc()['total'];
$count_putra = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender='Putra' AND status='approved'")->fetch_assoc()['total'];
$count_putri = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender='Putri' AND status='approved'")->fetch_assoc()['total'];

$html .= '
    <div class="summary">
        <h3>📊 SUMMARY STATISTICS</h3>
        <p><strong>Total Anggota:</strong> ' . $total_members . ' | 
           <strong>Tim Putra:</strong> ' . $count_putra . ' | 
           <strong>Tim Putri:</strong> ' . $count_putri . '</p>
    </div>

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
                <th>Terdaftar</th>
            </tr>
        </thead>
        <tbody>
';

// Get data from database
$members = mysqli_query($conn, 
    "SELECT m.*, u.username 
     FROM members m 
     LEFT JOIN users u ON m.user_id = u.id 
     WHERE m.status = 'approved'
     ORDER BY m.created_at DESC"
);

$no = 1;
while ($member = mysqli_fetch_assoc($members)) {
    $html .= '
            <tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($member['nama']) . '</td>
                <td>' . htmlspecialchars($member['tempat_lahir'] ?? '-') . '</td>
                <td>' . ($member['tanggal_lahir'] ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-') . '</td>
                <td>' . $member['umur'] . '</td>
                <td>' . $member['gender'] . '</td>
                <td>' . $member['posisi'] . '</td>
                <td>' . ($member['wa'] ?? '-') . '</td>
                <td>' . htmlspecialchars($member['alamat'] ?? '-') . '</td>
                <td>' . date('d-m-Y H:i', strtotime($member['created_at'])) . '</td>
            </tr>
    ';
}

$html .= '
        </tbody>
    </table>
    
    <div class="footer">
        <p>© ' . date('Y') . ' Club Bola Voli PORPPAD - Surabaya</p>
    </div>
</body>
</html>
';

// Set headers untuk download sebagai Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Data_Anggota_PORPPAD_' . date('Y-m-d_His') . '.xls"');

echo $html;
exit;
?>