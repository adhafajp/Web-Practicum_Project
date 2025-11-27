<?php
session_start();
// session_destroy(); // reset session saat testing
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Oksigen - Form Donasi</title>

    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@400;700&family=Inter:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/donasi.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen Logo" style="height: 35px;">
            </a>
            <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm" style="background-color: #2F80ED; color: white; padding: 8px 24px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 14px;">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="container main-container mt-5">

        <div class="step-label">Step 1 of 3</div>
        <div class="progress-bar-container">
            <div class="progress-bar-fill"></div>
        </div>

        <h2 class="page-title">Berdonasi untuk Nafas Bumi</h2>
        <p class="page-subtitle">Pilih nominal donasi dan lihat langsung berapa banyak pohon dan oksigen yang Anda hasilkan</p>

        <form action="donasi_pembayaran.php" method="POST">
            <div class="row g-4">

                <div class="col-lg-7">

                    <div class="form-card">
                        <h4>Nominal Donasi</h4>
                        <p class="desc">Masukkan jumlah donasi dalam rupiah atau pilih salah satu opsi cepat</p>

                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <label style="font-size: 12px; font-weight: 700;">Nominal Donasi (IDR)</label>
                            <span style="font-size: 12px; color: #666;">Minimal Rp10.000</span>
                        </div>

                        <input type="text" class="nominal-value" id="manualNominal" value="Rp 20.000">
                        <input type="hidden" name="nominal_fix" id="nominalFix" value="20000">

                        <div class="nominal-badges">
                            <div class="badge-nominal" onclick="setNominal(10000)">Rp 10 K</div>
                            <div class="badge-nominal active" onclick="setNominal(20000)">Rp 20 K</div>
                            <div class="badge-nominal" onclick="setNominal(50000)">Rp 50 K</div>
                            <div class="badge-nominal" onclick="setNominal(100000)">Rp 100 K</div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h4>Pilih Jenis Pohon (Opsional)</h4>
                        <p class="desc">Pilih jenis pohon yang ingin Anda dukung.</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="tree-label-wrapper">
                                    <input type="radio" name="pohon_id" value="1" class="tree-checkbox" checked>
                                    <div class="tree-content-block">
                                        <div class="tree-img-container"><img src="assets/images/pohon-mangga.jpeg" class="tree-img" alt="Mangga"></div>
                                        <div class="tree-content-row">
                                            <div class="selection-circle"></div>
                                            <div>
                                                <h5 class="tree-title">Mangga</h5>
                                                <div class="tree-desc">Buah & Peneduh</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="tree-label-wrapper">
                                    <input type="radio" name="pohon_id" value="2" class="tree-checkbox">
                                    <div class="tree-content-block">
                                        <div class="tree-img-container"><img src="assets/images/pohon-mahoni.jpeg" class="tree-img" alt="Mahoni"></div>
                                        <div class="tree-content-row">
                                            <div class="selection-circle"></div>
                                            <div>
                                                <h5 class="tree-title">Mahoni</h5>
                                                <div class="tree-desc">Penyerap CO2</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="tree-label-wrapper">
                                    <input type="radio" name="pohon_id" value="3" class="tree-checkbox">
                                    <div class="tree-content-block">
                                        <div class="tree-img-container"><img src="assets/images/pohon-bakau.jpeg" class="tree-img" alt="Bakau"></div>
                                        <div class="tree-content-row">
                                            <div class="selection-circle"></div>
                                            <div>
                                                <h5 class="tree-title">Bakau</h5>
                                                <div class="tree-desc">Cegah Abrasi</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h4>Data Donatur</h4>
                        <p class="desc">Isi data Anda untuk mengirimkan bukti donasi.</p>

                        <label class="form-label-custom">Nama Lengkap</label>
                        <input type="text" class="form-control-custom" name="nama" required>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label-custom">Email</label>
                                <input type="email" class="form-control-custom" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">No. HP</label>
                                <input type="text" class="form-control-custom" name="hp">
                            </div>
                        </div>

                        <label class="form-label-custom">Alamat</label>
                        <textarea class="form-control-custom" name="alamat" rows="2" placeholder="Masukkan alamat lengkap (tidak wajib)"></textarea>

                        <div class="form-check form-switch bg-light p-2 rounded ps-5 mt-2">
                            <input class="form-check-input" type="checkbox" id="anonim" name="is_anonymous" style="margin-left: -30px;">
                            <label class="form-check-label" for="anonim" style="font-size: 14px; color: #666; margin-left: 10px;">Sembunyikan nama saya (Anonim)</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="impact-card">
                        <h5>Dampak Donasi Anda</h5>
                        <div class="impact-text-block">
                            <h6 id="impactTreeText">Anda menanam <span id="impactTree">2</span> pohon baru</h6>
                            <p>Estimasi nominal Rp<span id="impactNominal">20.000</span></p>
                        </div>
                        <div class="impact-text-block">
                            <h6>Memproduksi sekitar <span id="impactOxygen">200</span> liter oksigen segar per hari.</h6>
                            <p>Membantu menyerap polusi udara di perkotaan.</p>
                        </div>
                        <p style="font-size: 11px; color: #888; line-height: 1.4; margin-top: 20px;">*Perhitungan bersifat estimasi (1 Pohon = Rp 10.000)</p>
                    </div>
                </div>
            </div>

            <div class="floating-bar">
                <div class="container d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size: 14px; color: #666;">Langkah berikutnya: Konfirmasi metode pembayaran.</div>
                    </div>
                    <button type="submit" class="btn-submit-custom">Lanjut ke Pembayaran</button>
                </div>
            </div>

        </form>
    </div>

    <script src="js/donasi.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>