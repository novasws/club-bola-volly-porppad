<?php
include "config.php";
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama'];
  $gender = $_POST['gender'];
  $umur = $_POST['umur'];
  $posisi = $_POST['posisi'];
  $alamat = $_POST['alamat'];
  $wa = $_POST['wa'];

  $conn->query("INSERT INTO members (nama, gender, umur, posisi, alamat, wa) 
                VALUES ('$nama','$gender','$umur','$posisi','$alamat','$wa')");
  $success = "Data anggota berhasil ditambahkan!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Anggota - PORPPAD</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h3 class="text-center fw-bold mb-4">Formulir Daftar Anggota Baru</h3>

  <?php if(isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="post" class="shadow p-4 rounded bg-light">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="nama" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select" required>
          <option value="">Pilih</option>
          <option value="Putra">Putra</option>
          <option value="Putri">Putri</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Umur</label>
        <input type="number" name="umur" class="form-control" min="10" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Posisi</label>
        <select name="posisi" class="form-select">
          <option>Spiker</option>
          <option>Libero</option>
          <option>Setter</option>
          <option>Blocker</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Alamat</label>
        <input type="text" name="alamat" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">No. WhatsApp</label>
        <input type="text" name="wa" class="form-control">
      </div>
    </div>

    <div class="text-end mt-4">
      <button class="btn btn-success">Simpan</button>
      <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>
  </form>
</div>
</body>
</html>
