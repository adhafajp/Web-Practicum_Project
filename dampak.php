<?php
// dampak.php
include 'koneksi.php';

// --- 1. LOGIKA STATISTIK DAMPAK ---

// Menggabungkan data donasi sukses dengan spesifikasi jenis pohon
// Rumus: Jumlah Pohon * Emisi/Serapan per pohon
$query_stats = "SELECT 
    SUM(d.tree_count) as total_pohon,
    SUM(d.tree_count * t.oxygen_emission) as total_oksigen,
    SUM(d.tree_count * t.co2_absorption) as total_co2
FROM donations d
JOIN tree_types t ON d.tree_type_id = t.id
WHERE d.payment_status = 'success'";

$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Formatting & Konversi Satuan
// Asumsi di DB: oxygen_emission (Liter/hari), co2_absorption (Kg/tahun)
$total_pohon   = $stats['total_pohon'] ?? 0;
$total_oksigen = number_format($stats['total_oksigen'] ?? 0, 0, ',', '.');
// Konversi Kg ke Ton (bagi 1000)
$total_co2     = number_format(($stats['total_co2'] ?? 0) / 1000, 2, ',', '.'); 
// Estimasi: 1 pohon ~ 4m² lahan (Asumsi jarak tanam 2x2 meter)
$lahan_pulih   = number_format(($total_pohon * 4), 0, ',', '.'); 


// --- 2. AMBIL DATA LOKASI PENANAMAN ---
$query_loc = "SELECT * FROM locations ORDER BY planted_trees DESC LIMIT 3";
$result_loc = mysqli_query($conn, $query_loc);


// --- 3. AMBIL ARTIKEL TERBARU (Untuk Widget Bawah) ---
$query_news = "SELECT * FROM articles WHERE is_published = 1 ORDER BY created_at DESC LIMIT 3";
$result_news = mysqli_query($conn, $query_news);

// Helper: Potong Teks
function limit_text_dampak($text, $limit) {
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos   = array_keys($words);
        $text  = substr($text, 0, $pos[$limit]) . '...';
    }
    return strip_tags($text); 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dampak Nyata - Donoxygen</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- STYLE GLOBAL --- */
        :root {
            --primary-green: #5AB162;
            --dark-green-bg: #103831;
            --light-green-bg: #E5F3E7;
            --grey-text: #6c757d;
            --light-grey-bg: #F0F2F5;
            --link-blue: #3B9AE1;
            --border-radius-card: 20px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-grey-bg);
            padding-top: 80px;
            color: #333;
        }

        /* Navbar */
        .navbar { background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 15px 0; }
        .nav-link { color: #333; font-weight: 500; margin: 0 10px; }
        .nav-link:hover, .nav-link.active { color: #333; font-weight: 700; }
        
        .btn-donasi-sm {
            background-color: var(--link-blue); color: white; border-radius: 50px;
            padding: 8px 25px; font-weight: 600; border: none; transition: 0.3s; text-decoration: none;
        }
        .btn-donasi-sm:hover { background-color: #2c7bb5; color: white; }
        .btn-green { background-color: var(--primary-green) !important; color: white; }
        .btn-green:hover { background-color: #489c50 !important; }

        /* Hero Section (Custom untuk Dampak) */
        .impact-hero {
            background-color: var(--light-green-bg); padding: 60px 40px;
            border-radius: var(--border-radius-card); margin-bottom: 40px;
            border: 1px solid rgba(0,0,0,0.02);
            position: relative; overflow: hidden;
        }
        .impact-hero h1 { font-size: 2.2rem; font-weight: 700; color: #1f1f1f; margin-bottom: 10px; }
        .impact-big-stat {
            font-size: 2.5rem; font-weight: 700; color: var(--primary-green);
            margin-top: 15px; margin-bottom: 5px;
        }
        
        /* Stat Cards Small */
        .stat-pill {
            background: white; padding: 10px 20px; border-radius: 50px;
            display: inline-flex; align-items: center; margin-right: 10px; margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); font-weight: 500; font-size: 0.9rem;
        }
        .stat-pill i { margin-right: 8px; color: var(--primary-green); }

        /* Cards Style (Lokasi & Artikel) */
        .custom-card {
            background: white; border-radius: 15px; overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: 0.3s; height: 100%;
            border: 1px solid #f0f0f0; display: flex; flex-direction: column;
        }
        .custom-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        
        .card-img-top-custom {
            height: 200px; width: 100%; object-fit: cover; background-color: #ddd;
        }
        .card-body-custom { padding: 25px; flex-grow: 1; }
        
        /* Progress Bar di Card Lokasi */
        .progress-custom { height: 8px; border-radius: 10px; background-color: #eee; margin: 15px 0 5px 0; }
        .progress-bar-custom { background-color: var(--primary-green); border-radius: 10px; }

        /* Footer */
        footer { background-color: var(--dark-green-bg); color: white; padding-top: 70px; padding-bottom: 30px; }
        footer h5 { font-weight: 700; margin-bottom: 20px; font-size: 1.1rem; }
        footer a { color: #aaa; text-decoration: none; transition: 0.3s; display: block; margin-bottom: 10px; }
        footer a:hover { color: white; }
        .social-icon {
            width: 45px; height: 45px; border: 1px solid rgba(255,255,255,0.3); border-radius: 50%;
            display: flex !important; align-items: center; justify-content: center;
            margin-right: 10px; color: white !important;
        }
        .social-icon:hover { border-color: white; background-color: rgba(255,255,255,0.1); }
        .footer-bottom { margin-top: 50px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); color: #888; font-size: 0.9rem; }
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="donasi.php">Donasi</a></li>
                    <li class="nav-item"><a class="nav-link active" href="dampak.php">Dampak</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="edukasi.php">Edukasi</a></li> 
                </ul>
            </div>
             <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-3">

        <section class="impact-hero px-4 px-md-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h6 class="text-uppercase ls-2 mb-3" style="color: var(--grey-text); font-size: 0.8rem; letter-spacing: 2px;">DAMPAK KOLEKTIF KOMUNITAS</h6>
                    <h1>Bumi Bernapas Lewat Aksi Hijau Anda</h1>
                    <p class="text-muted mt-3" style="max-width: 90%;">
                        Setiap donasi Anda bukan sekadar angka, tapi liter oksigen yang kita hirup dan karbon yang diserap dari atmosfer.
                    </p>
                    
                    <div class="impact-big-stat">
                        <?= $total_oksigen ?> <span style="font-size: 1.2rem; color: #555; font-weight: 500;">Liter Oksigen / Hari</span>
                    </div>
                    <p class="small text-muted mb-4">Dihasilkan dari <?= number_format($total_pohon, 0, ',', '.') ?> pohon yang telah didonasikan.</p>

                    <div class="d-flex flex-wrap">
                        <div class="stat-pill">
                            <i class="fa-solid fa-cloud"></i> <strong><?= $total_co2 ?> Ton</strong> &nbsp;CO2 Diserap
                        </div>
                        <div class="stat-pill">
                            <i class="fa-solid fa-tree"></i> <strong><?= $lahan_pulih ?> m²</strong> &nbsp;Lahan Dipulihkan
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center d-none d-lg-block">
                     <i class="fa-solid fa-earth-asia" style="font-size: 9rem; color: rgba(90, 177, 98, 0.2);"></i>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1" style="color: #222;">Lokasi Penghijauan</h3>
                    <p class="text-muted mb-0">Area fokus penanaman saat ini.</p>
                </div>
            </div>

            <div class="row g-4">
                <?php if(mysqli_num_rows($result_loc) > 0): ?>
                    <?php while($loc = mysqli_fetch_assoc($result_loc)): ?>
                        <?php 
                            $percent = ($loc['target_trees'] > 0) ? ($loc['planted_trees'] / $loc['target_trees']) * 100 : 0;
                            $percent = min(100, $percent); // Max 100%
                        ?>
                    <div class="col-md-4">
                        <div class="custom-card">
                            <img src="<?= !empty($loc['image_url']) ? $loc['image_url'] : 'assets/images/placeholder_loc.jpg' ?>" class="card-img-top-custom" alt="<?= htmlspecialchars($loc['name']) ?>">
                            <div class="card-body-custom">
                                <h5 class="fw-bold mb-2"><?= htmlspecialchars($loc['name']) ?></h5>
                                <p class="text-muted small mb-3"><?= limit_text_dampak($loc['description'], 12) ?></p>
                                
                                <div class="d-flex justify-content-between small fw-bold text-dark">
                                    <span>Terkumpul: <?= number_format($loc['planted_trees']) ?></span>
                                    <span>Target: <?= number_format($loc['target_trees']) ?></span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar progress-bar-custom" role="progressbar" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-5">Belum ada data lokasi.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="mb-5">
            <h3 class="fw-bold mb-4" style="color: #222;">Wawasan Lingkungan</h3>
            <div class="row g-4">
                <?php while($art = mysqli_fetch_assoc($result_news)): ?>
                <div class="col-md-4">
                    <div class="custom-card">
                         <div style="height: 180px; background-image: url('<?= !empty($art['thumbnail_url']) ? $art['thumbnail_url'] : 'assets/images/placeholder.jpg' ?>'); background-size: cover; background-position: center;"></div>
                        <div class="card-body-custom d-flex flex-column">
                            <div class="small text-success fw-bold text-uppercase mb-2" style="font-size: 0.75rem;"><?= htmlspecialchars($art['category']) ?></div>
                            <h5 class="fw-bold mb-2" style="font-size: 1.1rem;"><?= htmlspecialchars($art['title']) ?></h5>
                            <p class="text-muted small flex-grow-1"><?= limit_text_dampak($art['content'], 15) ?></p>
                            <a href="edukasi.php" class="text-decoration-none fw-bold text-dark mt-3" style="font-size: 0.9rem;">
                                Baca Artikel <i class="fa-solid fa-arrow-right ms-1 text-success"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section class="mb-5">
             <div style="background-color: #D1E7D6; padding: 40px; border-radius: var(--border-radius-card); display: flex; flex-direction: column; align-items: center; text-align: center;">
                <h4 class="fw-bold mb-2" style="color: var(--dark-green-bg);">Jadikan Wawasan Ini Nyata</h4>
                <p class="text-muted mb-4" style="max-width: 600px;">Pengetahuan tanpa aksi hanyalah potensi. Ubah kepedulian Anda menjadi pohon nyata sekarang juga.</p>
                <a href="donasi.php" class="btn btn-green btn-donasi-sm px-5 py-2">Donasi Pohon</a>
            </div>
        </section>

    </div>

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