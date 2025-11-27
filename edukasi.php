<?php
// edukasi.php
include "koneksi.php";

// 1. Konfigurasi Pagination
$articles_per_page = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start_from = ($page - 1) * $articles_per_page;

// 2. Logika Filter Kategori
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$where_sql = " WHERE a.is_published = 1 ";

if (!empty($category_filter)) {
    $cat_clean = $conn->real_escape_string($category_filter);
    $where_sql .= " AND a.category = '$cat_clean' ";
}

// Parameter URL untuk pagination
$url_category_param = !empty($category_filter) ? "&category=" . urlencode($category_filter) : "";

// 3. Ambil Daftar Kategori (Untuk Filter)
$sql_categories = "SELECT DISTINCT category FROM articles WHERE is_published = 1 AND category != '' ORDER BY category ASC";
$result_categories = $conn->query($sql_categories);

// 4. Ambil Artikel Featured (Terbaru)
$sql_featured = "SELECT a.*, u.name as author_name 
                 FROM articles a 
                 JOIN users u ON a.author_id = u.id 
                 $where_sql 
                 ORDER BY a.created_at DESC LIMIT 1";

$result_featured = $conn->query($sql_featured);
$featured = $result_featured->fetch_assoc();
$featured_id = $featured ? $featured['id'] : 0;

// 5. Ambil Artikel Grid (Pagination, Kecuali Featured)
$sql_articles = "SELECT a.*, u.name as author_name 
                 FROM articles a 
                 JOIN users u ON a.author_id = u.id 
                 $where_sql AND a.id != $featured_id
                 ORDER BY a.created_at DESC 
                 LIMIT $start_from, $articles_per_page";

$result_articles = $conn->query($sql_articles);

// 6. Hitung Total Data (Pagination)
$sql_count = "SELECT COUNT(*) as total FROM articles a $where_sql AND a.id != $featured_id";
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_pages = ceil($row_count['total'] / $articles_per_page);

// Helper: Potong Teks
function limit_text($text, $limit) {
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
    <title>Donasi Oksigen - Wawasan Hijau & Edukasi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- 1. Variables & Global --- */
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

        /* --- 2. Navigation --- */
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

        /* --- 3. Hero Section --- */
        .edu-hero {
            background-color: var(--light-green-bg); padding: 60px 40px;
            border-radius: var(--border-radius-card); margin-bottom: 40px;
            border: 1px solid rgba(0,0,0,0.02);
        }
        .edu-hero h1 { font-size: 2.2rem; font-weight: 700; color: #1f1f1f; margin-bottom: 10px; }

        /* --- 4. Featured Card --- */
        .featured-card {
            background: white; border-radius: var(--border-radius-card); overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03); margin-bottom: 50px; border: 1px solid #eee;
        }
        .featured-img-col {
            background-color: #ddd; min-height: 350px;
            background-size: cover; background-position: center;
        }
        .featured-content { padding: 50px; }
        .badge-featured {
            background-color: #8BC34A; color: white; font-weight: 600; padding: 6px 16px;
            border-radius: 50px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;
            display: inline-block; margin-bottom: 15px;
        }

        /* --- 5. Filters --- */
        .topic-filter { display: flex; flex-wrap: wrap; gap: 10px; }
        .topic-filter .badge-pill {
            background-color: #e9ecef; color: #666; font-weight: 500; padding: 10px 20px;
            border-radius: 50px; text-decoration: none; transition: 0.3s; font-size: 0.9rem;
        }
        .topic-filter .badge-pill:hover { background-color: #dbe0e5; color: #333; }
        .topic-filter .badge-pill.active { background-color: #8c9fa7; color: white; }

        /* --- 6. Article Grid --- */
        .article-card {
            background: white; border-radius: 15px; overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: 0.3s; height: 100%;
            display: flex; flex-direction: column; border: 1px solid #f0f0f0;
        }
        .article-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        
        .article-thumb { height: 200px; background-color: #e0e0e0; background-size: cover; background-position: center; }
        .article-body { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
        
        .meta-tag {
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 10px; display: block;
        }
        .article-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 12px; line-height: 1.4; color: #222; }
        .article-excerpt { color: var(--grey-text); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.6; flex-grow: 1; }
        
        .read-more-link {
            color: #333; text-decoration: none; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center;
        }
        .read-more-link:hover { color: var(--primary-green); }

        /* --- 7. Pagination --- */
        .pagination .page-link {
            color: #333; border: none; margin: 0 5px; border-radius: 50% !important;
            width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
            font-weight: 600; background: transparent;
        }
        .pagination .page-item.active .page-link { background-color: var(--dark-green-bg); color: white; }
        .page-item:first-child .page-link, .page-item:last-child .page-link { width: auto; border-radius: 0 !important; color: #888; font-weight: 500; }

        /* --- 8. CTA Section --- */
        .cta-section-edu {
            background-color: #D1E7D6; padding: 50px; border-radius: var(--border-radius-card); margin: 60px 0;
        }
        .btn-outline-custom {
            border: 2px solid #103831; color: #103831; border-radius: 50px;
            font-weight: 600; padding: 8px 25px; transition: 0.3s; text-decoration: none;
        }
        .btn-outline-custom:hover { background-color: #103831; color: white; }

        /* --- 9. Footer --- */
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

    <div class="container mt-5 pt-3">

        <section class="edu-hero px-4 px-md-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h6 class="text-uppercase ls-2 mb-3" style="color: var(--grey-text); font-size: 0.8rem; letter-spacing: 2px;">EDUKASI & BLOG</h6>
                    <h1>Wawasan Hijau & Edukasi</h1>
                    <p class="text-muted mt-3" style="max-width: 90%;">Pahami lebih dalam mengapa setiap pohon yang kita tanam sangat berarti bagi masa depan bumi.</p>
                </div>
            </div>
        </section>

        <?php if($featured): ?>
        <h6 class="text-muted mb-4 fw-bold" style="font-size: 0.9rem;">
            <?= empty($category_filter) ? "Artikel Pilihan Minggu Ini" : "Artikel Pilihan di Topik Ini" ?>
        </h6>
        <section class="featured-card">
            <div class="row g-0">
                <div class="col-lg-6 featured-img-col d-none d-lg-block" 
                     style="background-image: url('<?= !empty($featured['thumbnail_url']) ? $featured['thumbnail_url'] : 'assets/images/placeholder.jpg' ?>');">
                </div>
                
                <div class="col-lg-6">
                    <div class="featured-content">
                        <span class="badge-featured">Featured</span>
                        <div class="meta-tag text-success mb-2"><?= htmlspecialchars($featured['category']) ?></div>
                        <h2 class="mb-3 fw-bold" style="font-size: 1.8rem;"><?= htmlspecialchars($featured['title']) ?></h2>
                        <p class="text-muted mb-4">
                            <?= limit_text($featured['content'], 25) ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <small class="text-muted">Dipublikasikan: <?= date('d M Y', strtotime($featured['created_at'])) ?></small>
                            <a href="detail_artikel.php?slug=<?= $featured['slug'] ?>" class="btn btn-donasi-sm btn-green px-4">Baca Artikel</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php else: ?>
            <div class="alert alert-info">Belum ada artikel yang dipublikasikan di kategori ini.</div>
        <?php endif; ?>

        <section class="topic-filter mb-5">
            <a href="edukasi.php" class="badge-pill <?= empty($category_filter) ? 'active' : '' ?>">Semua Topik</a>
            <?php if($result_categories->num_rows > 0): ?>
                <?php while($cat = $result_categories->fetch_assoc()): ?>
                    <?php 
                        $nama_kat = $cat['category'];
                        $is_active = ($category_filter === $nama_kat) ? 'active' : '';
                        $link_kat = "?category=" . urlencode($nama_kat);
                    ?>
                    <a href="<?= $link_kat ?>" class="badge-pill <?= $is_active ?>">
                        <?= htmlspecialchars($nama_kat) ?>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>

        <section class="row g-4 mb-5">
            <?php if($result_articles->num_rows > 0): ?>
                <?php while($row = $result_articles->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="article-card">
                        <div class="article-thumb" 
                             style="background-image: url('<?= !empty($row['thumbnail_url']) ? $row['thumbnail_url'] : 'assets/images/placeholder.jpg' ?>');">
                        </div> 
                        
                        <div class="article-body">
                            <div class="meta-tag text-warning"><?= htmlspecialchars($row['category']) ?></div>
                            <h3 class="article-title"><?= htmlspecialchars($row['title']) ?></h3>
                            <p class="article-excerpt"><?= limit_text($row['content'], 15) ?></p>
                            
                            <div class="article-footer d-flex justify-content-between align-items-center mt-auto">
                                <span class="small text-muted"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                <a href="detail_artikel.php?slug=<?= $row['slug'] ?>" class="read-more-link">
                                    Baca Selengkapnya <i class="fa-solid fa-arrow-right ms-2 small"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <?php if($featured): ?>
                    <?php else: ?>
                    <div class="col-12 text-center text-muted">Tidak ada artikel lain untuk ditampilkan.</div>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mb-5">
            <ul class="pagination justify-content-center align-items-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($page > 1) ? "?page=".($page-1).$url_category_param : '#' ?>" tabindex="-1">Sebelumnya</a>
                </li>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $url_category_param ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($page < $total_pages) ? "?page=".($page+1).$url_category_param : '#' ?>">Berikutnya</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <section class="cta-section-edu d-md-flex justify-content-between align-items-center text-center text-md-start">
            <div class="mb-4 mb-md-0">
                <h5 class="fw-bold mb-2" style="color: var(--dark-green-bg); font-size: 1.4rem;">Sudah paham pentingnya oksigen? <br>Mari beraksi nyata.</h5>
                <p class="mb-0 text-muted">Setiap donasi akan dikonversi menjadi pohon baru.</p>
            </div>
            <div class="d-flex justify-content-center gap-3">
                <a href="donasi.php" class="btn btn-donasi-sm btn-green px-4 py-2 d-flex align-items-center">Donasi Sekarang</a>
                <a href="dampak.php" class="btn btn-outline-custom d-flex align-items-center">Lihat Dampak Donasi</a>
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