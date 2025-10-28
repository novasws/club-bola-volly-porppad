<?php
require_once 'config.php';

// Pastikan user sudah login
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $nama = clean($_POST['nama']);
    $tempat_lahir = clean($_POST['tempat_lahir']);
    $tanggal_lahir = clean($_POST['tanggal_lahir']);
    $umur = (int)$_POST['umur'];
    $gender = clean($_POST['gender']);
    $posisi = clean($_POST['posisi']);
    $wa = clean($_POST['wa']);
    $alamat = clean($_POST['alamat']);
    $alasan = clean($_POST['alasan']);
    $pengalaman = clean($_POST['pengalaman'] ?? '');
    $riwayat_cedera = clean($_POST['riwayat_cedera'] ?? '');
    
    // ========== HANDLE UPLOAD FOTO ==========
    $foto = null;
    $upload_success = false;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $file = $_FILES['foto'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Validasi tipe file dengan mime_content_type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($file_type, $allowed_mime)) {
            alert('❌ Format foto tidak valid! Gunakan JPG, PNG, atau GIF', 'danger');
            redirect('index.php');
            exit();
        }
        
        // Validasi ukuran
        if ($file['size'] > $max_size) {
            alert('❌ Ukuran foto terlalu besar! Maksimal 2MB', 'danger');
            redirect('index.php');
            exit();
        }
        
        // Generate nama file unik
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $foto = 'member_' . $user_id . '_' . time() . '.' . strtolower($extension);
        
        // Buat folder jika belum ada
        $upload_dir = __DIR__ . '/uploads/members/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Upload file
        $destination = $upload_dir . $foto;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $upload_success = true;
            
            // Optional: Resize foto jika terlalu besar
            // resizeImage($destination, 800, 800);
        }
    }
    
    // Jika tidak ada foto atau upload gagal
    if (!$upload_success) {
        alert('❌ Gagal mengupload foto. Pastikan foto valid dan ukuran < 2MB!', 'danger');
        redirect('index.php');
        exit();
    }
    
    // ========== SAVE TO DATABASE ==========
    $catatan = "Alasan: $alasan";
    if (!empty($pengalaman)) {
        $catatan .= " | Pengalaman: $pengalaman";
    }
    if (!empty($riwayat_cedera)) {
        $catatan .= " | Riwayat Cedera: $riwayat_cedera";
    }
    
    $query = "INSERT INTO members (user_id, nama, tempat_lahir, tanggal_lahir, umur, gender, posisi, wa, alamat, foto, catatan, status) 
              VALUES ('$user_id', '$nama', '$tempat_lahir', '$tanggal_lahir', $umur, '$gender', '$posisi', '$wa', '$alamat', '$foto', '$catatan', 'pending')";
    
    if (mysqli_query($conn, $query)) {
        
        // ========== KIRIM KE TELEGRAM ==========
        $telegram_bot_token = "7795312049:AAEk_sZYG6HNVS7gfGaoxUm57KkBgjTWgF0";
        $telegram_chat_id = "6644325326";
        
        // Format pesan
        $message = "🏐 <b>PENDAFTARAN ANGGOTA BARU</b> 🏐\n\n";
        $message .= "👤 <b>Nama:</b> $nama\n";
        $message .= "📅 <b>TTL:</b> $tempat_lahir, $tanggal_lahir ($umur tahun)\n";
        $message .= "⚧ <b>Gender:</b> $gender\n";
        $message .= "🏐 <b>Posisi:</b> $posisi\n";
        $message .= "📱 <b>WhatsApp:</b> $wa\n";
        $message .= "🏠 <b>Alamat:</b> $alamat\n\n";
        $message .= "💬 <b>Alasan:</b>\n$alasan\n\n";
        
        if (!empty($pengalaman)) {
            $message .= "🎯 <b>Pengalaman:</b>\n$pengalaman\n\n";
        }
        
        if (!empty($riwayat_cedera)) {
            $message .= "⚠️ <b>Riwayat Cedera:</b>\n$riwayat_cedera\n\n";
        }
        
        $message .= "👨‍💻 <b>Username:</b> $username\n";
        $message .= "🕐 <b>Waktu:</b> " . date('d M Y H:i:s') . "\n\n";
        $message .= "⏳ <b>Status:</b> Menunggu persetujuan\n\n";
        $message .= "✅ <i>Buka dashboard admin untuk approve/reject</i>";
        
        // Kirim text message
        $telegram_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
        
        $data = [
            'chat_id' => $telegram_chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $telegram_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Kirim foto ke Telegram
        if ($foto && file_exists($destination)) {
            $photo_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendPhoto";
            
            $photo_data = [
                'chat_id' => $telegram_chat_id,
                'photo' => new CURLFile($destination),
                'caption' => "📸 Foto Profil: $nama"
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $photo_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $photo_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            curl_exec($ch);
            curl_close($ch);
        }
        
        // Success message
        if ($httpcode == 200) {
            alert('✅ Pendaftaran berhasil! Data dan foto Anda sedang menunggu persetujuan admin. Kami akan menghubungi Anda segera.', 'success');
        } else {
            alert('✅ Pendaftaran berhasil disimpan! Menunggu persetujuan admin.', 'success');
        }
        
        redirect('home.php');
        
    } else {
        // Hapus foto jika database gagal
        if ($foto && file_exists($destination)) {
            unlink($destination);
        }
        
        alert('❌ Gagal menyimpan data. Silakan coba lagi! Error: ' . mysqli_error($conn), 'danger');
        redirect('home.php');
    }
} else {
    redirect('home.php');
}

// ========== FUNGSI RESIZE IMAGE (OPTIONAL) ==========
function resizeImage($file, $maxWidth, $maxHeight) {
    list($width, $height, $type) = getimagesize($file);
    
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return; // No need to resize
    }
    
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Load source image
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file);
            break;
        default:
            return;
    }
    
    // Resize
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $file, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $file, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($newImage, $file);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($newImage);
}
?>