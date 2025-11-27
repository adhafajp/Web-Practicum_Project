<?php
// detail_artikel.php
include "koneksi.php";

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// 1. Ambil Data Artikel Utama
// Menggunakan prepared statement untuk keamanan
$stmt = $conn->prepare("SELECT a.*, u.name as author_name 
                        FROM articles a 
                        JOIN users u ON a.author_id = u.id 
                        WHERE a.slug = ? AND a.is_published = 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

// Redirect ke halaman indeks jika artikel tidak ditemukan
if (!$article) {
    echo "<script>alert('Artikel tidak ditemukan!'); window.location='edukasi.php';</script>";
    exit;
}

// 2. Ambil Artikel Terkait (Related Posts)
// Mengambil 3 artikel terbaru selain artikel yang sedang dibuka
$query_related = "SELECT * FROM articles WHERE id != ? AND is_published = 1 ORDER BY created_at DESC LIMIT 3";
$stmt_related = $conn->prepare($query_related);
$stmt_related->bind_param("i", $article['id']);
$stmt_related->execute();
$related_articles = $stmt_related->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> - Donoxygen</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- 1. Variables & Base Styles --- */
        :root {
            --primary-green: #5AB162;
            --dark-green-bg: #103831;
            --light-green-bg: #E5F3E7;
            --grey-text: #6c757d;
            --light-grey-bg: #F0F2F5;
            --link-blue: #3B9AE1;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: white;
            padding-top: 80px;
            color: #333;
        }

        /* --- 2. Navigation --- */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .nav-link { color: #333; font-weight: 500; margin: 0 10px; }
        .nav-link:hover, .nav-link.active { color: var(--primary-green); font-weight: 700; }
        
        .btn-donasi-sm {
            background-color: var(--link-blue); color: white; border-radius: 50px;
            padding: 8px 25px; font-weight: 600; text-decoration: none; transition: 0.3s; border:none;
        }
        .btn-donasi-sm:hover { background-color: #2c7bb5; color: white; }

        /* --- 3. Article Header & Meta --- */
        .article-header { margin-bottom: 30px; }
        
        .breadcrumb-custom {
            font-size: 0.85rem; color: var(--grey-text);
            margin-bottom: 15px; font-weight: 500;
        }
        .breadcrumb-custom a { text-decoration: none; color: var(--grey-text); }
        .breadcrumb-custom a:hover { color: var(--primary-green); }
        .breadcrumb-code { color: #ccc; margin-left: 10px; font-size: 0.8rem; }

        .article-title {
            font-size: 2.5rem; font-weight: 700;
            line-height: 1.3; color: #222; margin-bottom: 20px;
        }

        .author-meta {
            display: flex; align-items: center;
            font-size: 0.9rem; color: var(--grey-text);
        }
        .author-meta strong { color: #333; }
        .dot-separator { margin: 0 10px; color: #ccc; }

        /* --- 4. Hero Image --- */
        .hero-image-container {
            width: 100%; height: 450px;
            border-radius: 20px; overflow: hidden;
            margin-bottom: 40px; background-color: #eee;
        }
        .hero-image { width: 100%; height: 100%; object-fit: cover; }

        /* --- 5. Article Content Typography --- */
        .article-content { font-size: 1.05rem; line-height: 1.8; color: #444; }
        .article-content p { margin-bottom: 25px; }
        .article-content h4, .article-content h3 { font-weight: 700; color: #222; margin-top: 40px; margin-bottom: 15px; }
        
        .article-content blockquote {
            border-left: 5px solid var(--primary-green);
            background: var(--light-green-bg);
            padding: 20px 25px;
            border-radius: 0 10px 10px 0;
            font-style: italic; font-weight: 500;
            color: var(--dark-green-bg); margin: 30px 0;
        }

        /* --- 6. Sidebar (Sticky Donation Card) --- */
        .sidebar-wrapper { position: sticky; top: 100px; }

        .card-donasi {
            background: white; border-radius: 20px; padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;
        }
        .card-donasi h5 { font-weight: 700; color: #222; margin-bottom: 15px; }
        .card-donasi p { font-size: 0.9rem; color: var(--grey-text); line-height: 1.6; margin-bottom: 25px; }
        
        .btn-donasi-cta {
            width: 100%; display: block; text-align: center;
            background-color: var(--link-blue); color: white;
            padding: 12px; border-radius: 50px; font-weight: 600;
            text-decoration: none; transition: 0.3s;
        }
        .btn-donasi-cta:hover { background-color: #2c7bb5; color: white; }

        /* --- 7. Related Articles --- */
        .related-section { margin-top: 80px; padding-top: 50px; border-top: 1px solid #eee; }
        .related-card { border: none; margin-bottom: 20px; }
        .related-thumb {
            height: 150px; border-radius: 15px;
            background-color: #eee; background-size: cover;
            background-position: center; margin-bottom: 15px;
        }
        .related-title {
            font-weight: 700; font-size: 1rem; line-height: 1.4;
            color: #222; text-decoration: none; display: block; margin-bottom: 5px;
        }
        .related-title:hover { color: var(--primary-green); }

        /* --- 8. Footer --- */
        footer { background-color: var(--dark-green-bg); color: white; padding-top: 60px; padding-bottom: 20px; }
        footer a { color: #aaa; text-decoration: none; }
        footer a:hover { color: white; }
        .social-icon { 
            color: white !important; width: 40px; height: 40px; 
            border: 1px solid rgba(255,255,255,0.3); border-radius: 50%; 
            display:inline-flex; align-items:center; justify-content:center; margin-right:8px; 
        }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            .article-title { font-size: 1.8rem; }
            .hero-image-container { height: 250px; }
            .sidebar-wrapper { position: static; margin-top: 40px; }
        }
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
                    <li class="nav-item"><a class="nav-link" href="#">Dampak</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="edukasi.php">Edukasi</a></li> 
                </ul>
            </div>
             <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-4">

        <div class="row">
            <div class="col-lg-8">
                
                <header class="article-header">
                    <div class="breadcrumb-custom">
                        <a href="edukasi.php">Edukasi</a> 
                        <i class="fa-solid fa-chevron-right mx-2" style="font-size: 0.7rem;"></i>
                        <span class="text-success"><?= htmlspecialchars($article['category']) ?></span>
                        <span class="breadcrumb-code">MH00<?= $article['id'] ?></span>
                    </div>

                    <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>

                    <div class="author-meta">
                        <span>Ditulis oleh: <strong><?= htmlspecialchars($article['author_name']) ?></strong></span>
                        <span class="dot-separator">•</span>
                        <span><?= date('d M Y', strtotime($article['created_at'])) ?></span>
                    </div>
                </header>

                <div class="hero-image-container">
                    <img src="<?= !empty($article['thumbnail_url']) ? $article['thumbnail_url'] : 'assets/images/placeholder.jpg' ?>" 
                         alt="<?= htmlspecialchars($article['title']) ?>" 
                         class="hero-image">
                </div>

                <article class="article-content">
                    <?= $article['content'] ?>
                </article>

                <div class="mt-5 pt-3 border-top">
                    <span class="fw-bold me-2 text-muted small">TAGS:</span>
                    <span class="badge bg-light text-dark border">#DonasiPohon</span>
                    <span class="badge bg-light text-dark border">#Lingkungan</span>
                    <span class="badge bg-light text-dark border">#<?= str_replace(' ', '', $article['category']) ?></span>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-wrapper ps-lg-4">
                    
                    <div class="card-donasi">
                        <h5>Saatnya menambah oksigen bumi!</h5>
                        <p>Dukung pelestarian hutan dan program penambahan oksigen di Indonesia. Dampak nyata untuk masa depan.</p>
                        <a href="donasi.php" class="btn-donasi-cta">
                            Donasi mulai dari 10K
                        </a>
                    </div>

                    <div class="mt-4">
                        <small class="text-uppercase fw-bold text-muted ls-1">Bagikan Artikel</small>
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle" style="width:35px;height:35px;"><i class="fa-brands fa-whatsapp"></i></button>
                            <button class="btn btn-sm btn-outline-secondary rounded-circle" style="width:35px;height:35px;"><i class="fa-brands fa-twitter"></i></button>
                            <button class="btn btn-sm btn-outline-secondary rounded-circle" style="width:35px;height:35px;"><i class="fa-brands fa-facebook-f"></i></button>
                            <button class="btn btn-sm btn-outline-secondary rounded-circle" style="width:35px;height:35px;"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="related-section">
            <h4 class="fw-bold mb-4">Baca Juga Artikel Lainnya</h4>
            <div class="row">
                <?php while($related = $related_articles->fetch_assoc()): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="related-card">
                        <div class="related-thumb" style="background-image: url('<?= !empty($related['thumbnail_url']) ? $related['thumbnail_url'] : 'assets/images/placeholder.jpg' ?>');"></div>
                        <small class="text-warning fw-bold text-uppercase" style="font-size:0.7rem;"><?= htmlspecialchars($related['category']) ?></small>
                        <a href="detail_artikel.php?slug=<?= $related['slug'] ?>" class="related-title mt-1">
                            <?= htmlspecialchars($related['title']) ?>
                        </a>
                        <small class="text-muted"><?= date('d M Y', strtotime($related['created_at'])) ?></small>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>

    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <img src="assets/images/logo-donoxygen.svg" alt="Logo Putih" class="mb-4" style="height: 40px; filter: brightness(0) invert(1);">
                    <p class="text-white-50 small">Misi kami sederhana: menghubungkan donatur, komunitas, dan alam untuk menghadirkan nafas baru bagi bumi.</p>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="h6 fw-bold">Navigasi</h5>
                    <ul class="list-unstyled small">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><a href="donasi.php">Donasi</a></li>
                        <li><a href="#">Dampak</a></li>
                    </ul>
                </div>
                 <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="h6 fw-bold">Edukasi</h5>
                    <ul class="list-unstyled small">
                        <li><a href="edukasi.php">Artikel</a></li>
                        <li><a href="#">Webinar</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="h6 fw-bold">Ikuti Kami</h5>
                    <div class="d-flex mb-3">
                        <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                    </div>
                    <p class="text-white-50 small mb-0">halo@donoxygen.com</p>
                </div>
            </div>
            <div class="border-top border-secondary mt-4 pt-3 text-center small text-white-50">
                <p class="mb-0">© 2025 Donoxygen. All right reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>