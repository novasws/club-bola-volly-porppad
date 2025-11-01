<?php
require_once 'config.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

$error = '';
$success = '';

// Handle Login
if (isset($_POST['login'])) {
    $username = clean($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password - Support both MD5 (admin lama) and bcrypt (member baru)
            $password_valid = false;
            
            // Cek apakah password pakai MD5 (32 karakter) atau bcrypt (60 karakter)
            if (strlen($user['password']) === 32) {
                // Password pakai MD5 (admin lama)
                $password_valid = (md5($password) === $user['password']);
            } else {
                // Password pakai bcrypt (member baru)
                $password_valid = password_verify($password, $user['password']);
            }
            
            if ($password_valid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                
                alert('Login berhasil! Selamat datang, ' . $user['username'], 'success');
                redirect('home.php');
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}

// Handle Register
if (isset($_POST['register'])) {
    $nama = clean($_POST['reg_nama']);
    $username = clean($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];

    if (empty($nama) || empty($username) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } else {
        // Check if username exists
        $check = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) > 0) {
            $error = 'Username sudah terdaftar!';
        } else {
            // Pakai bcrypt untuk member baru (lebih aman)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert = "INSERT INTO users (username, password, nama, role) 
                      VALUES ('$username', '$hashed_password', '$nama', 'member')";
            
            if (mysqli_query($conn, $insert)) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Gagal mendaftar. Coba lagi!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login / Register - Volley Club PORPPAD</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    
   <style>
    :root {
        --primary-dark: #1a1a2e;
        --primary-blue: #0f3460;
        --accent-red: #e94560;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        position: relative;
        overflow: hidden;
    }

    /* Animated Background Circles */
    body::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(233, 69, 96, 0.15), transparent);
        border-radius: 50%;
        top: -250px;
        right: -250px;
        animation: pulse 8s ease-in-out infinite;
    }

    body::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(15, 52, 96, 0.2), transparent);
        border-radius: 50%;
        bottom: -200px;
        left: -200px;
        animation: pulse 10s ease-in-out infinite reverse;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .login-card {
        max-width: 480px;
        width: 100%;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        position: relative;
        z-index: 10;
        overflow: hidden;
    }

    /* Top Gradient Line Animation */
    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #e94560, #0f3460, #e94560);
        background-size: 200% 100%;
        animation: gradient-shift 3s linear infinite;
    }

    @keyframes gradient-shift {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }

    .login-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
        color: white;
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .login-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1), transparent);
        animation: rotate 15s linear infinite;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .login-header h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        position: relative;
        z-index: 2;
        color: white;
    }

    .login-header p {
        font-size: 1rem;
        opacity: 0.9;
        position: relative;
        z-index: 2;
        margin: 0;
    }

    /* Tabs */
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 2rem;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        padding: 1rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s;
        position: relative;
    }

    .nav-tabs .nav-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #e94560, #0f3460);
        transition: width 0.3s;
    }

    .nav-tabs .nav-link:hover {
        color: #0f3460;
    }

    .nav-tabs .nav-link.active {
        color: #e94560;
        background: transparent;
        font-weight: 700;
    }

    .nav-tabs .nav-link.active::after {
        width: 100%;
    }

    /* Form */
    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .input-group-text {
        background: linear-gradient(135deg, #e94560, #0f3460);
        border: none;
        color: white;
        width: 45px;
        justify-content: center;
        border-radius: 10px 0 0 10px;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-left: none;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 0 10px 10px 0;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #e94560;
        box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
        background: #fff;
    }

    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, #e94560 0%, #d63447 100%);
        border: none;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 10px;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 5px 15px rgba(233, 69, 96, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #d63447 0%, #e94560 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(233, 69, 96, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);
        border: none;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 10px;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 5px 15px rgba(15, 52, 96, 0.3);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #16213e 0%, #0f3460 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 52, 96, 0.4);
    }

    /* Alerts */
    .alert {
        border: none;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        font-size: 0.95rem;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }

    .alert-danger {
        background: linear-gradient(135deg, #ffe5e9, #fff0f2);
        color: #d63447;
        border-left: 4px solid #e94560;
    }

    .alert-success {
        background: linear-gradient(135deg, #e8f5e9, #f1f8f4);
        color: #2e7d32;
        border-left: 4px solid #4caf50;
    }

    .alert-info {
        background: linear-gradient(135deg, #e3f2fd, #f0f7ff);
        color: #0f3460;
        border-left: 4px solid #2196f3;
        font-size: 0.9rem;
    }

    /* Mobile Responsive */
    @media (max-width: 576px) {
        .login-card {
            border-radius: 15px;
            margin: 0.5rem;
        }

        .login-header {
            padding: 2rem 1.5rem;
        }

        .login-header h3 {
            font-size: 1.5rem;
        }

        .nav-tabs .nav-link {
            padding: 0.875rem 1rem;
            font-size: 0.9rem;
        }

        .btn-primary,
        .btn-success {
            padding: 0.8rem 1.25rem;
            font-size: 0.95rem;
        }
    }
</style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h3 class="mb-0">🏐 Volley Club PORPPAD</h3>
            <p class="mb-0 small">Login / Registrasi Member</p>
        </div>
        
        <div class="p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs mb-4" id="loginTabs" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link active w-100" id="login-tab" data-bs-toggle="tab" 
                            data-bs-target="#tab-login" type="button">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100" id="register-tab" data-bs-toggle="tab" 
                            data-bs-target="#tab-register" type="button">
                        <i class="fas fa-user-plus me-2"></i>Daftar Akun
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- LOGIN TAB -->
                <div class="tab-pane fade show active" id="tab-login">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required />
                            </div>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                            <i class="fa fa-sign-in-alt me-2"></i> Login Sekarang
                        </button>
                        <div class="alert alert-info mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>WARNING!!!:</strong><strong><i>SELAIN ADMIN DILARANG LOGIN!</i></strong>                
                        </div>
                    </form>
                </div>

                <!-- REGISTER TAB -->
                <div class="tab-pane fade" id="tab-register">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="reg_nama" class="form-control" placeholder="Nama lengkap Anda" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="reg_username" class="form-control" placeholder="Pilih username untuk login" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="reg_password" class="form-control" placeholder="Buat password" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="reg_confirm_password" class="form-control" placeholder="Ulangi password" required />
                        </div>
                        <button type="submit" name="register" class="btn btn-success w-100">
                            <i class="fa fa-user-plus me-2"></i> Daftar Akun Baru
                        </button>
                    </form>
                </div>
            </div>

            <hr class="my-4">
            
            <div class="text-center">
                <p class="mb-2 text-muted small">Belum punya akun? Daftar dulu untuk akses penuh!</p>
               
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>