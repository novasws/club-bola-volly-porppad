<?php
require_once 'config.php';

// Jika sudah login dan admin, redirect ke dashboard
if (isLoggedIn() && isAdmin()) {
    redirect('admin/dashboard.php');
}

// Jika sudah login tapi bukan admin, redirect ke home
if (isLoggedIn() && !isAdmin()) {
    redirect('index.php');
}

$error = '';

// Handle Admin Login
if (isset($_POST['login'])) {
    $username = clean($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $query = "SELECT * FROM users WHERE username = '$username' AND role = 'admin'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Smart password verification
            $password_valid = false;
            
            if (strlen($user['password']) === 32 && ctype_xdigit($user['password'])) {
                $password_valid = (md5($password) === $user['password']);
            } elseif (substr($user['password'], 0, 4) === '$2y$' || substr($user['password'], 0, 4) === '$2a$') {
                $password_valid = password_verify($password, $user['password']);
            } else {
                if (md5($password) === $user['password']) {
                    $password_valid = true;
                } else {
                    $password_valid = password_verify($password, $user['password']);
                }
            }
            
            if ($password_valid) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                
                redirect('admin/dashboard.php');
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Bukan akun admin atau username tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Login - PORPPAD</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
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
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        overflow: hidden;
    }

    .login-header {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: white;
        padding: 3rem 2rem;
        text-align: center;
    }

    .login-header i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.95;
    }

    .login-header h3 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .input-group-text {
        background: linear-gradient(135deg, #dc2626, #991b1b);
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
        border-radius: 0 10px 10px 0;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        border: none;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 10px;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 5px 15px rgba(220, 38, 38, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
    }

    .alert {
        border: none;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        border-left: 4px solid #dc2626;
    }

    .alert-danger {
        background: linear-gradient(135deg, #ffe5e9, #fff0f2);
        color: #dc2626;
    }

    .back-link {
        text-align: center;
        margin-top: 1.5rem;
    }

    .back-link a {
        color: #6c757d;
        text-decoration: none;
        transition: color 0.3s;
    }

    .back-link a:hover {
        color: #dc2626;
    }

    .btn-outline-secondary {
        border: 2px solid #e9ecef;
        border-left: none;
        border-radius: 0 10px 10px 0;
        color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #dc2626;
        border-color: #dc2626;
        color: white;
    }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-user-shield"></i>
            <h3>Admin Login</h3>
            <p>Volley Club PORPPAD</p>
        </div>
        
        <div class="p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username Admin</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username admin" required autofocus />
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required />
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary w-100">
                    <i class="fa fa-sign-in-alt me-2"></i> Login Admin
                </button>
            </form>

            <div class="back-link">
                <a href="index.php">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>