<?php
require 'config/db.php';

// Ambil query pencarian dari input pengguna
$searchQuery = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : "";

// Koleksi MongoDB
$collection = $db->news;

// Filter pencarian jika ada input pengguna
$filter = [];
if ($searchQuery) {
    $filter['$or'] = [
        ['title' => new MongoDB\BSON\Regex($searchQuery, 'i')],
        ['content' => new MongoDB\BSON\Regex($searchQuery, 'i')]
    ];
}

// Ambil berita berdasarkan filter dan urutan berdasarkan tanggal terbaru
$cursor = $collection->find($filter, ['sort' => ['created_at' => -1]]);
$newsList = iterator_to_array($cursor);

// Halaman detail berita jika ada
$news = null;
if (isset($_GET['id'])) {
    try {
        $id = new MongoDB\BSON\ObjectId($_GET['id']);
        $news = $collection->findOne(['_id' => $id]);
        if (!$news) {
            echo "<p>Berita tidak ditemukan.</p>";
            exit;
        }
    } catch (Exception $e) {
        echo "<p>ID tidak valid.</p>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $news ? htmlspecialchars($news['title']) . " | PoliNews" : "PoliNews" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <style>
    /* .card-text-custom {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    } */

    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .footer {
        margin-top: auto;
    }

    .container {
        flex-grow: 1;
    }



    .custom-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 0 160px;
    }

    .card-text-custom {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .featured-card {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
    }

    .featured-card img {
        filter: brightness(70%);
        width: 100%;
        height: auto;
    }

    .featured-card .card-img-overlay {
        background: rgba(0, 0, 0, 0.6);
        color: white;
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-light bg-light shadow-sm sticky-top">
        <div class="container custom-container">
            <a class="navbar-brand fw-bold text-danger" href="index.php" style="font-size: 36px;">PoliNews</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <!-- <li class="nav-item"><a class="nav-link" href="#">All</a></li> -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Kategori</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="index.php?category=politik">Politik</a>
                            <a class="dropdown-item" href="index.php?category=bencana">Bencana</a>
                            <a class="dropdown-item" href="index.php?category=lalu-lintas">Lalu Lintas</a>
                            <a class="dropdown-item" href="index.php?category=pendidikan">Pendidikan</a>
                        </div>
                    </li>
                </ul>
                <form class="d-flex" method="get" action="index.php">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search"
                        value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="btn btn-outline-danger me-2" type="submit">Search</button>
                </form>
                <a href="admin/login.php">
                    <button class="btn btn-danger me-2" type="button">Login</button>
                </a>
            </div>
        </div>
    </nav>

    <div class="container custom-container mt-4">
        <?php if ($news): ?>
        <!-- Halaman Detail Berita -->
        <div class="row">
            <div class="text-white text-center py-5 d-flex align-items-center justify-content-center"
                style="height: 400px;">
                <img src="<?= isset($news['image']) ? 'images/' . $news['image'] : 'https://placehold.co/300x200' ?>"
                    class="card-img-top img-fluid rounded shadow-lg" alt="News Image"
                    style="max-height: 100%; max-width: 100%; object-fit: cover; padding:0px;">
            </div>
            <h2 class="mt-4 fw-bold"><?= htmlspecialchars($news['title']) ?></h2>
            <div class="d-flex align-items-center mt-2 mb-4">
                <i class="fas fa-user-circle me-2 fs-2" style="font-size: 40px; color: #6c757d;"></i>
                <span class="fw-semibold fs-5"><?= htmlspecialchars($news['author']) ?></span>
                <span class="mx-3"><?= $news['created_at']->toDateTime()->format('Y-m-d H:i:s') ?></span>
                <span class="badge bg-danger"><?= htmlspecialchars($news['category']) ?></span>
            </div>
            <div class="text-justify" style="font-size: 18px;">
                <p class="text-justify" style="text-align: justify;"><?= nl2br(htmlspecialchars($news['content'])) ?>
                </p>
            </div>
            <a href="index.php" class="btn btn-secondary mt-3 mb-5">Kembali ke Berita</a>
            <hr>
            <h4 class="fw-bold mb-4 mt-4" style="font-size: 20px;">Berita Lainnya <a href="index.php"
                    class="float-end text-danger fw-bold">See All</a></h4>
            <div class="row">
                <?php foreach ($newsList as $news): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="<?= isset($news['image']) ? 'images/' . $news['image'] : 'https://placehold.co/300x200' ?>"
                            class="card-img-top" alt="News Image">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
                            <p class="card-text card-text-custom"><?= htmlspecialchars($news['summary']) ?></p>
                            <a href="index.php?id=<?= $news['_id'] ?>" class="btn btn-danger">Selengkapnya</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Halaman Berita -->
        <h4 class="mb-4">Berita Lainnya</h4>
        <?php if ($searchQuery): ?>
        <p>Hasil pencarian untuk: <strong><?= htmlspecialchars($searchQuery) ?></strong></p>
        <?php endif; ?>
        <div class="row">
            <?php foreach ($newsList as $news): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img src="<?= isset($news['image']) ? 'images/' . $news['image'] : 'https://placehold.co/300x200' ?>"
                        class="card-img-top" alt="News Image">
                    class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>">
                    <div class="card-body">
                        <h5 class="card-title card-text-custom fw-semibold"><?= $news['title'] ?></h5>
                        <p class="card-text card-text-custom"><?= $news['summary'] ?></p>
                        <a href="index.php?id=<?= $news['_id'] ?>" class="btn btn-danger">Selengkapnya</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer fixed-bottom">
        <footer class="bg-light text-center text-lg-start mt-auto">
            <!-- Section: Contact -->
            <div class="text-center p-3">
                © 2024 <span class="text-danger fw-bold">PoliNews</span>. All rights reserved.
            </div>

            <!-- Footer Bottom -->

        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>