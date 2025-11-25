<?php
// session_start();
// if (!isset($_SESSION['user_id'])) { header("Location: auth.php"); exit; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Oksigen - Form Donasi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* --- CSS KHUSUS HALAMAN DONASI --- */
        body {
            background-color: var(--light-grey-bg);
            padding-top: 80px;
        }

        .donation-header { padding: 40px 0; }

        .progress-bar-container {
            height: 6px; background-color: #E0E0E0; border-radius: 10px; margin-bottom: 30px; overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%; background-color: var(--primary-green); width: 33%; border-radius: 10px;
        }

        .form-card {
            background: white; border-radius: 15px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); margin-bottom: 30px;
        }
        .form-card h4 { margin-bottom: 25px; }

        /* Pilihan Nominal */
        .nominal-option { display: none; }
        .nominal-label {
            display: block; text-align: center; padding: 12px; background-color: var(--light-grey-bg);
            border: 2px solid transparent; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .nominal-option:checked + .nominal-label {
            border-color: var(--primary-green); background-color: var(--light-green-bg); color: var(--primary-green);
        }

        .input-nominal-manual {
            font-size: 1.5rem; font-weight: 700; border: none; border-bottom: 2px solid #ddd; border-radius: 0; padding: 10px 0;
        }
        .input-nominal-manual:focus { box-shadow: none; border-color: var(--primary-green); }

        /* Pilihan Pohon */
        .tree-card {
            background: var(--light-grey-bg); border-radius: 15px; padding: 20px; text-align: center;
            cursor: pointer; border: 2px solid transparent; transition: 0.3s; height: 100%;
        }
        .tree-option:checked + .tree-card { border-color: var(--primary-green); background-color: var(--light-green-bg); }
        .tree-image { width: 100%; height: 120px; object-fit: contain; margin-bottom: 15px; background-color: #ddd; border-radius: 10px; }
        .tree-name { font-weight: 700; margin-bottom: 5px; }
        .tree-desc { font-size: 0.8rem; color: var(--grey-text); }

        /* Impact Card */
        .impact-card {
            background: var(--light-green-bg); border-radius: 15px; padding: 30px; position: sticky; top: 100px;
        }
        .impact-icon {
            width: 60px; height: 60px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: var(--primary-green); font-size: 1.5rem; margin-bottom: 20px;
        }

        .form-control-lg { border-radius: 10px; padding: 15px; font-size: 1rem; border: 1px solid #ddd; }
        .form-check-input:checked { background-color: var(--primary-green); border-color: var(--primary-green); }

        /* Footer Floating */
        .floating-submit-bar {
            position: fixed; bottom: 0; left: 0; width: 100%; background: white;
            padding: 20px; box-shadow: 0 -5px 20px rgba(0,0,0,0.05); z-index: 100;
        }
        
        .main-content { margin-bottom: 100px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen Logo"><!--style="height: 40px; width: 150px;"-->
            </a>
            <div class="d-flex">
                <a href="donasi.php" class="btn btn-donasi-sm">Donasi Sekarang</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <section class="donation-header container mt-5 pt-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Step 1 of 3</span>
                        <span>Isi Data Donasi</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill"></div>
                    </div>
                    <h2 class="mb-3">Berdonasi untuk Nafas Bumi</h2>
                    <p class="text-muted">Pilih nominal donasi dan lihat langsung berapa banyak pohon dan oksigen yang anda hasilkan.</p>
                </div>
            </div>
        </section>

        <section class="container mb-5">
            <form action="proses_donasi.php" method="POST">
                <div class="row g-5">
                    <div class="col-lg-8">
                        
                        <div class="form-card">
                            <h4>Nominal Donasi</h4>
                            <p class="text-muted mb-4">Masukkan jumlah donasi dalam rupiah atau pilih salah satu opsi cepat.</p>
                            
                            <div class="mb-4">
                                <label class="form-label small text-muted">Nominal Donasi (IDR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0 ps-0 fs-4 fw-bold">Rp</span>
                                    <input type="number" class="form-control input-nominal-manual" id="manualNominal" placeholder="0" value="20000">
                                </div>
                                <div class="text-end small text-muted mt-1">Minimal Rp10.000</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6 col-md-3"><input type="radio" class="nominal-option" name="nominal_cepat" id="nom10k" value="10000"><label class="nominal-label" for="nom10k">Rp 10 K</label></div>
                                <div class="col-6 col-md-3"><input type="radio" class="nominal-option" name="nominal_cepat" id="nom20k" value="20000" checked><label class="nominal-label" for="nom20k">Rp 20 K</label></div>
                                <div class="col-6 col-md-3"><input type="radio" class="nominal-option" name="nominal_cepat" id="nom50k" value="50000"><label class="nominal-label" for="nom50k">Rp 50 K</label></div>
                                <div class="col-6 col-md-3"><input type="radio" class="nominal-option" name="nominal_cepat" id="nom100k" value="100000"><label class="nominal-label" for="nom100k">Rp 100 K</label></div>
                            </div>
                        </div>

                        <div class="form-card">
                            <h4>Pilih Jenis Pohon (Opsional)</h4>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="radio" class="tree-option d-none" name="jenis_pohon" id="pohon1" value="mangga">
                                    <label class="tree-card d-block" for="pohon1">
                                        <img src="assets/images/pohon-mangga.png" class="tree-image" alt="Mangga">
                                        <div class="tree-name">Mangga</div><div class="tree-desc">Buah & Peneduh</div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="tree-option d-none" name="jenis_pohon" id="pohon2" value="mahoni">
                                    <label class="tree-card d-block" for="pohon2">
                                        <img src="assets/images/pohon-mahoni.png" class="tree-image" alt="Mahoni">
                                        <div class="tree-name">Mahoni</div><div class="tree-desc">Penyerap CO2</div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="tree-option d-none" name="jenis_pohon" id="pohon3" value="bakau">
                                    <label class="tree-card d-block" for="pohon3">
                                        <img src="assets/images/pohon-bakau.png" class="tree-image" alt="Bakau">
                                        <div class="tree-name">Bakau</div><div class="tree-desc">Cegah Abrasi</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-card">
                            <h4>Data Donatur</h4>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control form-control-lg" name="nama_donatur" required>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control form-control-lg" name="email_donatur" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">No. HP</label>
                                    <input type="tel" class="form-control form-control-lg" name="hp_donatur" required>
                                </div>
                            </div>
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" role="switch" id="anonimSwitch" name="is_anonymous">
                                <label class="form-check-label" for="anonimSwitch">Sembunyikan nama saya (Donasi sebagai anonim)</label>
                            </div>
                        </div>
                    </div> 

                    <div class="col-lg-4">
                        <div class="impact-card">
                            <h5 class="mb-4">Dampak Donasi Anda</h5>
                            <div class="d-flex mb-4">
                                <div class="impact-icon flex-shrink-0"><i class="fa-solid fa-tree"></i></div>
                                <div>
                                    <h6 class="fw-bold">Anda menanam <span id="impactTreeCount">2</span> pohon baru</h6>
                                    <p class="small text-muted mb-0">Estimasi nominal Rp<span id="impactNominal">20.000</span></p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="impact-icon flex-shrink-0"><i class="fa-solid fa-wind"></i></div>
                                <div>
                                    <h6 class="fw-bold">~<span id="impactOxygen">200</span> liter oksigen/hari.</h6>
                                    <p class="small text-muted mb-0">*Estimasi pohon dewasa</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="floating-submit-bar">
                    <div class="container d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Langkah berikutnya:</div>
                            <div class="fw-bold">Pembayaran</div>
                        </div>
                        <button type="submit" class="btn btn-donasi-sm btn-lg px-5">Lanjut</button>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <img src="assets/images/logo-donoxygen.svg" alt="Logo Putih" class="mb-4" style="height: 40px; width: 150px;">
                    <p style="color: #e0e0e0;">Misi kami sederhana: menghubungkan donatur, komunitas, dan alam untuk menghadirkan nafas baru bagi bumi.</p>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><a href="donasi.php">Donasi</a></li>
                        <li><a href="#">Laporan</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5>Kontak</h5>
                    <p style="color: #e0e0e0;">halo@donoxygen.com</p>
                </div>
            </div>
            <div class="footer-bottom mt-4 pt-3 border-top border-secondary text-center">
                <p class="small text-muted">© 2025 Donoxygen. All right reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const manualInput = document.getElementById('manualNominal');
            const radioOptions = document.querySelectorAll('.nominal-option');
            const impactTreeCount = document.getElementById('impactTreeCount');
            const impactNominal = document.getElementById('impactNominal');
            const impactOxygen = document.getElementById('impactOxygen');
            const hargaPerPohon = 10000; 
            const oksigenPerPohon = 100; 

            function updateImpact(nominal) {
                impactNominal.textContent = new Intl.NumberFormat('id-ID').format(nominal);
                const treeCount = Math.floor(nominal / hargaPerPohon);
                impactTreeCount.textContent = treeCount;
                impactOxygen.textContent = new Intl.NumberFormat('id-ID').format(treeCount * oksigenPerPohon);
            }

            radioOptions.forEach(option => {
                option.addEventListener('change', function() {
                    if (this.checked) {
                        manualInput.value = this.value;
                        updateImpact(this.value);
                    }
                });
            });

            manualInput.addEventListener('input', function() {
                updateImpact(parseInt(this.value) || 0);
                radioOptions.forEach(opt => opt.checked = false);
            });
            updateImpact(manualInput.value);
        });
    </script>
</body>
</html>