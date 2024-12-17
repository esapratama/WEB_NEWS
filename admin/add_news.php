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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $collection = $db->news;
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi apakah file adalah gambar
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "File is not an image.";
            $uploadOk = 0;
        }

        // Cek jika file sudah ada
        if (file_exists($target_file)) {
            $message = "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Cek ukuran file, max 500kb
        if ($_FILES["image"]["size"] > 500_000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Validasi format file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Jika tidak ada error, proses upload
        if ($uploadOk != 0) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $result = $collection->insertOne([
                    'title' => $_POST['title'],
                    'content' => $_POST['content'],
                    'summary' => $_POST['summary'],
                    'author' => $_POST['author'],
                    'category' => $_POST['category'],
                    'created_at' => new MongoDB\BSON\UTCDateTime(),
                    'updated_at' => new MongoDB\BSON\UTCDateTime(),
                    'image' => basename($_FILES["image"]["name"]) // Simpan hanya nama file
                ]);

                // Pesan pemberitahuan
                $message = "Berita berhasil ditambahkan!";
                header('Location: manage_news.php');
                exit;
            } else {
                $message = "Sorry, there was an error uploading your file.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    // Fungsi untuk menampilkan alert jika ada pesan
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
                        <a class="nav-link active" href="add_news.php">Tambah Berita</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Form Tambah Berita -->
    <div class="container mt-4">
        <h1>Tambah Berita</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Judul</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Konten</label>
                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="summary" class="form-label">Ringkasan</label>
                <textarea class="form-control" id="summary" name="summary" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Penulis</label>
                <input type="text" class="form-control" id="author" name="author" required>
            </div>
            <!-- Kategori Dropdown -->
            <div class="mb-3">
                <label for="category" class="form-label">Kategori</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Pilih Kategori</option>
                    <option value="politik">Politik</option>
                    <option value="bencana">Bencana</option>
                    <option value="lalu-lintas">Lalu Lintas</option>
                    <option value="pendidikan">Pendidikan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Gambar</label>
                <input type="file" class="form-control" id="image" name="image" accept=".jpg, .jpeg, .png, .gif">
            </div>
            <br>
            <button type="submit" class="btn btn-primary mb-3">Simpan</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>