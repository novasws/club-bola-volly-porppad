<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Data_Kas_PORPPAD_' . date('Y-m-d_His') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// JUDUL DAN HEADER YANG KEREN
fputcsv($output, ['']); // Baris kosong
fputcsv($output, ['💰 LAPORAN KAS CLUB BOLA VOLI PORPPAD']); // Judul utama
fputcsv($output, ['Diekspor pada: ' . date('d F Y H:i:s')]); // Tanggal
fputcsv($output, ['']); // Baris kosong

// SUMMARY STATISTICS
$total_masuk = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pemasukan'")->fetch_assoc()['total'] ?? 0;
$total_keluar = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pengeluaran'")->fetch_assoc()['total'] ?? 0;
$saldo = $total_masuk - $total_keluar;

fputcsv($output, ['📊 SUMMARY STATISTICS']);
fputcsv($output, ['💰 Total Pemasukan', '💸 Total Pengeluaran', '💵 Saldo']);
fputcsv($output, [
    'Rp ' . number_format($total_masuk, 0, ',', '.'),
    'Rp ' . number_format($total_keluar, 0, ',', '.'),
    'Rp ' . number_format($saldo, 0, ',', '.')
]);
fputcsv($output, ['']); // Baris kosong

// HEADER TABLE DENGAN EMOJI
fputcsv($output, [
    'No', '📅 Tanggal', '💳 Jenis', '👤 Nama', '📝 Deskripsi', '💰 Jumlah (Rp)', '⏰ Dicatat'
]);

// Get data from database
$kas_list = mysqli_query($conn, "SELECT * FROM kas ORDER BY tanggal DESC, created_at DESC");

// Data rows
$no = 1;
while ($kas = mysqli_fetch_assoc($kas_list)) {
    $jenis_emoji = $kas['jenis'] == 'Pemasukan' ? '💰' : '💸';
    
    fputcsv($output, [
        $no++,
        date('d-m-Y', strtotime($kas['tanggal'])),
        $jenis_emoji . ' ' . $kas['jenis'],
        $kas['nama'] ?? '-',
        $kas['deskripsi'],
        'Rp ' . number_format($kas['jumlah'], 0, ',', '.'),
        date('d-m-Y H:i', strtotime($kas['created_at']))
    ]);
}

// FOOTER
fputcsv($output, ['']);
fputcsv($output, ['© ' . date('Y') . ' Club Bola Voli PORPPAD - Surabaya']);

fclose($output);
exit;
?>