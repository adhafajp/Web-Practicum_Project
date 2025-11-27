<?php
session_start();

// 1. Validasi Request (Hanya menerima POST dari Step 1)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: donasi.php");
    exit;
}

// 2. Tangkap Data Input
$nominal    = $_POST['nominal_fix'] ?? 0;
$nama       = $_POST['nama'] ?? 'Anonim';
$email      = $_POST['email'] ?? '-';
$hp         = $_POST['hp'] ?? '-';
$anonim     = isset($_POST['is_anonymous']) ? 1 : 0;
$pohon_id   = $_POST['pohon_id'] ?? 1;

// 3. Konfigurasi & Logika Pohon
$harga_per_pohon = 10000;
$list_pohon = [
    1 => "Mangga",
    2 => "Mahoni",
    3 => "Bakau"
];
$nama_pohon = $list_pohon[$pohon_id] ?? "Pohon";

// Hitung estimasi jumlah pohon
$jumlah_pohon = floor($nominal / $harga_per_pohon);
if($jumlah_pohon < 1) $jumlah_pohon = 1;

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
    <title>Metode Pembayaran - Donasi Oksigen</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@400;700&family=Inter:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* --- Global & Layout --- */
        body {
            background-color: #E5E5E5;
            padding-top: 80px;
            font-family: 'Inter', sans-serif;
        }
        .main-container { padding-bottom: 100px; }

        /* --- Header Steps --- */
        .step-label { font-size: 14px; color: #666; margin-bottom: 8px; }
        .progress-bar-container { height: 6px; background-color: #D9D9D9; border-radius: 10px; margin-bottom: 40px; width: 300px; }
        .progress-bar-fill { height: 100%; background-color: var(--primary-green); width: 66%; border-radius: 10px; }
        h2.page-title { font-family: 'Inria Serif', serif; font-weight: 700; font-size: 24px; margin-bottom: 10px; }
        p.page-subtitle { font-size: 14px; color: #666; margin-bottom: 30px; }

        /* --- Payment Selection Card --- */
        .payment-card {
            background: white; border-radius: 12px; padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 20px;
        }
        .payment-category-title {
            font-weight: 700; font-size: 15px; margin-bottom: 15px; display: block;
            border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 30px; color: #333;
        }
        
        /* Custom Radio Button Styling */
        .sub-payment-option { display: block; margin-bottom: 10px; cursor: pointer; }
        .sub-payment-radio { display: none; } /* Hide default radio */

        .sub-payment-box {
            border: 1px solid #ddd; border-radius: 8px; padding: 12px 15px;
            display: flex; align-items: center; transition: all 0.2s; background-color: white;
        }
        .sub-payment-box:hover { background-color: #f9f9f9; border-color: #ccc; }

        /* Checked State */
        .sub-payment-radio:checked + .sub-payment-box {
            border-color: var(--primary-green);
            background-color: #E9F6EE;
            box-shadow: 0 0 0 1px var(--primary-green);
        }

        /* Custom Circle Radio Indicator */
        .radio-circle {
            width: 18px; height: 18px; border: 2px solid #ccc; border-radius: 50%;
            margin-right: 15px; flex-shrink: 0; position: relative;
        }
        .sub-payment-radio:checked + .sub-payment-box .radio-circle {
            border-color: var(--primary-green); background-color: var(--primary-green);
            box-shadow: inset 0 0 0 3px white;
        }

        /* Bank Details Typography */
        .bank-name { font-weight: 700; font-size: 14px; display: block; color: #000; }
        .bank-number { font-size: 13px; color: #555; display: block; margin-bottom: 2px; }
        .bank-owner { font-size: 11px; color: #888; font-style: italic; }

        /* QRIS Box */
        .qris-box {
            background: #333; color: white; border-radius: 8px; padding: 15px;
            text-align: center; width: fit-content; margin-left: 35px; margin-top: 10px;
        }
        .qris-img { width: 100px; height: 100px; background: white; margin: 0 auto 5px; display: block; }

        /* --- File Upload --- */
        .upload-box {
            background-color: #F0F0F0; border-radius: 8px; height: 60px;
            display: flex; align-items: center; padding: 0 20px;
            cursor: pointer; margin-top: 10px; position: relative;
        }
        .file-input-hidden { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

        /* --- Summary Sidebar --- */
        .summary-card {
            background: white; border-radius: 12px; padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 100px;
        }
        .summary-title { font-family: 'Inria Serif', serif; font-weight: 700; font-size: 16px; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; color: #555; }
        .summary-total { border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; font-weight: 700; font-size: 14px; color: #000; }
        
        .donor-info { background-color: #F9F9F9; border-radius: 6px; padding: 10px; font-size: 11px; color: #666; margin-top: 15px; margin-bottom: 20px; }
        
        .btn-pay {
            background-color: #207FCE; color: white; font-weight: 600; width: 100%;
            border-radius: 50px; padding: 10px; border: none; transition: 0.3s;
        }
        .btn-pay:hover { background-color: #1a6cb0; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen Logo" style="height: 35px;">
            </a>
            <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="container main-container mt-5">
        
        <div class="step-label">Step 2 of 3</div>
        <a href="javascript:history.back()" class="text-decoration-none small float-end" style="color: #207FCE;">Kembali</a>
        
        <div class="progress-bar-container">
            <div class="progress-bar-fill"></div>
        </div>

        <h2 class="page-title">Pilih Metode Pembayaran</h2>
        <p class="page-subtitle">Pilih metode pembayaran yang paling cocok denganmu</p>

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
                        <h5 class="mb-4 fw-bold" style="font-size: 16px; font-family: 'Inria Serif', serif;">Metode Pembayaran</h5>

                        <div style="margin-bottom: 20px;">
                            <label class="sub-payment-option">
                                <input type="radio" name="metode_bayar" value="QRIS" class="sub-payment-radio" checked>
                                <div class="sub-payment-box">
                                    <div class="radio-circle"></div>
                                    <span class="fw-bold">QRIS (Scan Cepat)</span>
                                </div>
                            </label>
                            <div class="qris-box">
                                <img src="assets/images/qris-placeholder.png" alt="QR Code" class="qris-img">
                                <div class="small text-white-50" style="font-size: 10px;">Gopay / OVO / Dana / LinkAja</div>
                            </div>
                        </div>

                        <span class="payment-category-title">Transfer Bank (Manual Check)</span>
                        
                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="BCA - Muhammad Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="radio-circle"></div>
                                <div>
                                    <span class="bank-name">BCA</span>
                                    <span class="bank-number">0601238461</span>
                                    <div class="bank-owner">a.n Muhammad Farelino Kelfin Ramadhani</div>
                                </div>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="Mandiri - Adhafa Joan" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="radio-circle"></div>
                                <div>
                                    <span class="bank-name">Mandiri</span>
                                    <span class="bank-number">123240069</span>
                                    <div class="bank-owner">a.n Adhafa Joan Putranto</div>
                                </div>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="BNI - Adhafa Putranto" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="radio-circle"></div>
                                <div>
                                    <span class="bank-name">BNI</span>
                                    <span class="bank-number">123240069</span>
                                    <div class="bank-owner">a.n Adhafa Putranto</div>
                                </div>
                            </div>
                        </label>

                        <span class="payment-category-title">E-Wallet / Bank Digital</span>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="Gopay - Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="radio-circle"></div>
                                <div>
                                    <span class="bank-name">Gopay</span>
                                    <span class="bank-number">089516656371</span>
                                    <div class="bank-owner">a.n Farelino</div>
                                </div>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="Shopeepay - Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="radio-circle"></div>
                                <div>
                                    <span class="bank-name">Shopeepay</span>
                                    <span class="bank-number">083894159607</span>
                                    <div class="bank-owner">Kelfin Farelno</div>
                                </div>
                            </div>
                        </label>

                        <label class="sub-payment-option">
                            <input type="radio" name="metode_bayar" value="SeaBank - Farelino" class="sub-payment-radio">
                            <div class="sub-payment-box">
                                <div class="radio-circle"></div>
                                <div>
                                    <span class="bank-name">SeaBank</span>
                                    <span class="bank-number">083894159607</span>
                                    <div class="bank-owner">a.n Farelino</div>
                                </div>
                            </div>
                        </label>

                    </div>

                    <div class="payment-card">
                        <h5 class="mb-2 fw-bold" style="font-size: 16px;">Upload Bukti Transfer</h5>
                        <div class="upload-box">
                            <i class="fa-solid fa-arrow-up-from-bracket upload-icon"></i>
                            <span class="ms-3 small text-muted" id="fileName">Klik untuk upload bukti pembayaran (JPG/PNG)</span>
                            <input type="file" name="bukti_transfer" class="file-input-hidden" accept="image/*" onchange="updateFileName(this)" required>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="summary-card">
                        <h5 class="summary-title">Ringkasan Donasi</h5>
                        <div class="summary-row"><span>Nominal</span><span class="fw-bold"><?= formatRupiah($nominal) ?></span></div>
                        <div class="summary-row"><span>Pohon</span><span class="text-end"><?= $nama_pohon ?> <br><small>(<?= $jumlah_pohon ?>x)</small></span></div>
                        <div class="summary-row"><span>Biaya Admin</span><span>Rp0</span></div>
                        <div class="summary-row summary-total"><span>Total</span><span><?= formatRupiah($nominal) ?></span></div>
                        
                        <div class="donor-info">
                            <div><?= htmlspecialchars($nama) ?></div>
                            <div><?= htmlspecialchars($email) ?></div>
                        </div>

                        <button type="submit" class="btn-pay">Konfirmasi & Bayar</button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        // Update tampilan nama file setelah upload
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                fileNameDisplay.textContent = input.files[0].name;
                fileNameDisplay.classList.remove('text-muted');
                fileNameDisplay.classList.add('text-success', 'fw-bold');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>