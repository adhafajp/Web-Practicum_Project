<?php
session_start();
include "koneksi.php";

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nominal      = cleanInput($_POST['nominal'] ?? 0);
    $nama         = cleanInput($_POST['nama'] ?? '');
    $email        = cleanInput($_POST['email'] ?? '');
    $hp           = cleanInput($_POST['hp'] ?? '');
    $alamat       = cleanInput($_POST['alamat'] ?? '-');
    $metode       = cleanInput($_POST['metode_bayar'] ?? '-');
    
    $anonim       = (int) ($_POST['anonim'] ?? 0); 
    $pohon_id     = (int) ($_POST['pohon_id'] ?? 1);
    $jumlah_pohon = (int) ($_POST['jumlah_pohon'] ?? 1);
    
    // Generate Invoice ID Unik
    $invoice = "INV/DNX/" . date("Ymd") . "/" . rand(1000, 9999);

    // --- LOGIKA UPLOAD BUKTI TRANSFER ---
    $nama_file_bukti = null;

    // Cek apakah ada file yang diupload dan tidak error
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === 0) {
        
        $file_name = $_FILES['bukti_transfer']['name'];
        $file_tmp  = $_FILES['bukti_transfer']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 
            'bmp', 'svg', 'tiff', 'tif', 'ico', 'heic'
        ];

        // CEK MIME TYPE
        // Fungsi ini membaca header file untuk mengetahui tipe aslinya
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (in_array($file_ext, $allowed_extensions) && strpos($mime_type, 'image/') === 0) {
            
            $nama_file_bukti = "bukti_" . time() . "_" . rand(100, 999) . "." . $file_ext;
            
            $target_dir = "assets/uploads/";
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (!move_uploaded_file($file_tmp, $target_dir . $nama_file_bukti)) {
                die("Error: Gagal mengupload gambar. Periksa izin folder.");
            }
        } else {
            die("Error: Format file tidak valid. Harap upload file gambar (JPG, PNG, GIF, WEBP, dll).");
        }
    }
    // ------------------------------------------------

    // --- Manajemen Data Donatur (Donors) ---
    $cekDonor = $conn->prepare("SELECT id FROM donors WHERE email = ?");
    $cekDonor->bind_param("s", $email);
    $cekDonor->execute();
    $resDonor = $cekDonor->get_result();

    if ($resDonor->num_rows > 0) {
        $row = $resDonor->fetch_assoc();
        $donor_id = $row['id'];
        
        $updateDonor = $conn->prepare("UPDATE donors SET name = ?, phone = ? WHERE id = ?");
        $updateDonor->bind_param("ssi", $nama, $hp, $donor_id);
        $updateDonor->execute();
    } else {
        $stmtDonor = $conn->prepare("INSERT INTO donors (name, email, phone) VALUES (?, ?, ?)");
        $stmtDonor->bind_param("sss", $nama, $email, $hp);
        
        if ($stmtDonor->execute()) {
            $donor_id = $conn->insert_id;
        } else {
            die("Error: Gagal menyimpan data donatur.");
        }
    }

    // --- Pencatatan Transaksi Donasi (Donations) ---
    $status = 'pending'; 
    $date_now = date("Y-m-d H:i:s");

    $stmtDonasi = $conn->prepare("INSERT INTO donations (invoice_number, donor_id, tree_type_id, amount, tree_count, payment_method, is_anonymous, payment_status, payment_proof, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtDonasi->bind_param("siidisisss", $invoice, $donor_id, $pohon_id, $nominal, $jumlah_pohon, $metode, $anonim, $status, $nama_file_bukti, $date_now);
    
    if ($stmtDonasi->execute()) {
        header("Location: donasi_sukses.php?inv=" . urlencode($invoice));
        exit;
    } else {
        die("Error: Gagal memproses donasi. Silakan coba lagi.");
    }

} else {
    header("Location: donasi.php");
    exit;
}
?>