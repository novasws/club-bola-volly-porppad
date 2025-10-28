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
    $stmt = $conn->prepare("DELETE FROM kas WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "Data kas berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data kas!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = $_POST['jenis'];
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE kas SET jenis=?, nama=?, deskripsi=?, jumlah=?, tanggal=? WHERE id=?");
        $stmt->bind_param("sssisi", $jenis, $nama, $deskripsi, $jumlah, $tanggal, $id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO kas (jenis, nama, deskripsi, jumlah, tanggal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $jenis, $nama, $deskripsi, $jumlah, $tanggal);
    }
    
    if ($stmt->execute()) {
        $success = $id ? "Data kas berhasil diupdate!" : "Data kas berhasil ditambahkan!";
    } else {
        $error = "Gagal menyimpan data!";
    }
}

// Get kas for edit
$edit_kas = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM kas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_kas = $stmt->get_result()->fetch_assoc();
}

// Get all kas with summary
$kas_list = $conn->query("SELECT * FROM kas ORDER BY tanggal DESC, created_at DESC");

// Calculate totals
$total_masuk = $conn->query("SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pemasukan'")->fetch_assoc()['total'] ?? 0;
$total_keluar = $conn->query("SELECT SUM(jumlah) as total FROM kas WHERE jenis = 'Pengeluaran'")->fetch_assoc()['total'] ?? 0;
$saldo = $total_masuk - $total_keluar;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
       <meta name="viewport" content="width=1200">
    <title>Kelola Kas - Admin Panel</title>
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
        }
        
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar h2 {
            font-size: 1.5rem;
        }
        
        .navbar nav a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h4 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .amount {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .stat-card.masuk .amount {
            color: #27ae60;
        }
        
        .stat-card.keluar .amount {
            color: #e74c3c;
        }
        
        .stat-card.saldo .amount {
            color: #3498db;
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
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e67e22;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #34495e;
            color: white;
        }
        
        table th,
        table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .badge-masuk {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-keluar {
            background: #f8d7da;
            color: #721c24;
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
        
        .amount-positive {
            color: #27ae60;
            font-weight: bold;
        }
        
        .amount-negative {
            color: #e74c3c;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .navbar nav {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            table {
                font-size: 0.875rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .stats {
                grid-template-columns: 1fr;
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

        <!-- Summary Stats -->
        <div class="stats">
            <div class="stat-card masuk">
                <h4>💰 Total Pemasukan</h4>
                <div class="amount">Rp <?= number_format($total_masuk, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card keluar">
                <h4>💸 Total Pengeluaran</h4>
                <div class="amount">Rp <?= number_format($total_keluar, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card saldo">
                <h4>💵 Saldo</h4>
                <div class="amount">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- Form Add/Edit -->
        <div class="card">
            <h3><?= $edit_kas ? '✏️ Edit Data Kas' : '➕ Tambah Data Kas' ?></h3>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit_kas['id'] ?? '' ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis Transaksi *</label>
                        <select name="jenis" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Pemasukan" <?= ($edit_kas['jenis'] ?? '') == 'Pemasukan' ? 'selected' : '' ?>>💰 Pemasukan</option>
                            <option value="Pengeluaran" <?= ($edit_kas['jenis'] ?? '') == 'Pengeluaran' ? 'selected' : '' ?>>💸 Pengeluaran</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="tanggal" required value="<?= $edit_kas['tanggal'] ?? date('Y-m-d') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nama Anggota *</label>
                    <input type="text" name="nama" required placeholder="Nama anggota yang bayar/terima" value="<?= $edit_kas['nama'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Jumlah (Rp) *</label>
                    <input type="number" name="jumlah" required placeholder="50000" value="<?= $edit_kas['jumlah'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Deskripsi *</label>
                    <textarea name="deskripsi" required placeholder="Contoh: Iuran bulanan anggota, Pembelian bola, dll..."><?= $edit_kas['deskripsi'] ?? '' ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_kas ? '💾 Update' : '➕ Tambah' ?>
                    </button>
                    <?php if ($edit_kas): ?>
                        <a href="kas.php" class="btn btn-secondary">❌ Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Kas -->
        <div class="card">
            <h3>📊 Riwayat Transaksi Kas (<?= $kas_list->num_rows ?>)</h3>
            
            <?php if ($kas_list->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($kas = $kas_list->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($kas['tanggal'])) ?></td>
                                <td>
                                    <span class="badge <?= $kas['jenis'] == 'Pemasukan' ? 'badge-masuk' : 'badge-keluar' ?>">
                                        <?= $kas['jenis'] == 'Pemasukan' ? '💰' : '💸' ?> <?= $kas['jenis'] ?>
                                    </span>
                                </td>
                                <td><strong><?= $kas['nama'] ?? '-' ?></strong></td>
                                <td><?= $kas['deskripsi'] ?></td>
                                <td>
                                    <span class="<?= $kas['jenis'] == 'Pemasukan' ? 'amount-positive' : 'amount-negative' ?>">
                                        <?= $kas['jenis'] == 'Pemasukan' ? '+' : '-' ?> Rp <?= number_format($kas['jumlah'], 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="?edit=<?= $kas['id'] ?>" class="btn btn-warning">✏️ Edit</a>
                                        <a href="?delete=<?= $kas['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus data kas ini?')">🗑️ Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: #999;">Belum ada data kas. Tambahkan transaksi pertama!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>