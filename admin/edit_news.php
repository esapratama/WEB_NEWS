<?php
    session_start();
    require '../config/db.php';

    // Redirect jika user belum login
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }

    // Variabel untuk pesan pemberitahuan
    $message = "";

    // Validasi ID dari URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("ID berita tidak valid.");
    }

    $id = new MongoDB\BSON\ObjectId($_GET['id']);
    $news = $db->news->findOne(['_id' => $id]);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $collection = $db->news;
        $target_dir = "../images/";
        $image_path = $news['image'] ?? ''; // Default ke gambar lama jika tidak ada yang baru
        $old_image_path = $image_path;

        // Cek apakah file baru diunggah
        if (!empty($_FILES["image"]["name"])) {
            $image_name = basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validasi tipe file
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $message = "File bukan gambar.";
                $uploadOk = 0;
            }

            // Cek ukuran file (maksimal 2MB)
            if ($_FILES["image"]["size"] > 2000000) {
                $message = "Ukuran file terlalu besar (maksimal 2MB).";
                $uploadOk = 0;
            }

            // Hanya izinkan format file tertentu
            $allowed_types = ["jpg", "jpeg", "png", "gif"];
            if (!in_array($imageFileType, $allowed_types)) {
                $message = "Hanya format JPG, JPEG, PNG, dan GIF yang diizinkan.";
                $uploadOk = 0;
            }

            // Proses upload file baru
            if ($uploadOk) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $image_name; // Simpan hanya nama file
                } else {
                    $message = "Gagal mengunggah file.";
                }
            }
        }

        // Update data di database
        $result = $collection->updateOne(
            ['_id' => $id],
            [
                '$set' => [
                    'title' => $_POST['title'],
                    'content' => $_POST['content'],
                    'summary' => $_POST['summary'],
                    'author' => $_POST['author'],
                    'category' => $_POST['category'],
                    'updated_at' => new MongoDB\BSON\UTCDateTime(),
                    'image' => $image_path
                ]
            ]
        );

        if ($result->getModifiedCount() > 0) {
            if ($image_path !== $old_image_path && file_exists($target_dir . $old_image_path)) {
                unlink($target_dir . $old_image_path);
            }
            $message = "Berita berhasil diupdate!";
            header('Location: manage_news.php');
            exit;
        } else {
            $message = "Tidak ada perubahan yang disimpan.";
        }
    }
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function showMessage(message) {
            if (message) {
                alert(message);
            }
        }
    </script>
</head>

<body onload="showMessage('<?= $message ?>')">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="manage_news.php">Admin <span class="text-danger">Polinews</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="manage_news.php">Kelola Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="edit_news.php">Edit Berita</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Form Edit Berita -->
    <div class="container mt-4">
        <h1>Edit Berita</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Judul</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($news['title'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Konten</label>
                <textarea class="form-control" id="content" name="content" rows="5" required><?= htmlspecialchars($news['content'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="summary" class="form-label">Ringkasan</label>
                <textarea class="form-control" id="summary" name="summary" rows="3" required><?= htmlspecialchars($news['summary'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Penulis</label>
                <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($news['author'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Kategori</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Pilih Kategori</option>
                    <option value="politik" <?= $news['category'] === 'politik' ? 'selected' : '' ?>>Politik</option>
                    <option value="bencana" <?= $news['category'] === 'bencana' ? 'selected' : '' ?>>Bencana</option>
                    <option value="lalu-lintas" <?= $news['category'] === 'lalu-lintas' ? 'selected' : '' ?>>Lalu Lintas</option>
                    <option value="pendidikan" <?= $news['category'] === 'pendidikan' ? 'selected' : '' ?>>Pendidikan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Gambar</label>
                <input type="file" class="form-control" id="image" name="image" accept=".jpg, .jpeg, .png, .gif">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
