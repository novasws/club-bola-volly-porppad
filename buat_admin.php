<?php
require_once 'config.php';

// Security key
if (!isset($_GET['key']) || $_GET['key'] !== 'porppad123') {
    die('❌ Access Denied! URL harus: buat_admin.php?key=porppad123');
}

echo "<h2>🔐 Buat Admin Manual</h2>";

// Cek apakah sudah ada admin
$check = mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin'");
$admin_count = mysqli_num_rows($check);

if ($admin_count > 0) {
    echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;margin-bottom:20px;'>";
    echo "⚠️ <strong>Sudah ada {$admin_count} admin:</strong><br>";
    while ($admin = mysqli_fetch_assoc($check)) {
        echo "- Username: <strong>{$admin['username']}</strong> | Nama: {$admin['nama']}<br>";
    }
    echo "</div>";
}

// Form buat admin
if (!isset($_POST['create'])) {
?>
    <form method="POST" style="max-width:400px;background:#f8f9fa;padding:30px;border-radius:10px;">
        <div style="margin-bottom:15px;">
            <label style="display:block;font-weight:bold;margin-bottom:5px;">Username Admin:</label>
            <input type="text" name="username" value="adminporppad" required 
                   style="width:100%;padding:10px;border:2px solid #ddd;border-radius:5px;font-size:16px;">
        </div>
        
        <div style="margin-bottom:15px;">
            <label style="display:block;font-weight:bold;margin-bottom:5px;">Password:</label>
            <input type="text" name="password" value="admin123" required 
                   style="width:100%;padding:10px;border:2px solid #ddd;border-radius:5px;font-size:16px;">
        </div>
        
        <div style="margin-bottom:20px;">
            <label style="display:block;font-weight:bold;margin-bottom:5px;">Nama Lengkap:</label>
            <input type="text" name="nama" value="Admin PORPPAD" required 
                   style="width:100%;padding:10px;border:2px solid #ddd;border-radius:5px;font-size:16px;">
        </div>
        
        <button type="submit" name="create" 
                style="width:100%;padding:12px;background:#28a745;color:white;border:none;border-radius:5px;font-size:16px;font-weight:bold;cursor:pointer;">
            ✅ BUAT ADMIN SEKARANG
        </button>
    </form>
<?php
} else {
    // Proses buat admin
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    $nama = clean($_POST['nama']);
    
    // Cek username sudah ada atau belum
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($check_user) > 0) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:5px;margin-top:20px;'>";
        echo "❌ <strong>Username sudah dipakai!</strong> Ganti username lain.";
        echo "</div>";
    } else {
        // Hash password dengan bcrypt
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert admin baru
        $insert = "INSERT INTO users (username, password, nama, role) 
                   VALUES ('$username', '$hashed', '$nama', 'admin')";
        
        if (mysqli_query($conn, $insert)) {
            echo "<div style='background:#d4edda;color:#155724;padding:30px;border-radius:10px;margin-top:20px;'>";
            echo "<h3>✅ ADMIN BERHASIL DIBUAT!</h3>";
            echo "<table style='width:100%;margin-top:15px;'>";
            echo "<tr><td style='padding:8px;border-bottom:1px solid #ccc;'><strong>Username:</strong></td><td style='padding:8px;border-bottom:1px solid #ccc;'>$username</td></tr>";
            echo "<tr><td style='padding:8px;border-bottom:1px solid #ccc;'><strong>Password:</strong></td><td style='padding:8px;border-bottom:1px solid #ccc;'>$password</td></tr>";
            echo "<tr><td style='padding:8px;'><strong>Nama:</strong></td><td style='padding:8px;'>$nama</td></tr>";
            echo "</table>";
            echo "<div style='margin-top:20px;'>";
            echo "<a href='index.php' style='background:#007bff;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;font-weight:bold;'>→ LOGIN SEKARANG</a>";
            echo "</div>";
            echo "</div>";
            
            echo "<hr style='margin:30px 0;'>";
            echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;'>";
            echo "⚠️ <strong>PENTING:</strong> Hapus file <code>buat_admin.php</code> setelah selesai untuk keamanan!";
            echo "</div>";
        } else {
            echo "<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:5px;margin-top:20px;'>";
            echo "❌ <strong>Error:</strong> " . mysqli_error($conn);
            echo "</div>";
        }
    }
}
?>