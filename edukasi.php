<?php
// session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Oksigen - Wawasan Hijau & Edukasi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* --- CSS KHUSUS HALAMAN EDUKASI --- */
        body {
            background-color: white;
            padding-top: 80px;
        }

        /* Header Section */
        .edu-hero {
            background-color: var(--light-green-bg);
            padding: 60px 0;
            border-radius: 20px;
            margin-bottom: 50px;
        }

        .edu-hero h1 {
            font-size: 2.5rem;
            color: var(--dark-green-bg);
        }

        /* Featured Article Card (Artikel Pilihan Mingguan) */
        .featured-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 50px;
        }

        .featured-img-col {
            background-color: #ddd;
            min-height: 300px;
            /* background-image: url('...'); background-size: cover; */
        }

        .featured-content {
            padding: 40px;
        }

        .badge-featured {
            background-color: var(--primary-green);
            color: white;
            font-weight: 600;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 15px;
            display: inline-block;
        }

        .meta-tag {
            font-size: 0.8rem;
            color: var(--grey-text);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* Filter Topik (Pill Badges) */
        .topic-filter .badge {
            background-color: var(--light-grey-bg);
            color: var(--grey-text);
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 50px;
            margin-right: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .topic-filter .badge:hover,
        .topic-filter .badge.active {
            background-color: var(--dark-green-bg);
            color: white;
        }

        /* Kartu Artikel Grid */
        .article-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            transition: 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .article-thumb {
            height: 200px;
            background-color: #ddd;
            /* background-image: url('...'); background-size: cover; */
        }

        .article-body {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .article-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .article-excerpt {
            color: var(--grey-text);
            font-size: 0.9rem;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .article-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--grey-text);
        }

        .read-more-link {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .read-more-link:hover {
            text-decoration: underline;
        }

        /* --- PAGINATION --- */
        .pagination {
            gap: 10px;
            align-items: center;
        }

        .page-item .page-link {
            color: var(--dark-green-bg);
            border: 1px solid #e0e0e0;
            margin: 0;
            border-radius: 50% !important;
            width: 40px; 
            height: 40px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-weight: 600;
            text-decoration: none;
        }

        .page-item:first-child .page-link, 
        .page-item:last-child .page-link {
            border-radius: 50px !important;
            width: auto;
            padding: 0 20px;
        }

        /* State Active */
        .pagination .page-item.active .page-link {
            background-color: var(--dark-green-bg);
            color: white;
            border-color: var(--dark-green-bg);
        }
        
        /* State Disabled */
        .page-item.disabled .page-link {
            background-color: transparent;
            border-color: transparent;
            color: #ccc;
        }

        /* CTA Section Bawah */
        .cta-section-edu {
            background-color: var(--light-green-bg);
            padding: 40px;
            border-radius: 20px;
            margin-top: 60px;
            margin-bottom: 60px;
        }

    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen Logo"><!--style="height: 40px; width: 150px;"-->
            </a>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="donasi.php">Donasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Dampak</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="edukasi.php">Edukasi</a></li> </ul>
            </div>
             <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <section class="edu-hero px-4 px-md-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h6 class="text-uppercase ls-2 mb-3" style="color: var(--grey-text);">EDUKASI & BLOG</h6>
                    <h1>Wawasan Hijau & Edukasi</h1>
                    <p class="lead text-muted">Pahami lebih dalam mengapa setiap pohon yang kita tanam sangat berarti bagi masa depan bumi.</p>
                </div>
                </div>
        </section>

        <h6 class="text-muted mb-4">Artikel Pilihan Minggu Ini</h6>
        <section class="featured-card">
            <div class="row g-0">
                <div class="col-lg-6 featured-img-col d-none d-lg-block" style="background-image: url('assets/images/artikel-mangrove.jpg');"></div>
                
                <div class="col-lg-6">
                    <div class="featured-content">
                        <span class="badge-featured">Featured</span>
                        <div class="meta-tag text-success">Manfaat Oksigen</div>
                        <h2 class="mb-3">Mengapa Hutan Mangrove Adalah Benteng Pesisir Terbaik</h2>
                        <p class="text-muted mb-4">Mangrove bukan hanya rumah bagi keanekaragaman hayati, tetapi juga perisai alami yang melindungi pesir dari abrasi, badai, dan kenaikan air laut. Kenali cara kerjanya...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Dipublikasikan: 12 Jan 2025</small>
                            <a href="#" class="btn btn-donasi-sm px-4">Baca Artikel</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="topic-filter mb-5">
            <a href="#" class="badge active">Semua Topik</a>
            <a href="#" class="badge">Manfaat Oksigen</a>
            <a href="#" class="badge">Ancaman Deforestasi</a>
            <a href="#" class="badge">Tips & Aksi</a>
        </section>

        <section class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="article-card">
                    <div class="article-thumb"></div> <div class="article-body">
                        <div class="meta-tag text-warning">Ancaman Deforestasi</div>
                        <h3 class="article-title">Bagaimana Deforestasi Mengganggu Siklus Oksigen Dunia</h3>
                        <p class="article-excerpt">Penggundulan hutan bukan hanya soal hilangnya pohon, tetapi juga tentang ketidakseimbangan oksigen dan iklim...</p>
                        <div class="article-footer">
                            <span>6 Jan 2025</span>
                            <a href="#" class="read-more-link">Baca Selengkapnya <i class="fa-solid fa-arrow-right ms-2 small"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="article-card">
                    <div class="article-thumb"></div>
                    <div class="article-body">
                        <div class="meta-tag text-warning">Ancaman Deforestasi</div>
                        <h3 class="article-title">Bagaimana Deforestasi Mengganggu Siklus Oksigen Dunia</h3>
                        <p class="article-excerpt">Penggundulan hutan bukan hanya soal hilangnya pohon, tetapi juga tentang ketidakseimbangan oksigen dan iklim...</p>
                        <div class="article-footer">
                            <span>6 Jan 2025</span>
                            <a href="#" class="read-more-link">Baca Selengkapnya <i class="fa-solid fa-arrow-right ms-2 small"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="article-card">
                    <div class="article-thumb"></div>
                    <div class="article-body">
                        <div class="meta-tag text-warning">Ancaman Deforestasi</div>
                        <h3 class="article-title">Bagaimana Deforestasi Mengganggu Siklus Oksigen Dunia</h3>
                        <p class="article-excerpt">Penggundulan hutan bukan hanya soal hilangnya pohon, tetapi juga tentang ketidakseimbangan oksigen dan iklim...</p>
                        <div class="article-footer">
                            <span>6 Jan 2025</span>
                            <a href="#" class="read-more-link">Baca Selengkapnya <i class="fa-solid fa-arrow-right ms-2 small"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            </section>

        <nav aria-label="Page navigation" class="mb-5">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link border-0" href="#" tabindex="-1">Sebelumnya</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link border-0" href="#">Berikutnya</a>
                </li>
            </ul>
        </nav>

        <section class="cta-section-edu d-md-flex justify-content-between align-items-center text-center text-md-start">
            <div class="mb-4 mb-md-0">
                <h5 class="fw-bold mb-2" style="color: var(--dark-green-bg);">Sudah paham pentingnya oksigen? Mari beraksi nyata.</h5>
                <p class="mb-0 text-muted">Setiap donasi akan dikonversi menjadi pohon baru yang menyejukkan bumi.</p>
            </div>
            <div class="d-flex justify-content-center gap-3">
                <a href="donasi.php" class="btn btn-donasi-sm px-4 py-2">Donasi Sekarang</a>
                <a href="#" class="btn btn-outline-success px-4 py-2" style="border-radius: 50px; font-weight: 600;">Lihat Dampak Donasi</a>
            </div>
        </section>

    </div> <footer>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>