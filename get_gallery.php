<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Query untuk ambil semua foto galeri
    $query = "SELECT * FROM gallery ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Database query failed");
    }
    
    $photos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $photos[] = [
            'id' => $row['id'],
            'filename' => $row['filename'],
            'caption' => $row['caption'] ?? '',
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'photos' => $photos,
        'total' => count($photos)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>