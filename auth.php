<?php
session_start();
include 'koneksi.php';

$message = "";
$msg_type = ""; 


if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // --- 1. PROSES LOGIN ---
    if ($action === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (!empty($email) && !empty($password)) {
            $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Set Session Variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    header("Location: dashboard.php");
                    exit; 
                } else {
                    $message = "Password salah.";
                    $msg_type = "danger";
                }
            } else {
                $message = "Email tidak terdaftar.";
                $msg_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "Email dan Password harus diisi.";
            $msg_type = "danger";
        }
    
    // --- 2. PROSES REGISTRASI ---
    } elseif ($action === 'register') {
        $nama = $_POST['nama_lengkap'];
        $email = $_POST['email_register'];
        $pass = $_POST['password_register'];
        $conf_pass = $_POST['konfirmasi_password'];
        $agree = isset($_POST['agree_terms']);

        // Validasi Input Dasar
        if (empty($nama) || empty($email) || empty($pass) || empty($conf_pass)) {
            $message = "Semua kolom wajib diisi.";
            $msg_type = "danger";
        } elseif ($pass !== $conf_pass) {
            $message = "Password tidak cocok.";
            $msg_type = "danger";
        } elseif (!$agree) {
            $message = "Anda harus menyetujui Syarat & Ketentuan.";
            $msg_type = "danger";
        } else {
            // Cek Email Duplikat
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $message = "Email sudah terdaftar. Silakan login.";
                $msg_type = "warning";
            } else {
                // Insert User Baru
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                $default_role = 'editor'; 

                $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $insertStmt->bind_param("ssss", $nama, $email, $hashed_password, $default_role);

                if ($insertStmt->execute()) {
                    $message = "Registrasi Berhasil! Silakan Login.";
                    $msg_type = "success";
                } else {
                    $message = "Terjadi kesalahan sistem: " . $conn->error;
                    $msg_type = "danger";
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Oksigen - Masuk</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-lg-6 left-banner d-none d-lg-flex">
            <div class="hero-title">Donasi Oksigen</div>
            <img src="assets/images/sejuta-pohon.svg" class="illustration-img" alt="Ilustrasi Pohon">
            <div class="hero-footer">Satu Pohon,<br>Sejuta Oksigen</div>
        </div>

        <div class="col-lg-6 right-form">
            <div class="login-card">
                
                <ul class="nav nav-tabs" id="authTab" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab">LOGIN</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab">DAFTAR</button></li>
                </ul>

                <div class="form-padding">
                    
                    <?php if($message): ?>
                        <div class="alert alert-<?= $msg_type ?> text-center mb-4 py-2" style="font-size:0.9rem; animation: fadeInUp 0.5s;"><?= $message ?></div>
                    <?php endif; ?>

                    <div class="tab-content" id="authTabContent">
                        
                        <div class="tab-pane fade show active" id="login-pane" role="tabpanel">
                            <h2 class="form-title">Login</h2>
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="login">
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                                <button type="submit" class="btn btn-green">LOGIN</button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="register-pane" role="tabpanel">
                            <h2 class="form-title">Register</h2>
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="register">
                                <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                                <input type="email" name="email_register" class="form-control" placeholder="Alamat Email" required>
                                <input type="password" name="password_register" class="form-control" placeholder="Buat Password" required>
                                <input type="password" name="konfirmasi_password" class="form-control" placeholder="Konfirmasi Password" required>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="agree_terms" id="agreeCheck" required>
                                    <label class="form-check-label" for="agreeCheck">
                                        Saya setuju dengan <a href="#" data-bs-toggle="modal" data-bs-target="#eulaModal">Syarat & Ketentuan</a>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-green">REGISTER</button>
                            </form>
                        </div>

                    </div>
                </div> 
            </div> 
        </div>

    </div>
</div>

<div class="modal fade" id="eulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="custom-modal-border"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Syarat & Ketentuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold mb-3 text-center">PERJANJIAN LISENSI PENGGUNA AKHIR (EULA)</h6>
                    <p><strong>1. Pihak Pengembang</strong><br>Aplikasi ini dikembangkan oleh: <strong>Adhafa Joan Putranto</strong> dan <strong>Muhammad Kelfin Farelino</strong>.</p>
                    <p><strong>2. Tujuan Penggunaan</strong><br>Aplikasi ini dibuat untuk keperluan <strong>Praktikum Pemrograman Web</strong>.</p>
                    <p><strong>3. Data Privasi</strong><br>Data bersifat simulasi dan tidak disebarluaskan.</p>
                    <hr>
                    <p class="text-muted small text-center">Dengan mendaftar, Anda menyetujui persyaratan di atas.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-green btn-sm" onclick="acceptTerms()">Saya Setuju</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Logic: Tombol "Saya Setuju" pada modal
    function acceptTerms() {
        document.getElementById('agreeCheck').checked = true;
        const modalEl = document.getElementById('eulaModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    }

    // Logic: Auto-switch ke tab login jika registrasi sukses
    <?php if ($msg_type === 'success' && $action === 'register'): ?>
    document.addEventListener("DOMContentLoaded", function() {
        const loginTab = new bootstrap.Tab(document.querySelector('#login-tab'));
        loginTab.show();
    });
    <?php endif; ?>
</script>

</body>
</html>