<?php
// admin_artikel.php
session_start();
include 'koneksi.php';

// Logic: Hapus Artikel
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil nama file/url gambar dari database sebelum row dihapus
    $sqlGetImage = "SELECT thumbnail_url FROM articles WHERE id = $id";
    $resultImage = mysqli_query($conn, $sqlGetImage);
    
    if ($resultImage && mysqli_num_rows($resultImage) > 0) {
        $rowImage = mysqli_fetch_assoc($resultImage);
        $gambar = $rowImage['thumbnail_url'];

        if (!empty($gambar)) {
            if (!filter_var($gambar, FILTER_VALIDATE_URL)) {
                
                if (file_exists($gambar)) {
                    unlink($gambar);
                } 
                elseif (file_exists("uploads/" . $gambar)) { unlink("uploads/" . $gambar); }
            }
        }
    }

    // Hapus data dari database
    $sqlDelete = "DELETE FROM articles WHERE id = $id";
    if (mysqli_query($conn, $sqlDelete)) {
        // PERUBAHAN: Set session notifikasi dan redirect bersih
        $_SESSION['flash_message'] = "Artikel dan gambar berhasil dihapus!";
        $_SESSION['flash_type'] = "success";
        header("Location: admin_artikel.php");
        exit();
    } else {
        $_SESSION['flash_message'] = "Gagal menghapus artikel.";
        $_SESSION['flash_type'] = "error";
        header("Location: admin_artikel.php");
        exit();
    }
}

// Logic: Filter Pencarian & Status
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

// Logic: Pagination
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Hitung total data
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

// Parameter URL untuk pagination
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <link rel="stylesheet" href="assets/css/admin_artikel.css">
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php" style="text-decoration: none;">
            <img src="assets/images/logo-donoxygen.svg" alt="Logo Donoxygen">
        </a>
    </div>
    <div class="small text-white-50 mb-4 px-2" style="margin-top: -10px;">Admin Dashboard</div>
    
    <a href="admin_donasi.php" class="nav-link">Data Donatur</a>
    <a href="admin_artikel.php" class="nav-link active">Artikel</a>
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
                    <th class="text-nowrap">Kategori</th>
                    <th class="text-nowrap">Tanggal</th>
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
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="detail_artikel.php?slug=<?= $row['slug'] ?>" target="_blank" 
                                class="btn-action btn-view" title="Lihat di Web">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>

                                <a href="admin_artikel_form.php?id=<?= $row['id'] ?>" 
                                class="btn-action btn-edit" title="Edit Data">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                
                                <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['title'])) ?>')" 
                                        class="btn-action btn-delete" title="Hapus Permanen">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Script untuk Konfirmasi Hapus
    function confirmDelete(id, title) {
        Swal.fire({
            title: 'Hapus Artikel?',
            text: "Anda akan menghapus artikel: " + title,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke link delete PHP
                window.location.href = 'admin_artikel.php?action=delete&id=' + id;
            }
        })
    }

    // 2. Script untuk Menampilkan Notifikasi Sukses dari PHP Session
    <?php if (isset($_SESSION['flash_message'])): ?>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: '<?= $_SESSION['flash_type'] ?>', // success or error
            title: '<?= $_SESSION['flash_message'] ?>',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        // Hapus session setelah ditampilkan agar tidak muncul lagi saat refresh
        <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
    <?php endif; ?>
</script>
</body>
</html>