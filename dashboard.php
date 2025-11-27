<?php
// dashboard.php
session_start();
include 'koneksi.php';

// --- LOGIKA STATISTIK ---
$query_stats = "SELECT 
    COUNT(DISTINCT d.donor_id) as total_donatur,
    SUM(d.tree_count) as total_pohon,
    SUM(d.tree_count * t.oxygen_emission) as total_oksigen
FROM donations d
LEFT JOIN tree_types t ON d.tree_type_id = t.id
WHERE d.payment_status = 'success'";

$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Format Angka
$total_donatur = number_format($stats['total_donatur'] ?? 0, 0, ',', '.');
$total_pohon   = number_format($stats['total_pohon'] ?? 0, 0, ',', '.');
$total_oksigen = number_format($stats['total_oksigen'] ?? 0, 0, ',', '.');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Oksigen - Beranda</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@300;400;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen" height="35">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="donasi.php">Donasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="dampak.php">Dampak</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="edukasi.php">Edukasi</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown me-3">
                        <button class="btn btn-sm rounded-pill user-dropdown-btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar-sm">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span class="fw-semibold me-1">
                                <?= isset($_SESSION['user_name']) ? htmlspecialchars(strtok($_SESSION['user_name'], " ")) : 'Akun' ?>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="border-radius: 15px; min-width: 200px;">
                            <li>
                                <h6 class="dropdown-header text-uppercase small fw-bold text-muted">Akun Anda</h6>
                            </li>
                            <li><span class="dropdown-item-text text-truncate fw-medium text-dark"><?= $_SESSION['user_name'] ?? 'User' ?></span></li>

                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <li>
                                    <hr class="dropdown-divider my-2">
                                </li>
                                <li>
                                    <h6 class="dropdown-header text-uppercase small fw-bold text-muted">Menu Admin</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded fw-medium py-2" href="admin_donasi.php">
                                        <i class="fa-solid fa-user-gear me-2 text-primary"></i>Kelola Donasi
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider my-2">
                            </li>
                            <li>
                                <a class="dropdown-item rounded text-danger fw-medium py-2" href="logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="auth.php" class="text-decoration-none text-secondary me-4 fw-semibold" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Masuk
                    </a>
                <?php endif; ?>

                <a href="donasi.php" class="btn btn-donasi-sm">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <section class="hero-dashboard">
        <div class="container">
            <span class="hero-top-text">PLATFORM DONASI OKSIGEN TERBAIK</span>
            <h1 class="hero-main-title">Satu Pohon,<br>Sejuta Oksigen</h1>
            <p class="hero-subtitle">Berikan nafas baru untuk Bumi. Donasi mulai dari Rp10.000 dan bantu kami menghijaukan kembali hutan di seluruh Indonesia.</p>

            <a href="donasi.php" class="btn-hero-custom">Mulai Donasi</a><br>
            <a href="dampak.php" class="hero-link-blue">Lihat dampak nyata <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
    </section>

    <section class="stats-container">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="stat-card blue">
                        <div class="stat-label">Total Donatur</div>
                        <div class="stat-number"><?= $total_donatur ?></div>
                        <div class="small text-muted">Orang Baik</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card gold">
                        <div class="stat-label">Produksi Oksigen</div>
                        <div class="stat-number"><?= $total_oksigen ?></div>
                        <div class="small text-muted">Liter / Hari</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card green">
                        <div class="stat-label">Pohon Tertanam</div>
                        <div class="stat-number"><?= $total_pohon ?></div>
                        <div class="small text-muted">Bibit Pohon</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-2">Mengapa Donoxygen?</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Kami merancang setiap langkah donasi secara transparan, mudah dipantau, dan berdampak nyata bagi bumi.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                        <h5 class="why-title">Transparansi Dana</h5>
                        <p class="why-desc">Pantau aliran donasi Anda secara realtime melalui laporan keuangan dan dashboard yang terbuka untuk publik.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h5 class="why-title">Lacak Pohonmu</h5>
                        <p class="why-desc">Dapatkan koordinat lokasi penanaman, foto pohon, dan update berkala mengenai pertumbuhan pohon donasi Anda.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon"><i class="fa-solid fa-users-line"></i></div>
                        <h5 class="why-title">Dampak Terukur</h5>
                        <p class="why-desc">Kami menghitung estimasi oksigen yang dihasilkan dan karbon yang diserap dari setiap kontribusi Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <h2 class="fw-bold mb-3">Jejak Hijau di Nusantara</h2>
                    <p class="text-muted mb-4">Donasi Anda disalurkan ke titik-titik kritis yang membutuhkan pemulihan ekosistem, mulai dari pesisir Jakarta hingga hutan hujan Kalimantan.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fa-solid fa-check-circle text-success me-2"></i> Konservasi Hutan Mangrove</li>
                        <li class="mb-2"><i class="fa-solid fa-check-circle text-success me-2"></i> Reboisasi Lahan Kritis</li>
                        <li class="mb-2"><i class="fa-solid fa-check-circle text-success me-2"></i> Taman Hutan Kota</li>
                    </ul>
                    <a href="dampak.php" class="btn btn-donasi-sm btn-green mt-3 px-4">Lihat Detail Persebaran</a>
                </div>
                <div class="col-lg-7">
                    <div class="map-container">
                        <img src="assets/images/peta-indonesia.svg" alt="Peta Persebaran Donasi" class="map-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <img src="assets/images/logo-donoxygen.svg" alt="Logo Putih" class="mb-4" style="height: 40px; filter: brightness(0) invert(1);">
                    <p class="text-white-50">Misi kami sederhana: menghubungkan donatur, komunitas, dan alam untuk menghadirkan nafas baru bagi bumi.</p>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><a href="donasi.php">Donasi</a></li>
                        <li><a href="dampak.php">Dampak</a></li>
                        <li><a href="laporan.php">Laporan</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5>Edukasi</h5>
                    <ul class="list-unstyled">
                        <li><a href="edukasi.php">Artikel</a></li>
                        <li><a href="#">Webinar</a></li>
                        <li><a href="#">Tips & Trik</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5>Ikuti Kami</h5>
                    <div class="d-flex mb-4">
                        <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                    <h5>Kontak</h5>
                    <p class="mb-0 text-white-50">halo@donoxygen.com</p>
                    <p class="text-white-50">+62 812 3456 7890</p>
                </div>
            </div>
            <div class="footer-bottom d-md-flex justify-content-between align-items-center text-center text-md-start">
                <p class="mb-2 mb-md-0">© 2025 Donoxygen. All right reserved.</p>
                <div>
                    <a href="#" class="me-3">Kebijakan Privasi</a>
                    <a href="#">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>