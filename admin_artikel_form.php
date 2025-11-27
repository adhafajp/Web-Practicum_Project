<?php
// admin_artikel_form.php
session_start();
include 'koneksi.php';

$id = "";
$title = "";
$slug = "";
$category = "";
$content = "";
$thumbnail_url = "";
$is_published = 0;
$is_edit = false;
$error_message = "";

// 1. Cek parameter ID untuk menentukan mode edit atau tambah baru
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM articles WHERE id = $id";
    $query = mysqli_query($conn, $sql);
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $title = $data['title'];
        $slug = $data['slug'];
        $category = $data['category'];
        $content = $data['content'];
        $thumbnail_url = $data['thumbnail_url'];
        $is_published = $data['is_published'];
        $is_edit = true;
    }
}

// 2. Proses penyimpanan data (Create / Update)
if (isset($_POST['simpan'])) {
    $title_input = mysqli_real_escape_string($conn, $_POST['title']);
    $category_input = mysqli_real_escape_string($conn, $_POST['category']);
    $content_input = mysqli_real_escape_string($conn, $_POST['content']);
    $status_input = (int)$_POST['is_published'];
    
    // Generate slug otomatis dari judul
    $slug_input = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title_input)));
    
    // Logika upload gambar
    $final_thumbnail = $thumbnail_url; // Gunakan gambar lama sebagai default
    
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $target_dir = "assets/images/uploads/";
        
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_name = time() . "_" . basename($_FILES['thumbnail']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file)) {
            $final_thumbnail = $target_file;
        }
    }

    $author_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

    $success = false;

    if ($is_edit) {
        // Update data artikel
        $sqlUpdate = "UPDATE articles SET 
                      title = '$title_input',
                      slug = '$slug_input',
                      category = '$category_input',
                      content = '$content_input',
                      thumbnail_url = '$final_thumbnail',
                      is_published = '$status_input'
                      WHERE id = $id";
        if(mysqli_query($conn, $sqlUpdate)) {
            $success = true;
        }
    } else {
        // Insert data artikel baru
        $sqlInsert = "INSERT INTO articles (title, slug, content, category, thumbnail_url, author_id, is_published) 
                      VALUES ('$title_input', '$slug_input', '$content_input', '$category_input', '$final_thumbnail', '$author_id', '$status_input')";
        if(mysqli_query($conn, $sqlInsert)) {
            $success = true;
        }
    }

    // PERUBAHAN UTAMA DI SINI
    if ($success) {
        // Set pesan ke session untuk ditampilkan di halaman admin_artikel.php via SweetAlert
        $_SESSION['flash_message'] = "Artikel berhasil disimpan!";
        $_SESSION['flash_type'] = "success";
        
        // Redirect bersih (PRG Pattern)
        header("Location: admin_artikel.php");
        exit();
    } else {
        $error_message = "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Artikel - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/admin_artikel_form.css">
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php" style="text-decoration: none;">
            <img src="assets/images/logo-donoxygen.svg" alt="Logo Donoxygen">
        </a>
    </div>
    <div class="small text-white-50 mb-4 px-2" style="margin-top: -10px;">Admin Dashboard</div>
    
    <a href="admin_donasi.php" class="nav-link">Data Donatur</a>
    <a href="admin_artikel.php" class="nav-link active">Artikel</a>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><?= $is_edit ? 'Edit Artikel' : 'Tulis Artikel Baru' ?></h2>
        <a href="admin_artikel.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label class="form-label">Judul Artikel</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Manfaat Mangrove..." value="<?= htmlspecialchars($title) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konten Artikel</label>
                        <textarea name="content" id="summernote" required><?= $content ?></textarea>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <label class="form-label">Status Publikasi</label>
                        <select name="is_published" class="form-select">
                            <option value="1" <?= $is_published == 1 ? 'selected' : '' ?>>Published (Tayang)</option>
                            <option value="0" <?= $is_published == 0 ? 'selected' : '' ?>>Draft (Simpan Dulu)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Manfaat Hutan" <?= $category == 'Manfaat Hutan' ? 'selected' : '' ?>>Manfaat Hutan</option>
                            <option value="Manfaat Oksigen" <?= $category == 'Manfaat Oksigen' ? 'selected' : '' ?>>Manfaat Oksigen</option>
                            <option value="Tips & Aksi" <?= $category == 'Tips & Aksi' ? 'selected' : '' ?>>Tips & Aksi</option>
                            <option value="Wawasan Hijau" <?= $category == 'Wawasan Hijau' ? 'selected' : '' ?>>Wawasan Hijau</option>
                            <option value="Ancaman Deforestasi" <?= $category == 'Ancaman Deforestasi' ? 'selected' : '' ?>>Ancaman Deforestasi</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Gambar Thumbnail</label>
                        <input type="file" name="thumbnail" class="form-control mb-2" accept="image/*" onchange="previewImage(event)">
                        <small class="text-muted d-block mb-2">*Biarkan kosong jika tidak ingin mengubah gambar.</small>
                        
                        <?php if(!empty($thumbnail_url)): ?>
                            <img id="preview" src="<?= $thumbnail_url ?>" class="img-preview" alt="Preview">
                        <?php else: ?>
                            <img id="preview" src="https://via.placeholder.com/300x150?text=No+Image" class="img-preview" alt="Preview">
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-save w-100">
                        <i class="fa-solid fa-save"></i> Simpan Artikel
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    // Inisialisasi Summernote
    $(document).ready(function() {
        $('#summernote').summernote({
            placeholder: 'Tulis isi artikel di sini...',
            tabsize: 2,
            height: 400,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });

    // Preview gambar upload
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('preview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>