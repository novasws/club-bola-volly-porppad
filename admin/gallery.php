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
    
    // Get filename before delete
    $stmt = $conn->prepare("SELECT filename FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $photo = $result->fetch_assoc();
    
    // Delete file if exists
    if ($photo['filename'] && file_exists("../uploads/gallery/" . $photo['filename'])) {
        unlink("../uploads/gallery/" . $photo['filename']);
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "✅ Foto berhasil dihapus!";
    } else {
        $error = "❌ Gagal menghapus foto!";
    }
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    $caption = $_POST['caption'] ?? '';
    $uploaded = 0;
    $failed = 0;
    
    // Create folder if not exists
    $upload_dir = "../uploads/gallery/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Process multiple files
    $files = $_FILES['photos'];
    $total_files = count($files['name']);
    
    for ($i = 0; $i < $total_files; $i++) {
        if ($files['error'][$i] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $files['name'][$i];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newname = 'gallery_' . time() . '_' . $i . '.' . $ext;
                $destination = $upload_dir . $newname;
                
                if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                    // Save to database
                    $stmt = $conn->prepare("INSERT INTO gallery (filename, caption) VALUES (?, ?)");
                    $stmt->bind_param("ss", $newname, $caption);
                    
                    if ($stmt->execute()) {
                        $uploaded++;
                    } else {
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            } else {
                $failed++;
            }
        }
    }
    
    if ($uploaded > 0) {
        $success = "✅ Berhasil upload $uploaded foto!";
    }
    if ($failed > 0) {
        $error = "⚠️ $failed foto gagal diupload!";
    }
}

// Get all photos
$photos = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Admin Panel</title>
    <style>
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
            max-width: 1400px;
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
        
        /* GALLERY UPLOAD IMPROVED */
        .upload-area {
            border: 3px dashed #3498db;
            border-radius: 10px;
            padding: 2rem 1rem;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .upload-area:hover {
            background: #e9ecef;
            border-color: #2980b9;
        }
        
        .upload-area.dragover {
            background: #d4edda;
            border-color: #28a745;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        
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
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* GALLERY GRID IMPROVED */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            aspect-ratio: 1;
        }
        
        .gallery-item:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #ddd;
        }
        
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .preview-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        /* RESPONSIVE IMPROVEMENTS */
        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }
            
            .card {
                padding: 1.5rem 1rem;
            }
            
            .upload-area {
                padding: 1.5rem 1rem;
                min-height: 120px;
            }
            
            .upload-icon {
                font-size: 2.5rem;
            }
            
            .navbar {
                flex-direction: column;
                text-align: center;
            }
            
            .navbar nav {
                width: 100%;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .preview-container {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .navbar nav a {
                font-size: 0.8rem;
                padding: 0.5rem 0.8rem;
            }
            
            .btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .card h3 {
                font-size: 1.2rem;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 0.75rem;
            }
            
            .stat-card .number {
                font-size: 1.5rem;
            }
        }
    </style>
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🏐 PORPPAD Admin</h2>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="approval.php">Persetujuan</a>
            <a href="members.php">Anggota</a>
            <a href="kas.php">Kas</a>
            <a href="trophies.php">Prestasi</a>
            <a href="gallery.php" style="background: #34495e;">📸 Galeri</a>
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

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="number"><?= $photos->num_rows ?></div>
                <div>Total Foto</div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="card">
            <h3>📤 Upload Foto Baru</h3>
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-area" id="uploadArea" onclick="document.getElementById('photoInput').click()">
                    <div class="upload-icon">📷</div>
                    <h4>Klik atau Drag & Drop Foto</h4>
                    <p style="color: #666; margin-top: 0.5rem;">JPG, PNG, GIF (Multiple files allowed)</p>
                    <input type="file" 
                           name="photos[]" 
                           id="photoInput" 
                           multiple 
                           accept="image/*" 
                           style="display: none;" 
                           onchange="previewFiles(this.files)">
                </div>
                
                <div id="previewContainer" class="preview-container" style="display: none;"></div>
                
                <div class="form-group" style="margin-top: 1.5rem;">
                    <label>Caption (Optional)</label>
                    <textarea name="caption" 
                              rows="3" 
                              placeholder="Tambahkan caption untuk semua foto..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" id="uploadBtn" style="display: none;">
                    📤 Upload Sekarang
                </button>
            </form>
        </div>

        <!-- Gallery Grid -->
        <div class="card">
            <h3>🖼️ Galeri Foto (<?= $photos->num_rows ?>)</h3>
            
            <?php if ($photos->num_rows > 0): ?>
                <div class="gallery-grid">
                    <?php while ($photo = $photos->fetch_assoc()): ?>
                        <div class="gallery-item">
                            <img src="../uploads/gallery/<?= $photo['filename'] ?>" 
                                 alt="<?= htmlspecialchars($photo['caption'] ?? 'Gallery') ?>"
                                 onerror="this.src='https://via.placeholder.com/300'">
                            <div class="gallery-overlay">
                                <a href="?delete=<?= $photo['id'] ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus foto ini?')">
                                    🗑️ Hapus
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>📷 Belum Ada Foto</h3>
                    <p>Upload foto pertama untuk galeri klub Anda!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let selectedFiles = [];
        
        // Drag & Drop functionality
        const uploadArea = document.getElementById('uploadArea');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            document.getElementById('photoInput').files = files;
            previewFiles(files);
        });
        
        // Preview files
        function previewFiles(files) {
            selectedFiles = Array.from(files);
            const container = document.getElementById('previewContainer');
            const uploadBtn = document.getElementById('uploadBtn');
            
            container.innerHTML = '';
            
            if (selectedFiles.length > 0) {
                container.style.display = 'flex';
                uploadBtn.style.display = 'inline-block';
                
                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    
                    reader.onload = (e) => {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="preview-remove" onclick="removeFile(${index})">
                                ×
                            </button>
                        `;
                        container.appendChild(div);
                    };
                    
                    reader.readAsDataURL(file);
                });
            } else {
                container.style.display = 'none';
                uploadBtn.style.display = 'none';
            }
        }
        
        // Remove file from preview
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            
            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            document.getElementById('photoInput').files = dataTransfer.files;
            
            previewFiles(selectedFiles);
        }
    </script>
</body>
</html>