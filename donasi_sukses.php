<?php
// donasi_sukses.php
session_start();
include 'koneksi.php';

// 1. Inisialisasi Variabel Default
$id_transaksi = "-";
$tanggal      = date("d F Y");
$metode       = "-";
$nominal      = 0;
$pohon        = 0;
$oksigen      = 0;
$nama_tampil  = "Donatur";

// 2. Logika Pengambilan Data
if (isset($_GET['inv'])) {
    // A. Prioritas: Ambil data permanen dari Database via Parameter URL
    $inv_code = urldecode($_GET['inv']);
    
    // Query join untuk mengambil data donasi & nama donatur
    $sql = "SELECT d.*, u.name AS nama_asli 
            FROM donations d 
            LEFT JOIN donors u ON d.donor_id = u.id 
            WHERE d.invoice_number = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $inv_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        $id_transaksi = $data['invoice_number'];
        $tanggal      = date("d F Y", strtotime($data['transaction_date']));
        $metode       = $data['payment_method'];
        $nominal      = $data['amount'];
        $pohon        = $data['tree_count'];
        $oksigen      = $pohon * 100; // Estimasi dampak
        
        // Cek status anonim
        $nama_tampil = ($data['is_anonymous'] == 1) ? "Hamba Allah" : $data['nama_asli'];
    } 

} elseif (isset($_SESSION['temp_invoice'])) {
    // B. Fallback: Ambil data sementara dari Session jika URL kosong
    $id_transaksi = $_SESSION['temp_invoice'];
    $tanggal      = date("d F Y"); 
    $metode       = $_SESSION['temp_metode'] ?? "-";
    $nominal      = $_SESSION['temp_nominal'] ?? 0;
    $pohon        = $_SESSION['temp_pohon_count'] ?? 0;
    $oksigen      = $pohon * 100;
    $nama_tampil  = "Donatur Dermawan"; 

} else {
    // C. Redirect jika tidak ada akses data valid
    header("Location: dashboard.php");
    exit;
}

// Helper: Format Rupiah
function formatRupiah($angka){
    return "Rp" . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Berhasil - Donasi Oksigen</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@400;700&family=Inter:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="assets/css/donasi_sukses.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen Logo" style="height: 35px;">
            </a>
            <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm" style="background-color: #2F80ED; color: white; border-radius: 50px; padding: 8px 24px; font-weight: 700; font-size: 14px; text-decoration: none;">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="container main-container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="step-label">Step 3 of 3</div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="progress-bar-container mb-0">
                        <div class="progress-bar-fill"></div>
                    </div>
                    <span class="small text-muted">Selesai</span>
                </div>

                <div class="success-icon-wrapper">
                    <i class="fa-solid fa-check success-icon"></i>
                </div>

                <h2 class="success-title">Terima Kasih, Pahlawan Nafas Bumi</h2>
                <p class="success-subtitle">Pembayaran Anda berhasil. Pohon Anda akan segera kami tanam dan dimonitor dalam portofolio hijau Anda.</p>

                <div class="receipt-card">
                    <div class="receipt-title">Detail Transaksi</div>
                    
                    <div class="receipt-row">
                        <span>ID Transaksi</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($id_transaksi) ?></span>
                    </div>
                    <div class="receipt-row">
                        <span>Donatur</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($nama_tampil) ?></span>
                    </div>
                    <div class="receipt-row">
                        <span>Tanggal</span>
                        <span><?= htmlspecialchars($tanggal) ?></span>
                    </div>
                    <div class="receipt-row">
                        <span>Metode Bayar</span>
                        <span><?= htmlspecialchars($metode) ?></span>
                    </div>

                    <div class="receipt-divider"></div> 

                    <div class="receipt-row total">
                        <span>Total Donasi</span>
                        <span><?= formatRupiah($nominal) ?></span>
                    </div>

                    <div class="impact-box">
                        <strong>Dampak Anda</strong>
                        <?= $pohon ?> Pohon baru telah ditambahkan ke portofolio hijau Anda. Estimasi <?= $oksigen ?> Liter Oksigen/Hari akan dihasilkan.
                    </div>
                </div>

                <div class="text-center">
                    <a href="#" class="btn-sertifikat shadow-sm">Unduh Sertifikat Donasi</a>
                    <a href="dashboard.php" class="btn-home">Kembali ke Beranda</a>
                </div>

            </div>
        </div>
    </div>

    <footer style="background-color: var(--dark-green-bg); color: white; padding: 60px 0 30px; margin-top: 80px;">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <img src="assets/images/logo-donoxygen.svg" alt="Logo Putih" class="mb-4" style="height: 40px; filter: brightness(0) invert(1);">
                    <p style="color: #e0e0e0; font-size: 0.9rem;">Misi kami sederhana: menghubungkan donatur, komunitas, dan alam untuk menghadirkan nafas baru bagi bumi.</p>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="fw-bold mb-3">Navigasi</h5>
                    <ul class="list-unstyled small text-white-50">
                        <li>Home</li><li>Donasi</li><li>Dampak</li><li>Laporan</li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="fw-bold mb-3">Ikuti Kami</h5>
                    <div class="d-flex gap-3">
                        <div style="width: 35px; height: 35px; border: 1px solid white; border-radius: 50%;"></div>
                        <div style="width: 35px; height: 35px; border: 1px solid white; border-radius: 50%;"></div>
                        <div style="width: 35px; height: 35px; border: 1px solid white; border-radius: 50%;"></div>
                        <div style="width: 35px; height: 35px; border: 1px solid white; border-radius: 50%;"></div>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-4 pt-3 d-flex justify-content-between small text-white-50">
                <span>© 2025 Donoxygen. All right reserved.</span>
                <div>
                    <span class="me-3">Kebijakan Privasi</span>
                    <span>Syarat & Ketentuan</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>