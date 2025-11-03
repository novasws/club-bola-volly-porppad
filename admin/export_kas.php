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

// Header row
fputcsv($output, [
    'Tanggal', 'Jenis', 'Nama', 'Deskripsi', 'Jumlah (Rp)', 'Dicatat'
]);

// Get data from database
$kas_list = mysqli_query($conn, "SELECT * FROM kas ORDER BY tanggal DESC, created_at DESC");

// Data rows
while ($kas = mysqli_fetch_assoc($kas_list)) {
    fputcsv($output, [
        date('d-m-Y', strtotime($kas['tanggal'])),
        $kas['jenis'],
        $kas['nama'] ?? '-',
        $kas['deskripsi'],
        $kas['jumlah'],
        date('d-m-Y H:i', strtotime($kas['created_at']))
    ]);
}

fclose($output);
exit;
?>