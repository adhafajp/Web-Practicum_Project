<?php
// admin_artikel.php
session_start();
include 'koneksi.php';

// 1. Logic: Hapus Artikel
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Hapus data dari database
    $sqlDelete = "DELETE FROM articles WHERE id = $id";
    if (mysqli_query($conn, $sqlDelete)) {
        echo "<script>alert('Artikel berhasil dihapus!'); window.location='admin_artikel.php';</script>";
    }
}

// 2. Logic: Filter Pencarian & Status
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$status  = isset($_GET['status']) ? $_GET['status'] : '';

$whereClause = "WHERE 1=1"; 

if (!empty($keyword)) {
    $whereClause .= " AND a.title LIKE '%$keyword%'";
}

if ($status !== '' && $status !== 'all') {
    $statusInt = (int)$status;
    $whereClause .= " AND a.is_published = '$statusInt'";
}

// 3. Logic: Pagination
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Hitung total data untuk pagination
$sqlCount = "SELECT count(*) as total FROM articles a $whereClause";
$resultCount = mysqli_query($conn, $sqlCount);
$rowCount = mysqli_fetch_assoc($resultCount);
$total_data = $rowCount['total'];
$total_pages = ceil($total_data / $limit);

// Query ambil data utama
$sqlData = "SELECT a.*, u.name as author_name 
            FROM articles a 
            JOIN users u ON a.author_id = u.id 
            $whereClause 
            ORDER BY a.created_at DESC 
            LIMIT $start, $limit";
$result = mysqli_query($conn, $sqlData);

// Parameter URL untuk pagination agar filter tetap tersimpan
$urlParams = "&keyword=" . urlencode($keyword) . "&status=" . urlencode($status);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Artikel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Base & Sidebar Styles --- */
        :root { --sidebar-bg: #0B2B1E; --active-bg: #1A3C2F; --green-brand: #4CAF50; }
        body { font-family: 'Inter', sans-serif; background-color: #F9FAFB; }
        
        .sidebar { width: 250px; background: var(--sidebar-bg); min-height: 100vh; position: fixed; padding: 25px; color: white; }
        .sidebar .brand { margin-bottom: 20px; display: flex; align-items: center; padding-left: 0; }
        .sidebar .brand img { max-width: 180px; height: auto; display: block; }
        .sidebar .brand span { color: white; font-weight: 600; }
        
        .nav-link { color: #A0B0A8; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; font-weight: 500; display: block; text-decoration: none; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.05); }
        .nav-link.active { background: var(--active-bg); color: white; border-left: 4px solid var(--green-brand); }
        
        /* --- Main Content & Layout --- */
        .main-content { margin-left: 250px; padding: 40px; }
        .page-title { font-weight: 700; font-size: 24px; margin-bottom: 5px; }
        .page-subtitle { color: #6B7280; font-size: 14px; margin-bottom: 30px; }
        
        /* --- Action Components --- */
        .btn-new-article { background: var(--green-brand); color: white; border: none; padding: 10px 20px; border-radius: 50px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: 0.3s;}
        .btn-new-article:hover { background: #43A047; color: white; }
        
        .search-filter-bar { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .search-form { display: flex; gap: 15px; width: 100%; }
        .search-input-simple { border: 1px solid #E5E7EB; border-radius: 50px; padding: 10px 20px 10px 40px; width: 300px; background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%239CA3AF" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>') no-repeat 15px center; }
        .dropdown-status { border: 1px solid #E5E7EB; border-radius: 50px; padding: 10px 20px; background: white; color: #374151; cursor: pointer; }

        /* --- Table Styling --- */
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 12px; border: 1px solid #E5E7EB; overflow: hidden; }
        .table-custom th { background: #F9FAFB; color: #6B7280; font-weight: 600; font-size: 13px; padding: 15px 25px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        .table-custom td { padding: 20px 25px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
        .table-custom tr:last-child td { border-bottom: none; }
        
        .article-thumb { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; margin-right: 15px; }
        .article-title { font-weight: 600; font-size: 14px; color: #111; display: block; margin-bottom: 2px; }
        .article-slug { color: #9CA3AF; font-size: 12px; }
        
        .badge-cat { background: #F3F4F6; color: #374151; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .author-info { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #374151; }
        .author-avatar { width: 24px; height: 24px; border-radius: 50%; }
        
        .badge-status-pub { background: #ECFDF5; color: #059669; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-status-draft { background: #F3F4F6; color: #6B7280; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }

        .action-btn { color: #9CA3AF; margin-left: 8px; transition: 0.2s; background: none; border: none; text-decoration: none; display: inline-block;}
        .action-btn.edit:hover { color: #3B82F6; }
        .action-btn.delete:hover { color: #EF4444; }
        .action-btn.view:hover { color: #10B981; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php" style="text-decoration: none;">
            <img src="assets/images/logo-donoxygen.svg" alt="Logo Donoxygen">
        </a>
    </div>
    <div class="small text-white-50 mb-4 px-2" style="margin-top: -10px;">Admin Dashboard</div>
    
    <a href="admin_donasi.php" class="nav-link active">Data Donatur</a>
    <a href="admin_artikel.php" class="nav-link">Artikel</a>
</div>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Kelola Artikel & Edukasi</h1>
            <p class="page-subtitle">Atur publikasi artikel blog, tips hijau, dan konten edukasi untuk para donatur.</p>
        </div>
        <a href="admin_artikel_form.php" class="btn-new-article"><i class="fa-solid fa-plus"></i> Tulis Artikel Baru</a>
    </div>

    <div class="search-filter-bar">
        <form action="" method="GET" class="search-form">
            <input type="text" name="keyword" class="search-input-simple" placeholder="Cari Judul..." value="<?= htmlspecialchars($keyword) ?>">
            
            <select name="status" class="dropdown-status border-0 shadow-sm" onchange="this.form.submit()">
                <option value="all" <?= ($status == 'all' || $status == '') ? 'selected' : '' ?>>Status: Semua</option>
                <option value="1" <?= ($status === '1') ? 'selected' : '' ?>>Published</option>
                <option value="0" <?= ($status === '0') ? 'selected' : '' ?>>Draft</option>
            </select>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">
            <?php if(!empty($keyword)): ?>
                Hasil pencarian untuk "<strong><?= htmlspecialchars($keyword) ?></strong>"
            <?php else: ?>
                Daftar artikel blog dan edukasi yang tersimpan di CMS.
            <?php endif; ?>
        </span>
        <div class="text-secondary small"><?= $total_data ?> artikel total &nbsp;•&nbsp; Diurutkan dari artikel terbaru</div>
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 40%;">Artikel</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Penulis</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= !empty($row['thumbnail_url']) ? $row['thumbnail_url'] : 'https://via.placeholder.com/48' ?>" class="article-thumb" alt="Thumb">
                                <div>
                                    <a href="detail_artikel.php?slug=<?= $row['slug'] ?>" target="_blank" class="article-title text-decoration-none"><?= htmlspecialchars($row['title']) ?></a>
                                    <span class="article-slug">Slug: /<?= htmlspecialchars($row['slug']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-cat"><?= htmlspecialchars($row['category']) ?></span>
                        </td>
                        <td style="font-size: 13px; color: #555;">
                            <?= date('d M Y', strtotime($row['created_at'])) ?>
                        </td>
                        <td>
                            <div class="author-info">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['author_name']) ?>&background=random&size=24" class="author-avatar" alt="Avatar">
                                <span><?= htmlspecialchars($row['author_name']) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if($row['is_published'] == 1): ?>
                                <span class="badge-status-pub">Published • Tayang</span>
                            <?php else: ?>
                                <span class="badge-status-draft">Draft • Konsep</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="admin_artikel_form.php?id=<?= $row['id'] ?>" class="action-btn edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                            <a href="admin_artikel.php?action=delete&id=<?= $row['id'] ?>" class="action-btn delete" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?');"><i class="fa-solid fa-trash"></i></a>
                            <a href="detail_artikel.php?slug=<?= $row['slug'] ?>" target="_blank" class="action-btn view" title="Lihat"><i class="fa-solid fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5">Artikel tidak ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 text-secondary small">
        <span>Halaman <?= $page ?> dari <?= max(1, $total_pages) ?></span>
        <div class="btn-group">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $urlParams ?>" class="btn btn-sm btn-outline-secondary rounded-start-pill">Sebelumnya</a>
            <?php else: ?>
                <span class="btn btn-sm btn-outline-secondary rounded-start-pill disabled">Sebelumnya</span>
            <?php endif; ?>
            
            <span class="btn btn-sm btn-outline-secondary disabled">Page <?= $page ?></span>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= $urlParams ?>" class="btn btn-sm btn-outline-secondary rounded-end-pill">Berikutnya</a>
            <?php else: ?>
                <span class="btn btn-sm btn-outline-secondary rounded-end-pill disabled">Berikutnya</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>