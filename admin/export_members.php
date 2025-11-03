<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Data_Anggota_PORPPAD_' . date('Y-m-d_His') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// JUDUL DAN HEADER YANG KEREN
fputcsv($output, ['']); // Baris kosong
fputcsv($output, ['🏐 DATA ANGGOTA CLUB BOLA VOLI PORPPAD']); // Judul utama
fputcsv($output, ['Diekspor pada: ' . date('d F Y H:i:s')]); // Tanggal
fputcsv($output, ['']); // Baris kosong

// SUMMARY STATISTICS
$total_members = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE status='approved'")->fetch_assoc()['total'];
$count_putra = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender='Putra' AND status='approved'")->fetch_assoc()['total'];
$count_putri = mysqli_query($conn, "SELECT COUNT(*) as total FROM members WHERE gender='Putri' AND status='approved'")->fetch_assoc()['total'];

fputcsv($output, ['📊 SUMMARY STATISTICS']);
fputcsv($output, ['Total Anggota', 'Tim Putra', 'Tim Putri']);
fputcsv($output, [$total_members, $count_putra, $count_putri]);
fputcsv($output, ['']); // Baris kosong

// HEADER TABLE DENGAN EMOJI
fputcsv($output, [
    'No', '👤 Nama Lengkap', '📍 Tempat Lahir', '📅 Tanggal Lahir', 
    '🎂 Umur', '⚧ Gender', '🎯 Posisi', '📱 WhatsApp', '🏠 Alamat', '⏰ Terdaftar'
]);

// Get data from database
$members = mysqli_query($conn, 
    "SELECT m.*, u.username 
     FROM members m 
     LEFT JOIN users u ON m.user_id = u.id 
     WHERE m.status = 'approved'
     ORDER BY m.created_at DESC"
);

// Data rows
$no = 1;
while ($member = mysqli_fetch_assoc($members)) {
    fputcsv($output, [
        $no++,
        $member['nama'],
        $member['tempat_lahir'] ?? '-',
        $member['tanggal_lahir'] ? date('d-m-Y', strtotime($member['tanggal_lahir'])) : '-',
        $member['umur'],
        $member['gender'],
        $member['posisi'],
        $member['wa'] ?? '-',
        $member['alamat'] ?? '-',
        date('d-m-Y H:i', strtotime($member['created_at']))
    ]);
}

// FOOTER
fputcsv($output, ['']);
fputcsv($output, ['© ' . date('Y') . ' Club Bola Voli PORPPAD - Surabaya']);

fclose($output);
exit;
?>