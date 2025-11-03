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

// Add BOM for UTF-8 (biar baca karakter spesial)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row
fputcsv($output, [
    'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir', 'Umur', 
    'Gender', 'Posisi', 'WhatsApp', 'Alamat', 'Terdaftar'
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
while ($member = mysqli_fetch_assoc($members)) {
    fputcsv($output, [
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

fclose($output);
exit;
?>