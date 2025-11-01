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
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to left, #363636ff, #fafafacc, #363636ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-header {
            background: linear-gradient(135deg, #ffffffff 0%, #272727ff 100% ,#white);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .login-header h3 {
            color: black;
            margin-bottom: 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .btn-success {
            padding: 0.75rem;
            font-weight: 500;
        }
        .nav-tabs .nav-link {
            color: #666;
        }
        .nav-tabs .nav-link.active {
            color: #667eea;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #444;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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