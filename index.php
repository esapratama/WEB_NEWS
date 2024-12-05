<?php
require 'config/db.php';

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : "";
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : "";
$collection = $db->news;

// Ambil kategori unik dari database
$categories = $collection->distinct('category');

// Query berita
if ($categoryFilter) {
    $cursor = $collection->find(
        ['category' => $categoryFilter],
        ['sort' => ['created_at' => -1]]
    );
} elseif ($searchQuery) {
    $cursor = $collection->find(
        [
            '$or' => [
                ['title' => new MongoDB\BSON\Regex($searchQuery, 'i')],
                ['content' => new MongoDB\BSON\Regex($searchQuery, 'i')]
            ]
        ],
        ['sort' => ['created_at' => -1]]
    );
} else {
    $cursor = $collection->find([], ['sort' => ['created_at' => -1]]);
}

$newsList = iterator_to_array($cursor);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PoliNews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
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

<body>
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

    <div class="container custom-container mt-3">
        <div class="jumbotron jumbotron-fluid text-center py-4"
            style="background: rgba(234, 234, 234, 0.5); border-radius: 10px;">
            <div class="judul fw-bold" style="font-size: 20px;">Selamat Datang, di <span class="text-danger">PoliNews
            </div>
            <div class="judul fw-bold" style="font-size: 32px;">Sumber Berita Terpercaya, Aktual, dan Berimbang</div>
            <div class="judul text-danger fw-semibold text-danger">Tetap terhubung dengan informasi terkini, inspirasi,
                dan analisis mendalam di POLINEMA</div>
        </div>

        <div class="row mt-4">

            <?php if (count($newsList) > 0): ?>
            <!-- Kiri: Berita Utama -->
            <div class="col-md-6">
                <div class=" text-white">
                    <img src="<?= isset($newsList[0]['image']) ? 'images/' . $newsList[0]['image'] : '' ?>"
                        class="card-img" alt="Featured News Image" style="border-radius: 8px;">
                </div>
            </div>

            <!-- Kanan: Daftar Berita Lainnya -->
            <div class="col-md-6 pt-2">
                <img src="https://placehold.co/40x40" alt="Author's profile picture" class="rounded-circle me-2">
                <span><?= $newsList[0]['author'] ?? '' ?></span>
                <span class="mx-3">·</span>
                <span class="mx-1"><?= $newsList[0]['created_at']->toDateTime()->format('Y-m-d H:i:s') ?></span>
                <h2 class="card-title fw-bold my-3"><?= $newsList[0]['title'] ?? '' ?> </h2>
                <p class="card-text">
                    <?= $newsList[0]['summary'] ?? '' ?>
                </p>
                <a href="detail.php?id=<?= $newsList[0]['_id'] ?>" class="btn btn-danger">Selengkapnya</a>
                <?php endif; ?>
            </div>


            <div class=" mt-4">
                <?php if ($searchQuery): ?>
                <h5>Hasil pencarian untuk: <strong><?= htmlspecialchars($searchQuery) ?></strong></h5>
                <?php if (empty($newsList)): ?>
                <p class="text-muted">Tidak ada hasil yang ditemukan untuk pencarian Anda.</p>
                <?php else: ?>
                <div class="row mt-4">
                    <?php foreach ($newsList as $news): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="<?= isset($news['image']) ? 'images/' . $news['image'] : 'https://placehold.co/300x200' ?>"
                                class="card-img-top" alt="News Image">
                            <div class="card-body">
                                <h5 class="card-title card-text-custom fw-semibold"><?= $news['title'] ?></h5>
                                <p class="card-text card-text-custom"><?= $news['summary'] ?></p>
                                <a href="detail.php?id=<?= $news['_id'] ?>" class="btn btn-danger">Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>

            <?php endif; ?>


        </div>


        <div class="row">
            <?php if (!$searchQuery): ?>
            <!-- Cek apakah tidak ada query pencarian -->
            <h5>Berita Lainnya</h5>
            <?php foreach (array_slice($newsList, 1) as $news): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img src="<?= isset($news['image']) ? 'images/' . $news['image'] : 'https://placehold.co/300x200' ?>"
                        class="card-img-top" alt="News Image">
                    <div class="card-body">
                        <h5 class="card-title card-text-custom fw-semibold"><?= $news['title'] ?></h5>
                        <p class="card-text card-text-custom"><?= $news['summary'] ?></p>
                        <a href="detail.php?id=<?= $news['_id'] ?>" class="btn btn-danger">Selengkapnya</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
    </div>


    <footer class="bg-light text-center text-lg-start mt-4">
        <!-- Section: Contact -->
        <div class="text-center p-3">
            © 2024 <span class="text-danger fw-bold">PoliNews</span>. All rights reserved.
        </div>
        </div>
        </div>

        <!-- Footer Bottom -->

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>