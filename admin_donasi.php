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
    
    <style>
        /* --- Base & Sidebar --- */
        :root { --sidebar-bg: #0B2B1E; --active-bg: #1A3C2F; --green-brand: #4CAF50; }
        body { font-family: 'Inter', sans-serif; background-color: #F9FAFB; }
        
        .sidebar { width: 250px; background: var(--sidebar-bg); min-height: 100vh; position: fixed; padding: 25px; color: white; }
        .sidebar .brand { margin-bottom: 20px; display: flex; align-items: center; padding-left: 0; }
        .sidebar .brand img { max-width: 180px; height: auto; display: block; }
        .sidebar .brand span { color: white; font-weight: 600; }
        
        .nav-link { color: #A0B0A8; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; font-weight: 500; display: block; text-decoration: none; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.05); }
        .nav-link.active { background: var(--active-bg); color: white; border-left: 4px solid var(--green-brand); }
        
        /* --- Main Content Layout --- */
        .main-content { margin-left: 250px; padding: 40px; }
        .page-title { font-weight: 700; font-size: 24px; margin-bottom: 5px; }
        .page-subtitle { color: #6B7280; font-size: 14px; margin-bottom: 30px; }
        
        /* --- Filters & Search --- */
        .top-bar { display: flex; justify-content: flex-end; gap: 15px; margin-bottom: 20px; }
        .search-input { border-radius: 50px; border: 1px solid #E5E7EB; padding: 8px 20px 8px 40px; width: 300px; font-size: 14px; background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%239CA3AF" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>') no-repeat 15px center; background-color: white; }
        .status-pill { background: #FFF7ED; color: #C2410C; border: 1px solid #FFEDD5; padding: 6px 16px; border-radius: 50px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
        .status-pill::before { content: ''; width: 6px; height: 6px; background: #C2410C; border-radius: 50%; }

        /* --- List/Table Styles --- */
        .custom-table-container { background: white; border-radius: 12px; border: 1px solid #E5E7EB; overflow: hidden; }
        .list-header { background: #F3F4F6; padding: 15px 30px; display: flex; font-size: 12px; font-weight: 600; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .list-row { padding: 20px 30px; border-bottom: 1px solid #F3F4F6; display: flex; align-items: center; transition: 0.2s; }
        .list-row:last-child { border-bottom: none; }
        .list-row:hover { background: #FAFAFA; }
        
        /* Columns Config */
        .col-1 { width: 30%; } /* Info */
        .col-2 { width: 20%; font-weight: 600; } /* Nominal */
        .col-3 { width: 15%; } /* Bukti */
        .col-4 { width: 20%; color: #6B7280; font-size: 13px; } /* Tanggal */
        .col-5 { width: 15%; text-align: right; } /* Aksi */

        .donor-name { font-weight: 600; color: #111; display: block; margin-bottom: 4px; }
        .donor-id { font-size: 12px; color: #9CA3AF; }
        
        /* --- Buttons --- */
        .btn-lihat { border: 1px solid #D1D5DB; color: #374151; font-size: 12px; padding: 6px 12px; border-radius: 4px; background: white; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-weight: 500; cursor: pointer; transition: 0.2s; }
        .btn-lihat:hover { background: #F3F4F6; border-color: #9CA3AF; }

        .btn-verif-outline { 
            border: 1px solid var(--green-brand); color: var(--green-brand); 
            background: white; border-radius: 50px; padding: 8px 16px; 
            font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.2s;
        }
        .btn-verif-outline:hover { background: var(--green-brand); color: white; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <img src="assets/images/logo-donoxygen.svg" alt="Logo Donoxygen">
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
                            <?php if(!empty($row['payment_proof'])): ?>
                                <img src="uploads/<?= $row['payment_proof'] ?>" alt="Bukti Transfer" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                            <?php else: ?>
                                <div class="py-5 text-muted bg-light rounded">
                                    <i class="fa-solid fa-image-slash fa-2x mb-2"></i><br>
                                    Tidak ada bukti transfer dilampirkan.
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