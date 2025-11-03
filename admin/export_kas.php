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
    <title>Laporan Kas PORPPAD</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { 
            text-align: center; 
            background: #27ae60; 
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
            background: #2ecc71; 
            color: white; 
            padding: 12px; 
            text-align: left;
            border: 1px solid #27ae60;
        }
        td { 
            padding: 10px; 
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .income { color: #27ae60; font-weight: bold; }
        .expense { color: #e74c3c; font-weight: bold; }
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
        <h1>💰 LAPORAN KAS CLUB BOLA VOLI PORPPAD</h1>
        <p>Diekspor pada: ' . date('d F Y H:i:s') . '</p>
    </div>
';

// Get summary statistics
$total_masuk = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pemasukan'")->fetch_assoc()['total'] ?? 0;
$total_keluar = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pengeluaran'")->fetch_assoc()['total'] ?? 0;
$saldo = $total_masuk - $total_keluar;

$html .= '
    <div class="summary">
        <h3>📊 SUMMARY STATISTICS</h3>
        <p><strong>Total Pemasukan:</strong> Rp ' . number_format($total_masuk, 0, ',', '.') . ' | 
           <strong>Total Pengeluaran:</strong> Rp ' . number_format($total_keluar, 0, ',', '.') . ' | 
           <strong>Saldo:</strong> Rp ' . number_format($saldo, 0, ',', '.') . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Jumlah (Rp)</th>
                <th>Dicatat</th>
            </tr>
        </thead>
        <tbody>
';

// Get data from database
$kas_list = mysqli_query($conn, "SELECT * FROM kas ORDER BY tanggal DESC, created_at DESC");

$no = 1;
while ($kas = mysqli_fetch_assoc($kas_list)) {
    $amount_class = $kas['jenis'] == 'Pemasukan' ? 'income' : 'expense';
    $amount_prefix = $kas['jenis'] == 'Pemasukan' ? '+' : '-';
    
    $html .= '
            <tr>
                <td>' . $no++ . '</td>
                <td>' . date('d-m-Y', strtotime($kas['tanggal'])) . '</td>
                <td>' . $kas['jenis'] . '</td>
                <td>' . ($kas['nama'] ?? '-') . '</td>
                <td>' . htmlspecialchars($kas['deskripsi']) . '</td>
                <td class="' . $amount_class . '">' . $amount_prefix . ' Rp ' . number_format($kas['jumlah'], 0, ',', '.') . '</td>
                <td>' . date('d-m-Y H:i', strtotime($kas['created_at'])) . '</td>
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
header('Content-Disposition: attachment; filename="Data_Kas_PORPPAD_' . date('Y-m-d_His') . '.xls"');

echo $html;
exit;
?>