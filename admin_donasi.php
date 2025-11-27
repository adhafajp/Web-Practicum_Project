<?php
// admin_donasi.php
session_start();
include 'koneksi.php';

// --- VERIFIKASI (Terima/Tolak) ---
if (isset($_POST['verifikasi_id'])) {
    $id = intval($_POST['verifikasi_id']);
    $status_baru = $_POST['status'];
    
    $queryUpdate = "UPDATE donations SET payment_status = '$status_baru' WHERE id = $id";
    mysqli_query($conn, $queryUpdate);
    
    $_SESSION['pesan'] = "Status donasi berhasil diperbarui!";
    header("Location: admin_donasi.php"); exit;
}

// --- HAPUS DONASI ---
if (isset($_POST['hapus_id'])) {
    $id = intval($_POST['hapus_id']);
    
    // Hapus file gambar bukti jika ada
    $qFile = mysqli_query($conn, "SELECT payment_proof FROM donations WHERE id = $id");
    $f = mysqli_fetch_assoc($qFile);
    if (!empty($f['payment_proof']) && file_exists("assets/uploads/".$f['payment_proof'])) {
        unlink("assets/uploads/".$f['payment_proof']);
    }

    $queryDelete = "DELETE FROM donations WHERE id = $id";
    mysqli_query($conn, $queryDelete);

    $_SESSION['pesan'] = "Data donasi berhasil dihapus.";
    header("Location: admin_donasi.php"); exit;
}

// --- SETUP PAGINATION & FILTER ---
$limit = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;
$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

$sqlBase = "SELECT d.*, u.name as donor_name FROM donations d 
            JOIN donors u ON d.donor_id = u.id 
            WHERE d.payment_status = 'pending'";

if ($keyword) { 
    $sqlBase .= " AND (u.name LIKE '%$keyword%' OR d.invoice_number LIKE '%$keyword%')"; 
}

$resultTotal = mysqli_query($conn, $sqlBase);
$total_data = mysqli_num_rows($resultTotal);
$total_pages = ceil($total_data / $limit);

$result = mysqli_query($conn, $sqlBase . " ORDER BY d.transaction_date DESC LIMIT $start, $limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Verifikasi Donasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/admin_donasi.css">
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
    
    <?php if(isset($_SESSION['pesan'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="page-title">Verifikasi Donasi</h1>
            <p class="page-subtitle">Daftar donasi masuk dengan status <b>Pending</b>.</p>
        </div>
        
        <form method="GET" class="top-bar">
            <input type="text" name="cari" class="search-input" placeholder="Cari nama / ID donasi..." value="<?= htmlspecialchars($keyword) ?>">
            <div class="status-pill">Menunggu</div>
        </form>
    </div>

    <div class="custom-table-container">
        <div class="list-header">
            <div class="col-1">Donor Info</div>
            <div class="col-2">Nominal</div>
            <div class="col-3">Bukti Transfer</div>
            <div class="col-4">Tanggal</div>
            <div class="col-5">Aksi</div>
        </div>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="list-row">
                <div class="col-1">
                    <span class="donor-name"><?= htmlspecialchars($row['donor_name']) ?></span>
                    <span class="donor-id">#<?= $row['invoice_number'] ?></span>
                </div>
                
                <div class="col-2" style="color: var(--green-brand);">
                    Rp <?= number_format($row['amount'], 0, ',', '.') ?>
                </div>
                
                <div class="col-3">
                    <button type="button" class="btn-lihat" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id'] ?>">
                        <i class="fa-regular fa-image"></i> Lihat
                    </button>
                </div>
                
                <div class="col-4">
                    <?= date('d M Y, H:i', strtotime($row['transaction_date'])) ?>
                </div>
                
                <div class="col-5">
                    <div class="d-flex justify-content-center align-items-center">
                        <button class="btn-verif-outline" data-bs-toggle="modal" data-bs-target="#modalVerif<?= $row['id'] ?>" title="Verifikasi">
                            <i class="fa-solid fa-check"></i> Verif
                        </button>

                        <button class="btn-hapus-outline" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $row['id'] ?>" title="Hapus Data">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalBukti<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <h6 class="fw-bold">Bukti Transfer #<?= $row['invoice_number'] ?></h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <?php 
                                $path = "assets/uploads/" . $row['payment_proof'];
                                if(!empty($row['payment_proof']) && file_exists($path)): 
                            ?>
                                <img src="<?= $path ?>" class="img-fluid rounded shadow-sm mb-3">
                                <a href="<?= $path ?>" target="_blank" class="btn btn-sm btn-outline-dark w-100">Buka Gambar Penuh</a>
                            <?php else: ?>
                                <p class="text-muted small">Tidak ada bukti transfer.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalVerif<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header border-0"><h6 class="modal-title fw-bold">Konfirmasi</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body text-center">
                            <p class="small text-muted mb-4">Terima donasi Rp <?= number_format($row['amount']) ?>?</p>
                            <form method="POST" class="d-grid gap-2">
                                <input type="hidden" name="verifikasi_id" value="<?= $row['id'] ?>">
                                <button name="status" value="success" class="btn btn-success btn-sm">Terima (Valid)</button>
                                <button name="status" value="failed" class="btn btn-outline-danger btn-sm">Tolak (Invalid)</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalHapus<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-body text-center pt-4">
                            <i class="fa-solid fa-triangle-exclamation text-danger fa-2x mb-3"></i>
                            <h6 class="fw-bold">Hapus Data?</h6>
                            <p class="small text-muted mb-4">Data ini akan dihapus permanen.</p>
                            <form method="POST" class="d-flex justify-content-center gap-2">
                                <input type="hidden" name="hapus_id" value="<?= $row['id'] ?>">
                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="p-5 text-center text-muted">Belum ada antrian donasi.</div>
        <?php endif; ?>
    </div>
    
    <div class="d-flex justify-content-end mt-3">
        <div class="btn-group">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&cari=<?= $keyword ?>" class="btn btn-sm btn-outline-secondary">Prev</a>
            <?php endif; ?>
            
            <button class="btn btn-sm btn-outline-secondary disabled"><?= $page ?></button>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&cari=<?= $keyword ?>" class="btn btn-sm btn-outline-secondary">Next</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>