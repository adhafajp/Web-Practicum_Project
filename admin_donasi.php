<?php
// admin_donasi.php
session_start();
include 'koneksi.php';

// 1. Handle Logic: Verifikasi Donasi
if (isset($_POST['verifikasi_id'])) {
    $id = intval($_POST['verifikasi_id']);
    $status_baru = $_POST['status']; 
    
    // Update status pembayaran (success/failed)
    $queryUpdate = "UPDATE donations SET payment_status = '$status_baru' WHERE id = $id";
    mysqli_query($conn, $queryUpdate);
    
    // Redirect untuk mencegah resubmission form saat refresh
    header("Location: admin_donasi.php"); exit;
}

// 2. Setup Pagination & Filter
$limit = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

// 3. Query Data (Hanya ambil status 'pending')
$sqlBase = "SELECT d.*, u.name as donor_name FROM donations d 
            JOIN donors u ON d.donor_id = u.id 
            WHERE d.payment_status = 'pending'";

if ($keyword) { 
    $sqlBase .= " AND (u.name LIKE '%$keyword%' OR d.invoice_number LIKE '%$keyword%')"; 
}

// Hitung total data
$resultTotal = mysqli_query($conn, $sqlBase);
$total_data = mysqli_num_rows($resultTotal);
$total_pages = ceil($total_data / $limit);

// Ambil data untuk halaman aktif
$sqlData = $sqlBase . " ORDER BY d.transaction_date DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $sqlData);
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
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="page-title">Antrian Verifikasi Donasi</h1>
            <p class="page-subtitle">Daftar donasi masuk dengan status Menunggu yang perlu diproses.</p>
        </div>
        
        <form method="GET" class="top-bar">
            <input type="text" name="cari" class="search-input" placeholder="Cari nama donatur atau ID..." value="<?= htmlspecialchars($keyword) ?>">
            <div class="status-pill">Status: Menunggu</div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Menampilkan donasi yang siap diverifikasi secara manual.</span>
        <span class="badge bg-light text-secondary border rounded-pill px-3"><?= $total_data ?> donasi menunggu verifikasi</span>
    </div>

    <div class="custom-table-container">
        <div class="list-header">
            <div class="col-1">Donor Info</div>
            <div class="col-2">Nominal</div>
            <div class="col-3">Bukti Transfer</div>
            <div class="col-4">Tanggal</div>
            <div class="col-5">Aksi / Status</div>
        </div>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="list-row">
                <div class="col-1">
                    <span class="donor-name"><?= htmlspecialchars($row['donor_name']) ?></span>
                    <span class="donor-id">ID Donasi: #<?= $row['invoice_number'] ?></span>
                </div>
                <div class="col-2">Rp <?= number_format($row['amount'], 0, ',', '.') ?></div>
                <div class="col-3">
                    <button type="button" class="btn-lihat" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id'] ?>">
                        <i class="fa-regular fa-image"></i> Lihat Bukti
                    </button>
                </div>
                <div class="col-4"><?= date('d M Y - H:i', strtotime($row['transaction_date'])) ?> WIB</div>
                <div class="col-5">
                    <button class="btn-verif-outline" data-bs-toggle="modal" data-bs-target="#modalVerif<?= $row['id'] ?>">
                        <i class="fa-regular fa-circle-check"></i> Verifikasi
                    </button>
                </div>
            </div>

            <div class="modal fade" id="modalBukti<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fs-6 fw-bold">Bukti Transfer #<?= $row['invoice_number'] ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            
                            <?php 
                                // Cek apakah data payment_proof ada di database
                                $bukti = $row['payment_proof']; 
                                $path_bukti = "assets/uploads/" . $bukti;
                            ?>

                            <?php if(!empty($bukti) && file_exists($path_bukti)): ?>
                                <img src="<?= $path_bukti ?>" alt="Bukti Transfer" class="img-fluid rounded shadow-sm" style="max-height: 400px; width: auto;">
                                <a href="<?= $path_bukti ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-3">
                                    <i class="fa-solid fa-up-right-from-square"></i> Buka Gambar Penuh
                                </a>
                            <?php else: ?>
                                <div class="py-5 text-muted bg-light rounded border border-dashed">
                                    <i class="fa-solid fa-image-slash fa-3x mb-3 text-secondary"></i><br>
                                    <span class="fw-bold">Tidak ada bukti transfer</span><br>
                                    <small>User mungkin tidak mengupload gambar atau file rusak.</small>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalVerif<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0"><h5 class="modal-title">Konfirmasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body text-center">
                            <p>Terima donasi <strong>Rp <?= number_format($row['amount']) ?></strong> dari <strong><?= htmlspecialchars($row['donor_name']) ?></strong>?</p>
                            <form method="POST" class="d-flex justify-content-center gap-2 mt-4">
                                <input type="hidden" name="verifikasi_id" value="<?= $row['id'] ?>">
                                <button name="status" value="failed" class="btn btn-outline-danger">Tolak</button>
                                <button name="status" value="success" class="btn btn-success">Verifikasi (Terima)</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="p-5 text-center text-muted">Tidak ada data antrian.</div>
        <?php endif; ?>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mt-3 text-secondary small">
        <span>Menampilkan <?= mysqli_num_rows($result) ?> dari <?= $total_data ?> data</span>
        <div class="btn-group">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&cari=<?= $keyword ?>" class="btn btn-sm btn-outline-secondary rounded-start-pill">Sebelumnya</a>
            <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary rounded-start-pill" disabled>Sebelumnya</button>
            <?php endif; ?>

            <span class="btn btn-sm btn-outline-secondary disabled">Halaman <?= $page ?></span>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&cari=<?= $keyword ?>" class="btn btn-sm btn-outline-secondary rounded-end-pill">Berikutnya</a>
            <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary rounded-end-pill" disabled>Berikutnya</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>