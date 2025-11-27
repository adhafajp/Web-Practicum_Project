<?php
// dashboard.php
session_start();
include 'koneksi.php';

// --- LOGIKA STATISTIK (REAL-TIME) ---
// Mengambil total donatur, total pohon, dan estimasi oksigen dari donasi sukses
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

    <style>
        /* --- 1. Variables & Global (Konsisten dengan halaman lain) --- */
        :root {
            --primary-green: #5AB162;
            --dark-green-bg: #103831;
            --light-green-bg: #E5F3E7;
            --grey-text: #6c757d;
            --light-grey-bg: #F0F2F5;
            --link-blue: #3B9AE1;
            --gold-accent: #D4AF37;
            --border-radius-card: 20px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-grey-bg);
            padding-top: 80px;
            color: #333;
        }

        /* Font Khusus Heading (Identitas Dashboard) */
        h1, h2, h3, .serif-font { font-family: 'Inria Serif', serif; }

        /* --- 2. Navigation --- */
        .navbar { background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 15px 0; }
        .nav-link { color: #333; font-weight: 500; margin: 0 10px; }
        .nav-link:hover, .nav-link.active { color: #333; font-weight: 700; }
        
        .btn-donasi-sm {
            background-color: var(--link-blue); color: white; border-radius: 50px;
            padding: 8px 25px; font-weight: 600; border: none; transition: 0.3s; text-decoration: none;
        }
        .btn-donasi-sm:hover { background-color: #2c7bb5; color: white; }
        
        .btn-green { background-color: var(--primary-green) !important; color: white; border: none; }
        .btn-green:hover { background-color: #489c50 !important; }

        /* --- 3. Hero Section --- */
        .hero-dashboard {
            background: linear-gradient(135deg, #103831 0%, #1e5c4e 100%);
            padding: 150px 0 180px; /* Padding bawah besar untuk overlap stats */
            text-align: center; position: relative; color: #FFFFFF;
            border-radius: 0 0 50px 50px;
        }
        .hero-top-text {
            font-size: 0.9rem; letter-spacing: 2px; text-transform: uppercase; 
            margin-bottom: 20px; display: block; opacity: 0.8; font-weight: 600;
        }
        .hero-main-title { font-size: 3.5rem; font-weight: 700; line-height: 1.2; margin-bottom: 20px; }
        .hero-subtitle { font-size: 1.1rem; line-height: 1.6; max-width: 700px; margin: 0 auto 40px; color: #d1e7dd; }
        
        .btn-hero-custom {
            background: var(--primary-green); border-radius: 50px; padding: 15px 40px;
            font-weight: 600; font-size: 1.1rem; color: white; text-decoration: none;
            transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .btn-hero-custom:hover { transform: translateY(-3px); background-color: #489c50; color: white; }
        .hero-link-blue { color: var(--link-blue) !important; margin-top: 25px; display: inline-block; text-decoration: none; font-weight: 500; }
        .hero-link-blue:hover { text-decoration: underline; color: white !important; }

        /* --- 4. Stats Cards --- */
        .stats-container { margin-top: -100px; position: relative; z-index: 10; margin-bottom: 80px; }
        .stat-card {
            background: white; padding: 35px 25px; border-radius: 20px; text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); height: 100%; border-bottom: 5px solid transparent;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 2.5rem; font-weight: 700; color: #333; margin-bottom: 5px; font-family: 'Poppins', sans-serif; }
        .stat-label { font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }

        /* Variants */
        .stat-card.blue { border-color: var(--link-blue); }
        .stat-card.blue .stat-label { color: var(--link-blue); }
        
        .stat-card.gold { border-color: var(--gold-accent); }
        .stat-card.gold .stat-label { color: var(--gold-accent); }
        
        .stat-card.green { border-color: var(--primary-green); }
        .stat-card.green .stat-label { color: var(--primary-green); }

        /* --- 5. Why Section --- */
        .why-section { padding: 50px 0 100px; }
        .why-card {
            background: white; border-radius: 20px; padding: 40px 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03); height: 100%;
            text-align: center; transition: 0.3s; border: 1px solid #eee;
        }
        .why-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        .why-icon {
            width: 70px; height: 70px; background: var(--light-green-bg); border-radius: 50%;
            margin: 0 auto 25px; display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: var(--primary-green);
        }
        .why-title { font-weight: 700; font-size: 1.25rem; margin-bottom: 15px; color: #222; }
        .why-desc { color: var(--grey-text); font-size: 0.95rem; line-height: 1.6; }

        /* --- 6. Map Section --- */
        .map-section { padding: 60px 0; background-color: white; }
        .map-container {
            background-color: #F8F9FA; border-radius: 30px; padding: 40px;
            border: 1px solid #eee;
        }
        .map-img { width: 100%; height: auto; opacity: 0.9; mix-blend-mode: multiply; }

        /* --- 7. Footer --- */
        footer { background-color: var(--dark-green-bg); color: white; padding-top: 70px; padding-bottom: 30px; }
        footer h5 { font-family: 'Poppins', sans-serif; font-weight: 700; margin-bottom: 20px; font-size: 1.1rem; }
        footer a { color: #aaa; text-decoration: none; transition: 0.3s; display: block; margin-bottom: 10px; font-family: 'Poppins', sans-serif; }
        footer a:hover { color: white; }
        .social-icon {
            width: 45px; height: 45px; border: 1px solid rgba(255,255,255,0.3); border-radius: 50%;
            display: flex !important; align-items: center; justify-content: center;
            margin-right: 10px; color: white !important;
        }
        .social-icon:hover { border-color: white; background-color: rgba(255,255,255,0.1); }
        .footer-bottom { margin-top: 50px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); color: #888; font-size: 0.9rem; font-family: 'Poppins', sans-serif; }
    </style>
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
             <div class="d-flex">
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
                        <div class="why-icon">
                            <i class="fa-solid fa-hand-holding-dollar"></i> 
                        </div> 
                        <h5 class="why-title">Transparansi Dana</h5>
                        <p class="why-desc">Pantau aliran donasi Anda secara realtime melalui laporan keuangan dan dashboard yang terbuka untuk publik.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon">
                            <i class="fa-solid fa-map-location-dot"></i> 
                        </div> 
                        <h5 class="why-title">Lacak Pohonmu</h5>
                        <p class="why-desc">Dapatkan koordinat lokasi penanaman, foto pohon, dan update berkala mengenai pertumbuhan pohon donasi Anda.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon">
                            <i class="fa-solid fa-users-line"></i> 
                        </div> 
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