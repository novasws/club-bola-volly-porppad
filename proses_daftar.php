<?php
require_once 'config.php';

// ========== CRITICAL: Pastikan user sudah login ==========
if (!isLoggedIn()) {
    alert('❌ Anda harus login terlebih dahulu untuk mendaftar!', 'danger');
    redirect('login_user.php');
    exit();
}

// ========== CEK: Apakah user sudah pernah daftar? ==========
$user_id = $_SESSION['user_id'];
$check_existing = mysqli_query($conn, "SELECT * FROM members WHERE user_id = '$user_id'");

if (mysqli_num_rows($check_existing) > 0) {
    alert('⚠️ Anda sudah terdaftar sebagai anggota!', 'warning');
    redirect('index.php');
    exit();
}

// ========== PROSES FORM ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
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
    
    // ========== HANDLE UPLOAD FOTO - IMPROVED ==========
    $foto = null;
    $upload_success = false;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $allowed_mime = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB (dinaikkan dari 2MB)
        
        // Validasi ukuran
        if ($file['size'] > $max_size) {
            alert('❌ Ukuran foto terlalu besar! Maksimal 5MB', 'danger');
            redirect('index.php#daftar');
            exit();
        }
        
        // Validasi tipe file menggunakan extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowed_ext)) {
            alert('❌ Format foto tidak valid! Gunakan JPG, PNG, atau GIF', 'danger');
            redirect('index.php#daftar');
            exit();
        }
        
        // Generate nama file unik
        $foto = 'member_' . $user_id . '_' . time() . '.' . $extension;
        
        // Buat folder jika belum ada
        $upload_dir = __DIR__ . '/uploads/members/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Upload file
        $destination = $upload_dir . $foto;
        
        // PENTING: Move langsung tanpa delay
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $upload_success = true;
            
            // Verify file exists
            if (!file_exists($destination)) {
                $upload_success = false;
                $foto = null;
            }
        } else {
            $foto = null;
        }
    } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Ada error upload
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension'
        ];
        
        $error_code = $_FILES['foto']['error'];
        $error_msg = $error_messages[$error_code] ?? 'Error tidak diketahui';
        
        alert('❌ Gagal upload foto: ' . $error_msg, 'danger');
        redirect('index.php#daftar');
        exit();
    }
    
    // Jika tidak ada foto atau upload gagal
    if (!$upload_success) {
        alert('❌ Gagal mengupload foto. Pastikan foto valid dan ukuran < 5MB!', 'danger');
        redirect('index.php#daftar');
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
    
    $query = "INSERT INTO members (user_id, nama, tempat_lahir, tanggal_lahir, umur, gender, posisi, wa, alamat, foto, catatan, status, created_at) 
              VALUES ('$user_id', '$nama', '$tempat_lahir', '$tanggal_lahir', $umur, '$gender', '$posisi', '$wa', '$alamat', '$foto', '$catatan', 'pending', NOW())";
    
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        curl_exec($ch);
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            curl_exec($ch);
            curl_close($ch);
        }
        
        // Success message
        alert('✅ Pendaftaran berhasil! Data Anda sedang menunggu persetujuan admin. Kami akan menghubungi Anda segera via WhatsApp.', 'success');
        redirect('index.php');
        
    } else {
        // Hapus foto jika database gagal
        if ($foto && file_exists($destination)) {
            unlink($destination);
        }
        
        alert('❌ Gagal menyimpan data. Silakan coba lagi! Error: ' . mysqli_error($conn), 'danger');
        redirect('index.php#daftar');
    }
    
} else {
    // Jika bukan POST request
    redirect('index.php');
}
?>