<?php
session_start();
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_donasi_oksigen';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

$message = "";
$msg_type = ""; 

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // === PROSES LOGIN ===
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
                    // Set Session
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
    
    // === PROSES REGISTER ===
    } elseif ($action === 'register') {
        $nama = $_POST['nama_lengkap'];
        $email = $_POST['email_register'];
        $pass = $_POST['password_register'];
        $conf_pass = $_POST['konfirmasi_password'];
        $agree = isset($_POST['agree_terms']);

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
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $message = "Email sudah terdaftar. Silakan login.";
                $msg_type = "warning";
            } else {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
        :root { --primary-green: #6ABD45; --bg-grey-tab: #E6E6E6; }
        body { font-family: 'Poppins', sans-serif; overflow-x: hidden; background-color: #f8f9fa; }

        /* --- Animations --- */
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-15px); } 100% { transform: translateY(0px); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }

        /* --- Layout --- */
        .left-banner {
            background: linear-gradient(180deg, #A8E0E8 0%, #8CD7B5 50%, #76CD95 100%);
            height: 100vh; position: relative; display: flex; justify-content: center; align-items: center; overflow: hidden;
        }
        .hero-title { position: absolute; top: 3rem; width: 100%; text-align: center; font-weight: 700; font-size: 2rem; color: white; z-index: 10; animation: fadeInUp 1s ease-out; }
        .hero-footer { position: absolute; bottom: 3rem; left: 3rem; font-weight: 700; font-size: 1.8rem; color: white; line-height: 1.2; text-align: left; z-index: 10; animation: fadeInUp 1s ease-out 0.3s backwards; }
        .illustration-img { width: 85%; max-width: 450px; height: auto; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1)); margin-top: -30px; animation: float 6s ease-in-out infinite; }

        .right-form {
            height: 100vh; display: flex; justify-content: center; align-items: flex-start; 
            padding-top: 9.5vh; background-color: #f4f4f4; overflow-y: auto; padding-bottom: 2rem;
        }

        .login-card {
            background: white; width: 100%; max-width: 400px; padding: 0; 
            box-shadow: 0 0 15px rgba(0,0,0,0.05); overflow: hidden; 
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); animation: fadeInUp 0.8s ease-out;
        }

        /* --- Tabs --- */
        .nav-tabs { border-bottom: none; width: 100%; display: flex; }
        .nav-item { width: 50%; padding: 0; margin: 0; }
        .nav-link {
            width: 100%; border-radius: 0 !important; padding: 15px 0; text-align: center; 
            font-weight: 600; text-transform: uppercase; border: none !important; 
            color: #9DA5AA; background-color: var(--bg-grey-tab); margin: 0; transition: all 0.3s;
        }
        .nav-link.active { background-color: white !important; color: var(--primary-green) !important; border-bottom: 4px solid var(--primary-green) !important; font-weight: 700; }
        .nav-link:hover { color: var(--primary-green); background-color: #f0f0f0; }

        /* --- Form --- */
        .form-padding { padding: 2.5rem 2rem; }
        .form-title { font-weight: 700; font-size: 2rem; text-align: center; margin-bottom: 2rem; color: black; animation: fadeInUp 0.5s ease-out backwards; }
        .form-control { border-radius: 6px; padding: 12px 15px; border: 1px solid #999; font-size: 0.95rem; margin-bottom: 20px; color: #333; transition: all 0.3s ease; }
        .form-control:focus { transform: scale(1.02); border-color: var(--primary-green); box-shadow: 0 4px 10px rgba(106, 189, 69, 0.1); }
        .form-control::placeholder { color: #bbb; }
        
        .btn-green {
            background-color: var(--primary-green); color: white; font-weight: 700; text-transform: uppercase; 
            border: none; border-radius: 6px; padding: 12px; width: 100%; font-size: 1rem; margin-top: 10px; 
            box-shadow: 0 4px 10px rgba(106, 189, 69, 0.3); transition: all 0.3s ease;
        }
        .btn-green:hover { background-color: #5aa638; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(106, 189, 69, 0.4); }
        .btn-green:active { transform: translateY(0); }
        .form-check-input:checked { background-color: var(--primary-green); border-color: var(--primary-green); }

        /* --- Staggered Animations --- */
        .tab-pane.active .form-title { animation-delay: 0.0s; }
        .tab-pane.active input:nth-of-type(1) { animation: fadeInUp 0.5s ease backwards; animation-delay: 0.1s; }
        .tab-pane.active input:nth-of-type(2) { animation: fadeInUp 0.5s ease backwards; animation-delay: 0.2s; }
        .tab-pane.active input:nth-of-type(3) { animation: fadeInUp 0.5s ease backwards; animation-delay: 0.3s; }
        .tab-pane.active input:nth-of-type(4) { animation: fadeInUp 0.5s ease backwards; animation-delay: 0.4s; }
        .tab-pane.active .form-check, .tab-pane.active .btn-green { animation: fadeInUp 0.5s ease backwards; animation-delay: 0.5s; }

        /* --- Modal EULA --- */
        .custom-modal-border { background: linear-gradient(135deg, #A8E0E8 0%, #95D5B2 50%, #74C69D 100%); padding: 15px; border-radius: 20px; border: none; }
        .modal-content { border-radius: 10px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .modal-header { border-bottom: 1px solid #eee; }
        .modal-title { font-weight: 700; color: #2d3436; }
        .modal-body { font-size: 0.9rem; line-height: 1.6; color: #555; max-height: 60vh; overflow-y: auto; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-lg-6 left-banner d-none d-lg-flex">
            <div class="hero-title">Donasi Oksigen</div>
            <img src="assets/images/sejuta-pohon.svg" class="illustration-img" alt="Ilustrasi">
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
                                        Saya setuju dengan <a href="#" data-bs-toggle="modal" data-bs-target="#eulaModal">Syarat & Ketentuan</a> dan Kebijakan Privasi
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

<div class="modal fade" id="eulaModal" tabindex="-1" aria-labelledby="eulaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="custom-modal-border"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eulaModalLabel">Syarat & Ketentuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold mb-3 text-center">PERJANJIAN LISENSI PENGGUNA AKHIR (EULA)<br>Donasi Oksigen</h6>
                    
                    <p><strong>1. Pihak Pengembang</strong><br>
                    Aplikasi ini dikembangkan oleh: <br><strong>Adhafa Joan Putranto</strong> dan <strong>Muhammad Kelfin Farelino</strong>.</p>
                    
                    <p><strong>2. Tujuan Penggunaan</strong><br>
                    Aplikasi ini dibuat untuk keperluan <strong>Praktikum Pemrograman Web</strong>.</p>
                    
                    <p><strong>3. Data Privasi</strong><br>
                    Data bersifat simulasi dan tidak disebarluaskan.</p>
                    
                    <p><strong>4. Penolakan Jaminan</strong><br>
                    Layanan disediakan "apa adanya".</p>
                    
                    <p><strong>5. Hak Cipta</strong><br>
                    Hak kekayaan intelektual milik pengembang.</p>
                    
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

<script>
    function acceptTerms() {
        document.getElementById('agreeCheck').checked = true;
        var modalEl = document.getElementById('eulaModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<?php if ($msg_type === 'success' && $action === 'register'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const loginTab = new bootstrap.Tab(document.querySelector('#login-tab'));
        loginTab.show();
    });
</script>
<?php endif; ?>

</body>
</html>