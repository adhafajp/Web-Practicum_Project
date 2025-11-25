<?php
// session_start();
// if (!isset($_SESSION['user_id'])) { header("Location: auth.php"); exit; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Oksigen - Dashboard</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inria+Sans:wght@400;700&family=Inria+Serif:wght@300;400;700&family=Inter:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* --- CSS DASHBOARD --- */
        
        /* HERO SECTION */
        .hero-dashboard {
            background: linear-gradient(99.91deg, #3C6444 6.13%, #272E3F 99.22%);
            padding: 140px 0 180px;
            text-align: center;
            position: relative;
            color: #FFFFFF;
        }

        .hero-top-text {
            font-family: 'Inria Serif', serif; font-weight: 700; font-size: 15px;
            line-height: 150%; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 15px; display: block;
        }

        .hero-main-title {
            font-family: 'Inria Serif', serif; font-weight: 700; font-size: 43px;
            line-height: 150%; margin-bottom: 15px; color: #FFFFFF;
        }

        .hero-subtitle {
            font-family: 'Arial', sans-serif; font-weight: 700; font-size: 15px;
            line-height: 150%; max-width: 650px; margin: 0 auto 40px; color: #FFFFFF;
        }

        .btn-hero-custom {
            background: #4CAF50; border-radius: 24px; width: 228px; height: 50px;
            font-family: 'Inter', sans-serif; font-weight: 700; font-size: 20px; color: #FFFFFF;
            border: none; display: inline-flex; align-items: center; justify-content: center;
            filter: drop-shadow(0px 4px 4px rgba(0, 0, 0, 0.25)); transition: transform 0.2s; text-decoration: none;
        }
        .btn-hero-custom:hover { transform: translateY(-2px); background: #43a047; color: white; }

        .hero-link-blue {
            display: block; margin-top: 20px; font-family: 'Inria Serif', serif; font-weight: 400;
            font-size: 13px; line-height: 150%; color: #207FCE !important; text-decoration: none;
        }
        .hero-link-blue:hover { text-decoration: underline; }

        /* STATS SECTION */
        .stats-container { margin-top: -80px; position: relative; z-index: 10; }
        .stat-card {
            background: white; padding: 30px; border-radius: 15px; text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); height: 100%; border-bottom: 4px solid var(--primary-green);
        }
        .stat-number { font-size: 2rem; font-weight: 700; color: var(--dark-green-bg); margin-bottom: 5px; }
        .stat-label { color: var(--grey-text); font-weight: 600; }

        /* --- MENGAPA DONOXYGEN --- */
        .why-section {
            padding: 100px 0;
            background-color: #D9D9D9;
            text-align: center;
        }

        .why-title {
            font-family: 'Inria Serif', serif;
            font-weight: 700;
            font-size: 40px;
            color: #000000;
            margin-bottom: 15px;
        }

        .why-subtitle {
            font-family: 'Inria Sans', sans-serif;
            font-weight: 700;
            font-size: 20px;
            color: #707070;
            max-width: 900px;
            margin: 0 auto 60px;
        }

        .why-card {
            background: #FFFFFF;
            border-radius: 13px;
            padding: 40px 20px;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s;
        }

        .why-card:hover {
            transform: translateY(-5px);
        }
        
        .why-icon-circle {
            width: 58px;
            height: 58px;
            background: #E9F6EE;
            border-radius: 50%;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #4CAF50;
        }

        .why-card-title {
            font-family: 'Inria Serif', serif;
            font-weight: 700;
            font-size: 22px;
            color: #000000;
            margin-bottom: 15px;
        }

        .why-card-desc {
            font-family: 'Inria Sans', sans-serif;
            font-weight: 700;
            font-size: 16px;
            line-height: 150%;
            color: #9D9D9D;
        }

        .map-section { padding: 80px 0; text-align: center; }
        .map-container { background-color: #f8f9fa; border-radius: 20px; padding: 50px; display: flex; justify-content: center; align-items: center; min-height: 400px; }
        .map-svg { max-width: 100%; height: auto; }
        .gallery-section { padding: 80px 0; background-color: white; text-align: center; }
        .gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .gallery-item { background-color: #ddd; border-radius: 15px; aspect-ratio: 1 / 1; }
        .gallery-item.large { grid-column: span 2; grid-row: span 2; aspect-ratio: auto; }
        .shape-1 { clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%); }
        .shape-2 { clip-path: circle(50% at 50% 50%); }
        .shape-3 { border-radius: 30px; }

    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen Logo" style="height: 40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="donasi.php">Donasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Dampak</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="edukasi.php">Edukasi</a></li>
                </ul>
            </div>
            <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm">Donasi Sekarang</a>
                <!-- <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </li> -->
            </div>
        </div>
    </nav>

    <section class="hero-dashboard">
        <div class="container">
            <span class="hero-top-text">PLATFORM DONASI OKSIGEN TERBAIK</span>
            <h1 class="hero-main-title">Satu Pohon, Sejuta Oksigen</h1>
            <p class="hero-subtitle">Berikan nafas baru untuk Bumi. Donasi mulai dari Rp10.000 dan bantu kami menambah oksigen di seluruh Indonesia</p>
            <a href="donasi.php" class="btn-hero-custom">Donasi Sekarang</a>
            <a href="#" class="hero-link-blue">Pelajari lebih lanjut</a>
        </div>
    </section>

    <section class="stats-container">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-label mb-2">TOTAL DONATUR</div>
                        <div class="stat-number">12.450</div>
                        <div class="small text-muted">Orang</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="background-color: #F9F3E5; border-color: #EBC886;">
                        <div class="stat-label mb-2" style="color: #9F7D3F;">OXYGEN PRODUCED</div>
                        <div class="stat-number" style="color: #9F7D3F;">7.5M</div>
                        <div class="small" style="color: #9F7D3F;">Liters/Day</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="background-color: #E8F5E9; border-color: var(--primary-green);">
                        <div class="stat-label mb-2" style="color: var(--primary-green);">TREES PLANTED</div>
                        <div class="stat-number" style="color: var(--primary-green);">75.320</div>
                        <div class="small" style="color: var(--primary-green);">Pohon</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-section mt-5">
        <div class="container">
            
            <h2 class="why-title">Mengapa Donoxygen?</h2>
            <p class="why-subtitle">Kami merancang setiap langkah donasi secara transparan, mudah dipantau, dan berdampak nyata bagi bumi.</p>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon-circle">
                            <i class="fa-solid fa-hand-holding-dollar"></i> </div> 
                        <h5 class="why-card-title">Transparansi Dana</h5>
                        <p class="why-card-desc">Pantau aliran donasi Anda secara realtime melalui laporan dan dashboard yang terbuka.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon-circle">
                            <i class="fa-solid fa-map-location-dot"></i> </div> 
                        <h5 class="why-card-title">Lacak Pohonmu</h5>
                        <p class="why-card-desc">Dapatkan dokumentasi penanaman, lokasi pohon, dan hasil dari oksigen yang anda dukung.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon-circle">
                            <i class="fa-solid fa-users-line"></i> </div> 
                        <h5 class="why-card-title">Komunitas yang Besar</h5>
                        <p class="why-card-desc">Kamu bisa menjadi relawan serta donatur yang peduli dengan masa depan bumi.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="map-section">
        <div class="container">
            <h2 class="text-center mb-3">Lokasi Penghijauan Kami</h2>
            <p class="text-center w-75 mx-auto mb-5 text-muted">Donasi Anda disalurkan ke berbagai titik strategis di seluruh Indonesia.</p>
            <div class="map-container" style="background-color: #f8f9fa; padding: 40px; border-radius: 20px;">
                <img src="assets/images/peta-indonesia.svg" alt="Peta Indonesia" style="width: 100%; height: auto; max-height: 500px;">
            </div>
        </div>
    </section>

    <section class="gallery-section">
        <div class="container">
            <h2 class="text-center mb-5">Dokumentasi Kegiatan</h2>
            <div class="gallery-grid">
                <div class="gallery-item large shape-1"></div>
                <div class="gallery-item shape-2"></div>
                <div class="gallery-item shape-3"></div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <img src="assets/images/logo-donoxygen.svg" alt="Logo Putih" class="mb-4" style="height: 40px; width: 150px;">
                    <p>Misi kami sederhana: menghubungkan donatur, komunitas, dan alam untuk menghadirkan nafas baru bagi bumi.</p>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><a href="donasi.php">Donasi</a></li>
                        <li><a href="#">Dampak</a></li>
                        <li><a href="#">Laporan</a></li>
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
                    <div class="d-flex">
                        <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                    <h5 class="mt-4">Kontak</h5>
                    <p class="mb-0">halo@donoxygen.com</p>
                    <p>+62 812 3456 7890</p>
                </div>
            </div>
            <div class="footer-bottom text-center text-md-start d-md-flex justify-content-between align-items-center">
                <p class="mb-2 mb-md-0">© 2025 Donoxygen. All right reserved.</p>
                <div>
                    <a href="#" class="me-3">Kebijakan Privasi</a>
                    <a href="#">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>