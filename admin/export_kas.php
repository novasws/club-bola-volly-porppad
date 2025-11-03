<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Data_Kas_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Get all kas transactions
$kas_list = mysqli_query($conn, "SELECT * FROM kas ORDER BY tanggal DESC, created_at DESC");

// Calculate totals
$total_masuk = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pemasukan'")->fetch_assoc()['total'] ?? 0;
$total_keluar = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pengeluaran'")->fetch_assoc()['total'] ?? 0;
$saldo = $total_masuk - $total_keluar;

// Count transactions
$total_transaksi = mysqli_num_rows($kas_list);
$count_masuk = mysqli_query($conn, "SELECT COUNT(*) as total FROM kas WHERE jenis = 'Pemasukan'")->fetch_assoc()['total'];
$count_keluar = mysqli_query($conn, "SELECT COUNT(*) as total FROM kas WHERE jenis = 'Pengeluaran'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Kas PORPPAD</title>
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
        .summary-box {
            background-color: #F2F2F2;
            padding: 10px;
            margin-bottom: 10px;
        }
        .pemasukan { background-color: #E7FFE7; }
        .pengeluaran { background-color: #FFE7E7; }
        .amount-in { color: #27ae60; font-weight: bold; }
        .amount-out { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>💰 LAPORAN KAS CLUB BOLA VOLI PORPPAD</h2>
        <p>Diekspor pada: <?= date('d F Y H:i:s') ?></p>
    </div>

    <!-- Summary Statistics -->
    <table class="summary">
        <tr>
            <th colspan="2" style="background-color: #27ae60;">💰 Total Pemasukan</th>
            <th colspan="2" style="background-color: #e74c3c;">💸 Total Pengeluaran</th>
            <th colspan="2" style="background-color: #3498db;">💵 Saldo</th>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold; font-size: 18px; color: #27ae60;">
                Rp <?= number_format($total_masuk, 0, ',', '.') ?>
            </td>
            <td style="text-align: center;"><?= $count_masuk ?> transaksi</td>
            <td style="text-align: center; font-weight: bold; font-size: 18px; color: #e74c3c;">
                Rp <?= number_format($total_keluar, 0, ',', '.') ?>
            </td>
            <td style="text-align: center;"><?= $count_keluar ?> transaksi</td>
            <td style="text-align: center; font-weight: bold; font-size: 18px; color: #3498db;">
                Rp <?= number_format($saldo, 0, ',', '.') ?>
            </td>
            <td style="text-align: center;"><?= $total_transaksi ?> total</td>
        </tr>
    </table>

    <br>

    <!-- Transactions Data -->
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
            <?php 
            $no = 1;
            while ($kas = mysqli_fetch_assoc($kas_list)): 
                $row_class = $kas['jenis'] === 'Pemasukan' ? 'pemasukan' : 'pengeluaran';
                $amount_class = $kas['jenis'] === 'Pemasukan' ? 'amount-in' : 'amount-out';
                $prefix = $kas['jenis'] === 'Pemasukan' ? '+' : '-';
            ?>
                <tr class="<?= $row_class ?>">
                    <td><?= $no++ ?></td>
                    <td><?= date('d-m-Y', strtotime($kas['tanggal'])) ?></td>
                    <td><?= $kas['jenis'] ?></td>
                    <td><?= htmlspecialchars($kas['nama'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($kas['deskripsi']) ?></td>
                    <td class="<?= $amount_class ?>">
                        <?= $prefix ?> Rp <?= number_format($kas['jumlah'], 0, ',', '.') ?>
                    </td>
                    <td><?= date('d-m-Y H:i', strtotime($kas['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #F2F2F2; font-weight: bold;">
                <td colspan="5" style="text-align: right;">TOTAL SALDO:</td>
                <td colspan="2" style="font-size: 16px; color: <?= $saldo >= 0 ? '#27ae60' : '#e74c3c' ?>;">
                    Rp <?= number_format($saldo, 0, ',', '.') ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <br>

    <!-- Footer -->
    <p style="text-align: center; color: #666; font-size: 12px;">
        © <?= date('Y') ?> Club Bola Voli PORPPAD - Surabaya
    </p>
</body>
</html>


<?php