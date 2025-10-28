<?php
include "config.php";
header("Content-Type: application/json");

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $nama = $_POST['nama'];
        $wa = $_POST['wa'];
        $q = mysqli_query($conn, "INSERT INTO users (username, password, nama, wa) VALUES ('$username','$password','$nama','$wa')");
        echo json_encode(['status' => $q ? 'ok' : 'fail']);
        break;

    case 'login':
        $username = $_POST['username'];
        $password = $_POST['password'];
        $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        $u = mysqli_fetch_assoc($q);
        if ($u && password_verify($password, $u['password'])) {
            echo json_encode(['status' => 'ok', 'data' => $u]);
        } else {
            echo json_encode(['status' => 'fail']);
        }
        break;

    case 'get_members':
        $res = mysqli_query($conn, "SELECT * FROM members ORDER BY id DESC");
        $data = mysqli_fetch_all($res, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'save_member':
        $nama = $_POST['nama'];
        $umur = $_POST['umur'];
        $posisi = $_POST['posisi'];
        $gender = $_POST['gender'];
        $alamat = $_POST['alamat'];
        $foto = null;

        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'uploads/members/' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        }

        mysqli_query($conn, "INSERT INTO members (nama, umur, posisi, gender, alamat, foto) 
                             VALUES ('$nama','$umur','$posisi','$gender','$alamat','$foto')");
        echo json_encode(['status' => 'ok']);
        break;

    case 'delete_member':
        $id = $_GET['id'];
        mysqli_query($conn, "DELETE FROM members WHERE id='$id'");
        echo json_encode(['status' => 'ok']);
        break;

    case 'get_trophies':
        $res = mysqli_query($conn, "SELECT * FROM trophies ORDER BY id DESC");
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        break;

    case 'add_trophy':
        $nama = $_POST['nama'];
        $tahun = $_POST['tahun'];
        $hasil = $_POST['hasil'];
        $foto = null;

        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'uploads/trophies/' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        }

        mysqli_query($conn, "INSERT INTO trophies (nama, tahun, hasil, foto) VALUES ('$nama','$tahun','$hasil','$foto')");
        echo json_encode(['status' => 'ok']);
        break;

    case 'delete_trophy':
        $id = $_GET['id'];
        mysqli_query($conn, "DELETE FROM trophies WHERE id='$id'");
        echo json_encode(['status' => 'ok']);
        break;

    case 'get_kas':
        $res = mysqli_query($conn, "SELECT * FROM kas ORDER BY id DESC");
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        break;

    case 'add_kas':
        $tgl = $_POST['tanggal'];
        $jenis = $_POST['jenis'];
        $desk = $_POST['deskripsi'];
        $jml = $_POST['jumlah'];
        mysqli_query($conn, "INSERT INTO kas (tanggal, jenis, deskripsi, jumlah) VALUES ('$tgl','$jenis','$desk','$jml')");
        echo json_encode(['status' => 'ok']);
        break;

    case 'delete_kas':
        $id = $_GET['id'];
        mysqli_query($conn, "DELETE FROM kas WHERE id='$id'");
        echo json_encode(['status' => 'ok']);
        break;

    case 'clear_kas':
        mysqli_query($conn, "TRUNCATE TABLE kas");
        echo json_encode(['status' => 'cleared']);
        break;

    default:
        echo json_encode(['status' => 'unknown_action']);
        break;
}
?>
