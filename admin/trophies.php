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
    $stmt = $conn->prepare("SELECT foto FROM trophies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $trophy = $result->fetch_assoc();
    
    // Delete photo file if exists
    if ($trophy['foto'] && file_exists("../uploads/trophies/" . $trophy['foto'])) {
        unlink("../uploads/trophies/" . $trophy['foto']);
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM trophies WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "Prestasi berhasil dihapus!";
    } else {
        $error = "Gagal menghapus prestasi!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal = $_POST['tanggal'];
    $badge = $_POST['badge'];
    $id = $_POST['id'] ?? null;
    
    // Handle photo upload
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . '.' . $ext;
            $destination = "../uploads/trophies/" . $newname;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
                $foto = $newname;
                
                // Delete old photo if editing
                if ($id) {
                    $stmt = $conn->prepare("SELECT foto FROM trophies WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $old = $result->fetch_assoc();
                    
                    if ($old['foto'] && file_exists("../uploads/trophies/" . $old['foto'])) {
                        unlink("../uploads/trophies/" . $old['foto']);
                    }
                }
            }
        }
    }
    
    if ($id) {
        // Update
        if ($foto) {
            $stmt = $conn->prepare("UPDATE trophies SET judul=?, deskripsi=?, tanggal=?, badge=?, foto=? WHERE id=?");
            $stmt->bind_param("sssssi", $judul, $deskripsi, $tanggal, $badge, $foto, $id);
        } else {
            $stmt = $conn->prepare("UPDATE trophies SET judul=?, deskripsi=?, tanggal=?, badge=? WHERE id=?");
            $stmt->bind_param("ssssi", $judul, $deskripsi, $tanggal, $badge, $id);
        }
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO trophies (judul, deskripsi, tanggal, badge, foto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $judul, $deskripsi, $tanggal, $badge, $foto);
    }
    
    if ($stmt->execute()) {
        $success = $id ? "Prestasi berhasil diupdate!" : "Prestasi berhasil ditambahkan!";
    } else {
        $error = "Gagal menyimpan data!";
    }
}

// Get trophy for edit
$edit_trophy = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM trophies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_trophy = $stmt->get_result()->fetch_assoc();
}

// Get all trophies
$trophies = $conn->query("SELECT * FROM trophies ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Prestasi - Admin Panel</title>

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
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
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
    
    /* TROPHY GRID IMPROVED */
    .trophy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .trophy-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .trophy-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .trophy-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
    }
    
    .trophy-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .trophy-content {
        padding: 1.5rem;
    }
    
    .trophy-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-prestasi {
        background: #ffeaa7;
        color: #d63031;
    }
    
    .badge-turnamen {
        background: #74b9ff;
        color: #0984e3;
    }
    
    .badge-kejuaraan {
        background: #a29bfe;
        color: #6c5ce7;
    }
    
    .badge-penghargaan {
        background: #fd79a8;
        color: #e84393;
    }
    
    .trophy-title {
        font-size: 1.25rem;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    
    .trophy-date {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
    }
    
    .trophy-description {
        color: #555;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .trophy-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .trophy-actions a {
        flex: 1;
        text-align: center;
        padding: 0.6rem;
        font-size: 0.875rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    /* RESPONSIVE IMPROVEMENTS */
@media (max-width: 768px) {
    .navbar {
        flex-direction: row;           /* Horizontal, bukan vertikal */
        align-items: center;
        padding: 0.75rem;
        overflow-x: auto;              /* Bisa scroll horizontal */
        white-space: nowrap;
    }
    
    .navbar h2 {
        font-size: 1.2rem;
        margin-right: 1rem;
        flex-shrink: 0;                /* Jangan mengecil */
    }
    
    .navbar nav {
        display: flex;
        flex-wrap: nowrap;             /* Jangan wrap ke bawah */
        gap: 0.5rem;
    }
    
    .navbar nav a {
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
        width: auto;                   /* Lebar sesuai konten */
        flex-shrink: 0;
        white-space: nowrap;
    }
    
    /* Sembunyikan scrollbar */
    .navbar::-webkit-scrollbar {
        display: none;
    }
    .navbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
}
        
        .card {
            padding: 1.5rem 1rem;
            margin-bottom: 1.5rem;
        }
        
        .card h3 {
            font-size: 1.2rem;
        }
        
        .form-row {
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
        
        .trophy-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .trophy-image {
            height: 180px;
        }
        
        .trophy-content {
            padding: 1.25rem;
        }
        
        .trophy-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .trophy-actions a {
            width: 100%;
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
        
        .btn {
            padding: 0.7rem 1.2rem;
            font-size: 0.9rem;
        }
        
        .trophy-title {
            font-size: 1.1rem;
        }
        
        .trophy-image {
            height: 150px;
        }
        
        .empty-state {
            padding: 2rem 1rem;
        }
        
        .empty-state-icon {
            font-size: 3rem;
        }
    }
</style>
</head>
<body>
    <div class="navbar">
        <h2>🏐 Volley Club Admin</h2>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="members.php">Anggota</a>
            <a href="kas.php">Kas</a>
            <a href="trophies.php">Prestasi</a>
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

        <!-- Form Add/Edit -->
        <div class="card">
            <h3><?= $edit_trophy ? '✏️ Edit Prestasi' : '➕ Tambah Prestasi Baru' ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_trophy['id'] ?? '' ?>">
                
                <div class="form-group">
                    <label>Judul Prestasi *</label>
                    <input type="text" name="judul" required placeholder="Contoh: Juara 1 Liga Kampus 2024" value="<?= $edit_trophy['judul'] ?? '' ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal/Bulan *</label>
                        <input type="text" name="tanggal" required placeholder="Contoh: Apr 2024" value="<?= $edit_trophy['tanggal'] ?? '' ?>">
                        <small style="color: #666;">Format: Bulan Tahun (contoh: Apr 2024, Jan 2025)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Badge/Kategori *</label>
                        <select name="badge" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Prestasi" <?= ($edit_trophy['badge'] ?? '') == 'Prestasi' ? 'selected' : '' ?>>🏆 Prestasi</option>
                            <option value="Turnamen" <?= ($edit_trophy['badge'] ?? '') == 'Turnamen' ? 'selected' : '' ?>>⚡ Turnamen</option>
                            <option value="Kejuaraan" <?= ($edit_trophy['badge'] ?? '') == 'Kejuaraan' ? 'selected' : '' ?>>👑 Kejuaraan</option>
                            <option value="Penghargaan" <?= ($edit_trophy['badge'] ?? '') == 'Penghargaan' ? 'selected' : '' ?>>🎖️ Penghargaan</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi *</label>
                    <textarea name="deskripsi" required placeholder="Ceritakan tentang prestasi ini..."><?= $edit_trophy['deskripsi'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Foto Prestasi</label>
                    <input type="file" name="foto" accept="image/*">
                    <?php if ($edit_trophy && $edit_trophy['foto']): ?>
                        <small style="color: #666;">Foto saat ini: <?= $edit_trophy['foto'] ?></small>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_trophy ? '💾 Update' : '➕ Tambah' ?>
                    </button>
                    <?php if ($edit_trophy): ?>
                        <a href="trophies.php" class="btn btn-secondary">❌ Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Trophy Grid -->
        <div class="card">
            <h3>🏆 Koleksi Prestasi (<?= $trophies->num_rows ?>)</h3>
            
            <?php if ($trophies->num_rows > 0): ?>
                <div class="trophy-grid">
                    <?php while ($trophy = $trophies->fetch_assoc()): ?>
                        <div class="trophy-card">
                            <div class="trophy-image">
                                <?php if ($trophy['foto'] && file_exists("../uploads/trophies/" . $trophy['foto'])): ?>
                                    <img src="../uploads/trophies/<?= $trophy['foto'] ?>" alt="<?= $trophy['judul'] ?>">
                                <?php else: ?>
                                    🏆
                                <?php endif; ?>
                            </div>
                            <div class="trophy-content">
                                <span class="trophy-badge badge-<?= strtolower($trophy['badge']) ?>">
                                    <?= $trophy['badge'] ?>
                                </span>
                                <div class="trophy-title"><?= $trophy['judul'] ?></div>
                                <div class="trophy-date">📅 <?= $trophy['tanggal'] ?></div>
                                <div class="trophy-description"><?= $trophy['deskripsi'] ?></div>
                                <div class="trophy-actions">
                                    <a href="?edit=<?= $trophy['id'] ?>" class="btn btn-warning">✏️ Edit</a>
                                    <a href="?delete=<?= $trophy['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus prestasi ini?')">🗑️ Hapus</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🏆</div>
                    <h3>Belum Ada Prestasi</h3>
                    <p>Tambahkan prestasi pertama tim Anda!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>