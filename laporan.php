<?php
// laporan.php
include "koneksi.php";

// --- 1. LOGIKA KEUANGAN & STATISTIK ---

// A. Hitung Total Pemasukan & Jumlah Donatur
// Tabel: donations, Kolom: amount, payment_status
$sql_masuk = "SELECT SUM(amount) as total_masuk, COUNT(DISTINCT donor_id) as total_donatur 
              FROM donations 
              WHERE payment_status = 'success'";
$result_masuk = $conn->query($sql_masuk);
$data_masuk = $result_masuk->fetch_assoc();

$total_masuk = $data_masuk['total_masuk'] ?? 0;
$total_donatur = $data_masuk['total_donatur'] ?? 0;

// B. Hitung Dana Disalurkan
// CATATAN PENTING:
// Di db.txt tidak ada tabel 'pengeluaran' (expenses).
// Namun, ada tabel 'locations' dengan kolom 'planted_trees'. 
// Kita bisa mengasumsikan dana tersalurkan = (Pohon Tertanam * Estimasi Harga Pohon Rata-rata)
// Atau manual sementara 0. Di sini saya buat manual agar tidak error.
$total_keluar = 0; // Anda bisa ubah ini atau buat tabel pengeluaran nanti.

// C. Hitung Sisa Dana
$sisa_dana = $total_masuk - $total_keluar;


// --- 2. LOGIKA DAFTAR DONATUR & PENCARIAN ---

// Konfigurasi Pagination
$donors_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start_from = ($page - 1) * $donors_per_page;

// Logika Pencarian & Query Dasar
$keyword = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

// Base Query: Gabung tabel donations (t) dan donors (d)
// Hanya ambil yang statusnya 'success'
$base_where = " WHERE t.payment_status = 'success' ";

if (!empty($keyword)) {
    // Cari berdasarkan nama donatur (dari tabel donors) atau pesan (dari tabel donations)
    $base_where .= " AND (d.name LIKE '%$keyword%' OR t.message LIKE '%$keyword%') ";
}

// Parameter URL untuk pagination
$url_search_param = !empty($keyword) ? "&q=" . urlencode($keyword) : "";

// Ambil Data Donatur (JOIN Table)
$sql_donatur = "SELECT t.*, d.name as donor_name 
                FROM donations t
                JOIN donors d ON t.donor_id = d.id
                $base_where
                ORDER BY t.transaction_date DESC 
                LIMIT $start_from, $donors_per_page";

$result_donatur = $conn->query($sql_donatur);

// Hitung Total Data untuk Pagination
$sql_count = "SELECT COUNT(*) as total 
              FROM donations t
              JOIN donors d ON t.donor_id = d.id
              $base_where";
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_pages = ceil($row_count['total'] / $donors_per_page);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transparansi - Donoxygen</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/laporan.css">
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
                    <li class="nav-item"><a class="nav-link" href="dampak.php">Dampak</a></li>
                    <li class="nav-item"><a class="nav-link active" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="edukasi.php">Edukasi</a></li> 
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
                    <h6 class="text-uppercase ls-2 mb-3" style="color: var(--grey-text); font-size: 0.8rem; letter-spacing: 2px;">TRANSPARANSI KEUANGAN</h6>
                    <h1>Laporan Donasi & Penyaluran</h1>
                    <p class="text-muted mt-3" style="max-width: 90%;">
                        Kepercayaan Anda adalah amanah bagi kami. Kami berkomitmen untuk menyajikan data donasi yang transparan, akuntabel, dan dapat diakses oleh publik setiap saat.
                    </p>
                </div>
                <div class="col-lg-4 text-center d-none d-lg-block">
                    <i class="fa-solid fa-file-invoice-dollar" style="font-size: 8rem; color: rgba(16, 56, 49, 0.1);"></i>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                                <div class="stat-value">Rp <?= number_format($total_masuk, 0, ',', '.') ?></div>
                                <div class="stat-label">Total Donasi Terkumpul</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-icon" style="color: #e67e22; background-color: #fff4e6;"><i class="fa-solid fa-paper-plane"></i></div>
                                <div class="stat-value">Rp <?= number_format($total_keluar, 0, ',', '.') ?></div>
                                <div class="stat-label">Dana Telah Disalurkan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-icon" style="color: #3B9AE1; background-color: #eaf6ff;"><i class="fa-solid fa-wallet"></i></div>
                                <div class="stat-value">Rp <?= number_format($sisa_dana, 0, ',', '.') ?></div>
                                <div class="stat-label">Dana Tersisa (Kas)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1" style="color: #222;">Riwayat Donatur</h3>
                    <p class="text-muted mb-0">Terima kasih kepada <span class="fw-bold text-success"><?= $total_donatur ?></span> orang baik.</p>
                </div>
                
                <div class="mt-3 mt-md-0" style="width: 100%; max-width: 300px;">
                    <form action="" method="GET" class="search-box input-group">
                        <input type="text" name="q" class="form-control" placeholder="Cari donatur..." value="<?= htmlspecialchars($keyword) ?>">
                        <button class="btn" type="submit"><i class="fa-solid fa-search"></i></button>
                    </form>
                </div>
            </div>

            <div class="table-responsive table-custom">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="30%">Nama Donatur</th>
                            <th width="20%">Jumlah Donasi</th>
                            <th width="25%">Pesan / Doa</th>
                            <th width="20%">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result_donatur && $result_donatur->num_rows > 0): ?>
                            <?php 
                                $no = $start_from + 1;
                                while($row = $result_donatur->fetch_assoc()): 
                                    
                                    // Logika Anonim (Hamba Allah)
                                    if ($row['is_anonymous'] == 1) {
                                        $tampil_nama = "Hamba Allah";
                                        $inisial = "H";
                                    } else {
                                        $tampil_nama = htmlspecialchars($row['donor_name']);
                                        $inisial = strtoupper(substr($tampil_nama, 0, 1));
                                    }

                                    $jumlah = number_format($row['amount'], 0, ',', '.');
                                    // Kolom 'message' bisa NULL, jadi kita cek
                                    $pesan = !empty($row['message']) ? htmlspecialchars($row['message']) : '<span class="text-muted fst-italic">-</span>';
                                    $tanggal = date('d M Y, H:i', strtotime($row['transaction_date']));
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial"><?= $inisial ?></div>
                                        <span class="fw-medium"><?= $tampil_nama ?></span>
                                    </div>
                                </td>
                                <td class="fw-bold text-success">Rp <?= $jumlah ?></td>
                                <td><small><?= $pesan ?></small></td>
                                <td><small class="text-muted"><?= $tanggal ?></small></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open mb-3 d-block" style="font-size: 2rem;"></i>
                                    Belum ada data donasi yang ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mb-5">
            <ul class="pagination justify-content-center align-items-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($page > 1) ? "?page=".($page-1).$url_search_param : '#' ?>" tabindex="-1"><i class="fa-solid fa-chevron-left"></i></a>
                </li>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $url_search_param ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($page < $total_pages) ? "?page=".($page+1).$url_search_param : '#' ?>"><i class="fa-solid fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

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