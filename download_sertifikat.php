<?php
// download_sertifikat.php
session_start();
require 'koneksi.php';

// Validasi Akses (Cek Parameter Invoice)
if (!isset($_GET['inv'])) {
    http_response_code(400); // Bad Request
    die("Error: Parameter Invoice tidak ditemukan.");
}

$invoice_number = urldecode($_GET['inv']);

// Pengecekan Fungsi GD Library
if (!function_exists('imagecreatefromjpeg')) {
    http_response_code(500);
    die("Fatal Error: Ekstensi GD Library PHP tidak diaktifkan. Harap aktifkan 'extension=gd' di php.ini.");
}

// Ambil nama donatur, jenis pohon, jumlah pohon, dan tanggal transaksi
$sql = "SELECT d.invoice_number, d.tree_count, d.transaction_date, d.is_anonymous,
              u.name AS nama_donatur, 
              t.name AS jenis_pohon
        FROM donations d
        LEFT JOIN donors u ON d.donor_id = u.id
        LEFT JOIN tree_types t ON d.tree_type_id = t.id
        WHERE d.invoice_number = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $invoice_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die("Error: Data donasi tidak ditemukan.");
}

$data = $result->fetch_assoc();

// Siapkan Data Variabel untuk Sertifikat
// Jika anonim, gunakan nama samaran
$nama_tampil = ($data['is_anonymous'] == 1) ? "Hamba Allah" : strtoupper($data['nama_donatur']);
$jumlah_pohon = (int)$data['tree_count'];
// Default 'Pohon' jika jenis pohon null atau kosong
$jenis_pohon  = (!empty($data['jenis_pohon'])) ? strtoupper($data['jenis_pohon']) : 'POHON';
$tanggal_transaksi = date("d F Y", strtotime($data['transaction_date']));

// Hitung dampak oksigen (Logika: 1 pohon = 100 Liter/hari)
$oksigen = number_format($jumlah_pohon * 100, 0, ',', '.');

// Konfigurasi Aset (Path File)
$base_dir      = __DIR__; 
$template_path = $base_dir . '/assets/images/template-sertif.jpeg';
$font_bold     = $base_dir . '/assets/fonts/poppins.bold.ttf';
$font_reg      = $base_dir . '/assets/fonts/poppins.regular.ttf';

// Validasi keberadaan file aset
if (!file_exists($template_path)) die("Error: Template gambar tidak ditemukan di $template_path");
if (!file_exists($font_bold)) die("Error: Font Bold tidak ditemukan di $font_bold");
if (!file_exists($font_reg)) die("Error: Font Regular tidak ditemukan di $font_reg");

// Buat Kanvas Gambar dari Template
$image = imagecreatefromjpeg($template_path);
if (!$image) {
    http_response_code(500);
    die("Error: Gagal memuat gambar template. Pastikan format file adalah JPEG dan tidak rusak.");
}

$img_width  = imagesx($image);
$img_height = imagesy($image);

// Definisi Warna (RGB)
$color_dark_green = imagecolorallocate($image, 21, 87, 36);    
$color_black      = imagecolorallocate($image, 50, 50, 50);    
// $color_highlight  = imagecolorallocate($image, 46, 125, 50); // Tidak terpakai saat ini

// --- Helper Function: Teks Rata Tengah ---
if (!function_exists('printCenteredText')) {
    function printCenteredText($img, $size, $angle, $font, $text, $y, $color, $width)
    {
        $bbox = imagettfbbox($size, $angle, $font, $text);
        $text_width = $bbox[2] - $bbox[0];
        $x = ($width - $text_width) / 2;
        imagettftext($img, $size, $angle, $x, $y, $color, $font, $text);
    }
}


// Tulis Teks ke Sertifikat (Sesuaikan Koordinat Y)
// Koordinat Y (argumen ke-5)

// NAMA DONATUR (Besar di Tengah)
printCenteredText($image, 70, 0, $font_bold, $nama_tampil, 650, $color_dark_green, $img_width);

// NARASI DAMPAK (Detail Donasi)
$text_line1 = "Atas kontribusi nyata dalam menanam " . $jumlah_pohon . " " . $jenis_pohon;
printCenteredText($image, 24, 0, $font_reg, $text_line1, 750, $color_black, $img_width);

$text_line2 = "dan membantu menghasilkan " . $oksigen . " liter oksigen / hari untuk bumi.";
printCenteredText($image, 24, 0, $font_reg, $text_line2, 800, $color_black, $img_width);

// TANGGAL (Di Tengah Bawah)
$text_date = "Tanggal " . $tanggal_transaksi;
printCenteredText($image, 20, 0, $font_bold, $text_date, 850, $color_black, $img_width);

// Output File (Download)
if (ob_get_length()) ob_clean();

header('Content-Type: image/jpeg');
// Membuat nama file yang bersih
$filename_safe = "Sertifikat_Donoxygen_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nama_tampil) . ".jpg";
header('Content-Disposition: attachment; filename="' . $filename_safe . '"');

imagejpeg($image, NULL, 90); // Parameter ke-3: kualitas 0-100 (90 cukup baik)
imagedestroy($image);
exit;