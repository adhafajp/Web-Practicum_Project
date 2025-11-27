<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: donasi.php");
    exit;
}

// Tangkap Data
$nominal    = $_POST['nominal_fix'] ?? 0;
$nama       = $_POST['nama'] ?? 'Anonim';
$email      = $_POST['email'] ?? '-';
$hp         = $_POST['hp'] ?? '-';
$anonim     = isset($_POST['is_anonymous']) ? 1 : 0;
$pohon_id   = $_POST['pohon_id'] ?? 1;

$harga_per_pohon = 10000;
$list_pohon = [1 => "Mangga", 2 => "Mahoni", 3 => "Bakau"];
$nama_pohon = $list_pohon[$pohon_id] ?? "Pohon";

$jumlah_pohon = floor($nominal / $harga_per_pohon);
if ($jumlah_pohon < 1) $jumlah_pohon = 1;

function formatRupiah($angka)
{
    return "Rp" . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metode Pembayaran - Donasi Oksigen</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/donasi_pembayaran.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen Logo" style="height: 35px;">
            </a>
            <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm" style="background-color: #2F80ED; color: white; padding: 8px 24px; border-radius: 50px; font-weight: 700; font-size: 14px;">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="container main-container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="step-label">Langkah 2 dari 3</div>
            <a href="javascript:history.back()" class="text-decoration-none small" style="color: #207FCE;"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
        </div>

        <div class="progress-bar-container">
            <div class="progress-bar-fill"></div>
        </div>

        <h2 class="page-title">Pilih Metode Pembayaran</h2>
        <p class="page-subtitle">Metode aman dan terverifikasi otomatis.</p>

        <form action="proses_pembayaran.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="nominal" value="<?= $nominal ?>">
            <input type="hidden" name="nama" value="<?= $nama ?>">
            <input type="hidden" name="email" value="<?= $email ?>">
            <input type="hidden" name="hp" value="<?= $hp ?>">
            <input type="hidden" name="anonim" value="<?= $anonim ?>">
            <input type="hidden" name="pohon_id" value="<?= $pohon_id ?>">
            <input type="hidden" name="jumlah_pohon" value="<?= $jumlah_pohon ?>">

            <div class="row g-4">

                <div class="col-lg-8">
                    <div class="payment-card">
                        <h5 class="mb-4 fw-bold" style="font-family: 'Inria Serif', serif;">Metode Pembayaran</h5>

                        <div style="margin-bottom: 20px;">
                            <label class="sub-payment-option">
                                <input type="radio" name="metode_bayar" id="radioQris" value="QRIS" class="sub-payment-radio" checked>
                                <div class="sub-payment-box">
                                    <div class="bank-info-container">
                                        <span class="bank-name">QRIS (Scan Cepat)</span>
                                        <span class="bank-number text-muted" style="font-weight: normal;">Gopay, OVO, Dana, LinkAja</span>
                                    </div>
                                    <i class="fa-solid fa-circle-check check-icon"></i>
                                </div>
                            </label>

                            <div class="qris-box" id="qrisContainer">
                                <img src="assets/images/qris-placeholder.jpg" alt="QR Code" class="qris-img">
                                <div class="small text-white-50">Scan kode di atas untuk membayar</div>
                            </div>
                        </div>

                        <span class="payment-category-title">Transfer Bank</span>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="BCA - Muhammad Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="bank-info-container">
                                    <span class="bank-name">BCA</span>
                                    <span class="bank-number">0601238461</span>
                                    <div class="bank-owner">a.n Muhammad Farelino</div>
                                </div>
                                <i class="fa-solid fa-circle-check check-icon"></i>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="Mandiri - Adhafa Joan" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="bank-info-container">
                                    <span class="bank-name">Mandiri</span>
                                    <span class="bank-number">123240069</span>
                                    <div class="bank-owner">a.n Adhafa Joan Putranto</div>
                                </div>
                                <i class="fa-solid fa-circle-check check-icon"></i>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="BNI - Adhafa Putranto" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="bank-info-container">
                                    <span class="bank-name">BNI</span>
                                    <span class="bank-number">123240069</span>
                                    <div class="bank-owner">a.n Adhafa Putranto</div>
                                </div>
                                <i class="fa-solid fa-circle-check check-icon"></i>
                            </div>
                        </label>

                        <span class="payment-category-title">E-Wallet / Bank Digital</span>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="Gopay - Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="bank-info-container">
                                    <span class="bank-name">Gopay</span>
                                    <span class="bank-number">089516656371</span>
                                    <div class="bank-owner">a.n Farelino</div>
                                </div>
                                <i class="fa-solid fa-circle-check check-icon"></i>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="Shopeepay - Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="bank-info-container">
                                    <span class="bank-name">Shopeepay</span>
                                    <span class="bank-number">083894159607</span>
                                    <div class="bank-owner">Kelfin Farelno</div>
                                </div>
                                <i class="fa-solid fa-circle-check check-icon"></i>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="SeaBank - Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="bank-info-container">
                                    <span class="bank-name">SeaBank</span>
                                    <span class="bank-number">083894159607</span>
                                    <div class="bank-owner">a.n Farelino</div>
                                </div>
                                <i class="fa-solid fa-circle-check check-icon"></i>
                            </div>
                        </label>

                    </div>

                    <div class="payment-card">
                        <h5 class="mb-3 fw-bold" style="font-size: 16px;">Upload Bukti Transfer</h5>
                        <div class="upload-box">
                            <div class="upload-text">
                                <i class="fa-solid fa-cloud-arrow-up fa-lg mb-2 text-primary"></i><br>
                                <span id="fileName">Klik atau seret file ke sini (JPG/PNG)</span>
                            </div>
                            <input type="file" name="bukti_transfer" class="file-input-hidden" accept="image/*" onchange="updateFileName(this)" required>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="summary-card">
                        <h5 class="summary-title">Ringkasan Donasi</h5>
                        <div class="summary-row"><span>Nominal</span><span class="fw-bold"><?= formatRupiah($nominal) ?></span></div>
                        <div class="summary-row"><span>Pohon</span><span class="text-end fw-bold text-success"><?= $nama_pohon ?> <br><small class="text-muted fw-normal">(<?= $jumlah_pohon ?> bibit)</small></span></div>
                        <div class="summary-row"><span>Biaya Admin</span><span>Rp0</span></div>
                        <div class="summary-total d-flex justify-content-between">
                            <span>Total</span>
                            <span class="text-primary"><?= formatRupiah($nominal) ?></span>
                        </div>

                        <div class="donor-info">
                            <div class="fw-bold text-dark mb-1">Data Donatur:</div>
                            <div><?= htmlspecialchars($nama) ?></div>
                            <div class="text-truncate"><?= htmlspecialchars($email) ?></div>
                        </div>

                        <button type="submit" class="btn-pay">Konfirmasi & Bayar <i class="fa-solid fa-arrow-right ms-2"></i></button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Logic Update Nama File
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                fileNameDisplay.innerHTML = `<span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i> ${input.files[0].name}</span>`;
            }
        }

        // Logic Toggle QRIS
        document.addEventListener('DOMContentLoaded', function() {
            const qrisBox = document.getElementById('qrisContainer');

            function checkQris() {
                const selected = document.querySelector('input[name="metode_bayar"]:checked');
                if (selected && selected.value === 'QRIS') {
                    qrisBox.style.display = 'block';
                } else {
                    qrisBox.style.display = 'none';
                }
            }

            checkQris();
            document.querySelectorAll('input[name="metode_bayar"]').forEach(radio => {
                radio.addEventListener('change', checkQris);
            });
        });
    </script>
</body>

</html>