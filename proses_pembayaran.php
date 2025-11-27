<?php
session_start();
include "koneksi.php";

/**
 * Helper: Sanitasi input pengguna untuk keamanan dasar (XSS Prevention).
 * Catatan: SQL Injection sudah ditangani oleh Prepared Statements.
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Pastikan skrip hanya berjalan pada method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Pengolahan & Validasi Input ---
    $nominal      = cleanInput($_POST['nominal'] ?? 0);
    $nama         = cleanInput($_POST['nama'] ?? '');
    $email        = cleanInput($_POST['email'] ?? '');
    $hp           = cleanInput($_POST['hp'] ?? '');
    $alamat       = cleanInput($_POST['alamat'] ?? '-');
    $metode       = cleanInput($_POST['metode_bayar'] ?? '-');
    
    // Casting ke integer untuk data numerik
    $anonim       = (int) ($_POST['anonim'] ?? 0); 
    $pohon_id     = (int) ($_POST['pohon_id'] ?? 1);
    $jumlah_pohon = (int) ($_POST['jumlah_pohon'] ?? 1);
    
    // Generate Invoice ID Unik
    $invoice = "INV/DNX/" . date("Ymd") . "/" . rand(1000, 9999);

    // --- 2. Manajemen Data Donatur (Donors) ---
    // Cek apakah email sudah terdaftar dalam sistem
    $cekDonor = $conn->prepare("SELECT id FROM donors WHERE email = ?");
    $cekDonor->bind_param("s", $email);
    $cekDonor->execute();
    $resDonor = $cekDonor->get_result();

    if ($resDonor->num_rows > 0) {
        // Donatur lama: Perbarui data kontak terbaru
        $row = $resDonor->fetch_assoc();
        $donor_id = $row['id'];
        
        $updateDonor = $conn->prepare("UPDATE donors SET name = ?, phone = ? WHERE id = ?");
        $updateDonor->bind_param("ssi", $nama, $hp, $donor_id);
        $updateDonor->execute();
    } else {
        // Donatur baru: Buat data baru
        $stmtDonor = $conn->prepare("INSERT INTO donors (name, email, phone) VALUES (?, ?, ?)");
        $stmtDonor->bind_param("sss", $nama, $email, $hp);
        
        if ($stmtDonor->execute()) {
            $donor_id = $conn->insert_id;
        } else {
            // Log error jika diperlukan di lingkungan production
            die("Error: Gagal menyimpan data donatur.");
        }
    }

    // --- 3. Pencatatan Transaksi Donasi (Donations) ---
    // Status default 'success' untuk simulasi. Gunakan 'pending' jika terintegrasi Payment Gateway.
    $status = 'success'; 
    $date_now = date("Y-m-d H:i:s");

    $stmtDonasi = $conn->prepare("INSERT INTO donations (invoice_number, donor_id, tree_type_id, amount, tree_count, payment_method, is_anonymous, payment_status, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Parameter Binding: s=string, i=integer, d=double
    $stmtDonasi->bind_param("siidisiss", $invoice, $donor_id, $pohon_id, $nominal, $jumlah_pohon, $metode, $anonim, $status, $date_now);
    
    if ($stmtDonasi->execute()) {
        // Redirect ke halaman sukses dengan membawa ID Invoice
        header("Location: sukses_donasi.php?inv=" . urlencode($invoice));
        exit;
    } else {
        die("Error: Gagal memproses donasi. Silakan coba lagi.");
    }

} else {
    // Redirect jika akses langsung tanpa POST
    header("Location: donasi.php");
    exit;
}
?>