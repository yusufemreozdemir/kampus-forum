<?php
session_start();

//Giriş yapılmamışsa login sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['kullanici_adi'];

//Veritabanı bağlantısı
require_once 'config.php';

$sql = "SELECT id, baslik FROM konular ORDER BY tarih DESC";
$sonuc = $baglanti->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kampüs Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    <?php include 'navbar.php'; ?> 

    <!-- İçerik -->
    <div class="container mt-4">
        <br>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3>Forum Konuları</h3>
            <a href="yeni_konu.php" class="btn btn-sm btn-primary">Yeni Konu</a>
        </div>

        <hr>

        <div class="list-group">
            
            <?php if ($sonuc && $sonuc->num_rows > 0): ?>

                <?php while ($konu = $sonuc->fetch_assoc()): ?>
                    <a href="konu.php?id=<?= $konu['id'] ?>" class="list-group-item list-group-item-action">
                        <?= htmlspecialchars($konu['baslik']) ?>
                    </a>

                <?php endwhile; ?>

            <?php else: ?>
                <div class="list-group-item">Henüz hiç konu yok.</div>

            <?php endif; ?>
        </div>

    </div>

    <?php $baglanti->close(); ?>

</body>

</html>