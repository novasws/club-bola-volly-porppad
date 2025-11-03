<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get photo filename before delete
    $stmt = $conn->prepare("SELECT foto FROM members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
    
    // Delete photo file if exists
    if ($member['foto'] && file_exists("../uploads/members/" . $member['foto'])) {
        unlink("../uploads/members/" . $member['foto']);
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "Anggota berhasil dihapus!";
    } else {
        $error = "Gagal menghapus anggota!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $umur = $_POST['umur'];
    $posisi = $_POST['posisi'];
    $gender = $_POST['gender'];
    $wa = $_POST['wa'];
    $alamat = $_POST['alamat'];
    $id = $_POST['id'] ?? null;
    
    // Handle photo upload
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . '.' . $ext;
            $destination = "../uploads/members/" . $newname;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
                $foto = $newname;
                
                // Delete old photo if editing
                if ($id) {
                    $stmt = $conn->prepare("SELECT foto FROM members WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $old = $result->fetch_assoc();
                    
                    if ($old['foto'] && file_exists("../uploads/members/" . $old['foto'])) {
                        unlink("../uploads/members/" . $old['foto']);
                    }
                }
            }
        }
    }
    
    if ($id) {
        // Update
        if ($foto) {
            $stmt = $conn->prepare("UPDATE members SET nama=?, tempat_lahir=?, tanggal_lahir=?, umur=?, posisi=?, gender=?, wa=?, alamat=?, foto=? WHERE id=?");
            $stmt->bind_param("sssisssssi", $nama, $tempat_lahir, $tanggal_lahir, $umur, $posisi, $gender, $wa, $alamat, $foto, $id);
        } else {
            $stmt = $conn->prepare("UPDATE members SET nama=?, tempat_lahir=?, tanggal_lahir=?, umur=?, posisi=?, gender=?, wa=?, alamat=? WHERE id=?");
            $stmt->bind_param("ssisssssi", $nama, $tempat_lahir, $tanggal_lahir, $umur, $posisi, $gender, $wa, $alamat, $id);
        }
    } else {
        // Insert - LANGSUNG APPROVED karena ditambah admin
        $stmt = $conn->prepare("INSERT INTO members (nama, tempat_lahir, tanggal_lahir, umur, posisi, gender, wa, alamat, foto, status, approved_by, approved_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?, NOW())");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("sssisssssi", $nama, $tempat_lahir, $tanggal_lahir, $umur, $posisi, $gender, $wa, $alamat, $foto, $admin_id);
    }
    
    if ($stmt->execute()) {
        $success = $id ? "Anggota berhasil diupdate!" : "Anggota berhasil ditambahkan dan langsung approved!";
    } else {
        $error = "Gagal menyimpan data!";
    }
}

// Get member for edit
$edit_member = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_member = $stmt->get_result()->fetch_assoc();
}

// Get filter
$gender_filter = $_GET['gender'] ?? '';

// Build query - HANYA TAMPILKAN YANG APPROVED
$query = "SELECT * FROM members WHERE status = 'approved'";
if ($gender_filter) {
    $query .= " AND gender = '$gender_filter'";
}
$query .= " ORDER BY created_at DESC";

$members = $conn->query($query);

// Count by gender - HANYA APPROVED
$count_putra = $conn->query("SELECT COUNT(*) as total FROM members WHERE gender = 'Putra' AND status = 'approved'")->fetch_assoc()['total'] ?? 0;
$count_putri = $conn->query("SELECT COUNT(*) as total FROM members WHERE gender = 'Putri' AND status = 'approved'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f5f5;
        line-height: 1.6;
    }
    
    /* NAVBAR IMPROVED */
    .navbar {
        background: #2c3e50;
        color: white;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .navbar h2 {
        font-size: 1.3rem;
        white-space: nowrap;
    }
    
    .navbar nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .navbar nav a {
        color: white;
        text-decoration: none;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        transition: background 0.3s;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    
    .navbar nav a:hover {
        background: #34495e;
    }
    
    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* GENDER FILTER IMPROVED */
    .gender-filter {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 2rem;
    }
    
    .gender-tab {
        padding: 1rem 0.5rem;
        background: white;
        border: 2px solid #ddd;
        border-radius: 10px;
        text-align: center;
        text-decoration: none;
        color: #555;
        transition: all 0.3s;
        min-height: 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .gender-tab:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .gender-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }
    
    .gender-tab .icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .gender-tab .count {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .card {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .card h3 {
        margin-bottom: 1.5rem;
        color: #2c3e50;
        font-size: 1.4rem;
    }
    
    /* FORM IMPROVED */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #555;
        font-weight: 500;
        font-size: 1rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.8rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .form-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
    }
    
    .btn {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        font-weight: 500;
    }
    
    .btn-primary {
        background: #3498db;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }
    
    .btn-warning {
        background: #f39c12;
        color: white;
    }
    
    .btn-warning:hover {
        background: #e67e22;
        transform: translateY(-2px);
    }
    
    .btn-danger {
        background: #e74c3c;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c0392b;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: #95a5a6;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #7f8c8d;
        transform: translateY(-2px);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    
    table thead {
        background: #34495e;
        color: white;
    }
    
    table th,
    table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .member-photo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
    }
    
    .actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .actions a,
    .actions button {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .badge-gender {
        padding: 0.4rem 0.8rem;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .badge-putra {
        background: #e3f2fd;
        color: #1565c0;
    }
    
    .badge-putri {
        background: #fce4ec;
        color: #c2185b;
    }
    
    /* RESPONSIVE IMPROVEMENTS */
    @media (max-width: 768px) {
        .container {
            padding: 0 0.5rem;
            margin: 1rem auto;
        }
        
        .navbar {
            flex-direction: column;
            text-align: center;
            padding: 0.75rem;
        }
        
        .navbar nav {
            width: 100%;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .navbar nav a {
            width: 100%;
            text-align: center;
            padding: 0.75rem;
            font-size: 1rem;
        }
        
        .card {
            padding: 1.5rem 1rem;
            margin-bottom: 1.5rem;
        }
        
        .card h3 {
            font-size: 1.2rem;
        }
        
        .gender-filter {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .gender-tab {
            min-height: 70px;
            padding: 0.75rem;
        }
        
        .gender-tab .icon {
            font-size: 1.5rem;
        }
        
        .gender-tab .count {
            font-size: 1.25rem;
        }
        
        .form-row, .form-row-3 {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            font-size: 16px;
            padding: 0.75rem;
        }
        
        .btn {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            width: 100%;
            text-align: center;
        }
        
        table {
            font-size: 0.8rem;
            display: block;
            overflow-x: auto;
        }
        
        .member-photo {
            width: 40px;
            height: 40px;
        }
        
        .actions {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .actions a {
            padding: 0.5rem;
            font-size: 0.8rem;
            text-align: center;
        }
    }
    
    @media (max-width: 480px) {
        .navbar h2 {
            font-size: 1.1rem;
        }
        
        .navbar nav a {
            font-size: 0.9rem;
            padding: 0.75rem;
        }
        
        .container {
            padding: 0 0.75rem;
        }
        
        .card {
            padding: 1.25rem 0.75rem;
        }
        
        .card h3 {
            font-size: 1.1rem;
        }
        
        .form-group label {
            font-size: 0.9rem;
        }
        
        .gender-tab {
            min-height: 60px;
        }
        
        table th,
        table td {
            padding: 0.5rem 0.25rem;
        }
        
        .member-photo {
            width: 35px;
            height: 35px;
        }
    }
</style>
</head>
<body>
    <div class="navbar">
        <h2>🏐 Volley Club Admin</h2>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="approval.php">Persetujuan</a>
            <a href="members.php">Anggota</a>
            <a href="kas.php">Kas</a>
            <a href="trophies.php">Prestasi</a>
            <a href="../index.php" style="background: #27ae60;">🏠 Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Gender Filter -->
        <div class="gender-filter">
            <a href="members.php" class="gender-tab <?= $gender_filter === '' ? 'active' : '' ?>">
                <div class="icon">👥</div>
                <div class="count"><?= $count_putra + $count_putri ?></div>
                <div>Semua</div>
            </a>
            <a href="?gender=Putra" class="gender-tab <?= $gender_filter === 'Putra' ? 'active' : '' ?>">
                <div class="icon">👨</div>
                <div class="count"><?= $count_putra ?></div>
                <div>Tim Putra</div>
            </a>
            <a href="?gender=Putri" class="gender-tab <?= $gender_filter === 'Putri' ? 'active' : '' ?>">
                <div class="icon">👩</div>
                <div class="count"><?= $count_putri ?></div>
                <div>Tim Putri</div>
            </a>
        </div>

        <!-- Form Add/Edit -->
        <div class="card">
            <h3><?= $edit_member ? '✏️ Edit Anggota' : '➕ Tambah Anggota Baru' ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_member['id'] ?? '' ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama" required value="<?= $edit_member['nama'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="">-- Pilih Gender --</option>
                            <option value="Putra" <?= ($edit_member['gender'] ?? '') == 'Putra' ? 'selected' : '' ?>>👨 Putra</option>
                            <option value="Putri" <?= ($edit_member['gender'] ?? '') == 'Putri' ? 'selected' : '' ?>>👩 Putri</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Tempat Lahir *</label>
                        <input type="text" name="tempat_lahir" required placeholder="Surabaya" value="<?= $edit_member['tempat_lahir'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Lahir *</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" required value="<?= $edit_member['tanggal_lahir'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Umur *</label>
                        <input type="number" name="umur" id="umur" required readonly value="<?= $edit_member['umur'] ?? '' ?>" style="background: #f0f0f0;">
                        <small style="color: #666;">Otomatis dari tanggal lahir</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Posisi *</label>
                        <select name="posisi" required>
                            <option value="">-- Pilih Posisi --</option>
                            <option value="Setter" <?= ($edit_member['posisi'] ?? '') == 'Setter' ? 'selected' : '' ?>>Setter</option>
                            <option value="Spiker" <?= ($edit_member['posisi'] ?? '') == 'Spiker' ? 'selected' : '' ?>>Spiker</option>
                            <option value="Libero" <?= ($edit_member['posisi'] ?? '') == 'Libero' ? 'selected' : '' ?>>Libero</option>
                            <option value="Blocker" <?= ($edit_member['posisi'] ?? '') == 'Blocker' ? 'selected' : '' ?>>Blocker</option>
                            <option value="Server" <?= ($edit_member['posisi'] ?? '') == 'Server' ? 'selected' : '' ?>>Server</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>WhatsApp</label>
                        <input type="text" name="wa" placeholder="08xxxxxxxxxx" value="<?= $edit_member['wa'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" placeholder="Alamat lengkap" value="<?= $edit_member['alamat'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" name="foto" accept="image/*">
                    <?php if ($edit_member && $edit_member['foto']): ?>
                        <small style="color: #666;">Foto saat ini: <?= $edit_member['foto'] ?></small>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_member ? '💾 Update' : '➕ Tambah & Auto Approve' ?>
                    </button>
                    <?php if ($edit_member): ?>
                        <a href="members.php" class="btn btn-secondary">❌ Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Members -->
        <div class="card">
            <h3>📋 Daftar Anggota Approved (<?= $members->num_rows ?>)</h3>
            
            <?php if ($members->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>Gender</th>
                                <th>Tempat, Tanggal Lahir</th>
                                <th>Umur</th>
                                <th>Posisi</th>
                                <th>WhatsApp</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($member = $members->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if ($member['foto']): ?>
                                            <img src="../uploads/members/<?= $member['foto'] ?>" alt="<?= $member['nama'] ?>" class="member-photo" onerror="this.src='../assets/img/default-profile.jpg'">
                                        <?php else: ?>
                                            <img src="../assets/img/default-profile.jpg" alt="Default" class="member-photo">
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= $member['nama'] ?></strong></td>
                                    <td>
                                        <span class="badge-gender badge-<?= strtolower($member['gender']) ?>">
                                            <?= $member['gender'] == 'Putra' ? '👨' : '👩' ?> <?= $member['gender'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($member['tempat_lahir']) && !empty($member['tanggal_lahir'])) {
                                            echo htmlspecialchars($member['tempat_lahir']) . ', ' . date('d-m-Y', strtotime($member['tanggal_lahir']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?= $member['umur'] ?> th</td>
                                    <td><span style="background: #3498db; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.875rem;"><?= $member['posisi'] ?></span></td>
                                    <td><?= $member['wa'] ?? '-' ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?edit=<?= $member['id'] ?>&gender=<?= $gender_filter ?>" class="btn btn-warning">✏️ Edit</a>
                                            <a href="?delete=<?= $member['id'] ?>&gender=<?= $gender_filter ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus anggota ini?')">🗑️ Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: #999;">Belum ada anggota approved. Approve anggota di menu Persetujuan!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript untuk Auto Calculate Age -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tanggalLahirInput = document.getElementById('tanggal_lahir');
            const umurInput = document.getElementById('umur');
            
            function calculateAge(birthDate) {
                if (!birthDate) return '';
                
                const birth = new Date(birthDate);
                const today = new Date();
                
                let age = today.getFullYear() - birth.getFullYear();
                const monthDiff = today.getMonth() - birth.getMonth();
                
                // Jika bulan sekarang lebih kecil dari bulan lahir, 
                // atau bulan sama tapi tanggal sekarang lebih kecil dari tanggal lahir
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                    age--;
                }
                
                return age;
            }
            
            // Event listener untuk perubahan tanggal lahir
            if (tanggalLahirInput && umurInput) {
                tanggalLahirInput.addEventListener('change', function() {
                    const age = calculateAge(this.value);
                    umurInput.value = age;
                });
                
                // Calculate age on page load if editing
                if (tanggalLahirInput.value) {
                    const age = calculateAge(tanggalLahirInput.value);
                    umurInput.value = age;
                }
            }
        });
    </script>
</body>
</html>