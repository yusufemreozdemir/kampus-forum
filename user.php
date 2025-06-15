<?php
session_start();

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}

//Veritabanı bağlantısı
require_once 'config.php';

$kullanici_id = $_SESSION['kullanici_id'];
$kullanici_adi = $_SESSION['kullanici_adi'];

//Kullanıcı bilgisi
$bilgi_sorgu = $baglanti->prepare("SELECT kullanici_adi, email FROM kullanicilar WHERE id = ?");
$bilgi_sorgu->bind_param("i", $kullanici_id);
$bilgi_sorgu->execute();
$kullanici = $bilgi_sorgu->get_result()->fetch_assoc();

//Açtığı konular
$konular_sorgu = $baglanti->prepare("SELECT id, baslik, tarih FROM konular WHERE kullanici_id = ? ORDER BY tarih DESC");
$konular_sorgu->bind_param("i", $kullanici_id);
$konular_sorgu->execute();
$konular = $konular_sorgu->get_result();

//Yanıtları
$yanit_sorgu = $baglanti->prepare("
    SELECT y.icerik, y.tarih, k.baslik AS konu_basligi, k.id AS konu_id
    FROM yanitlar y
    JOIN konular k ON y.konu_id = k.id
    WHERE y.kullanici_id = ?
    ORDER BY y.tarih DESC
");

$yanit_sorgu->bind_param("i", $kullanici_id);
$yanit_sorgu->execute();
$yanitlar = $yanit_sorgu->get_result();

//Beğendiği yanıtlar
$begeni_sorgu = $baglanti->prepare("
    SELECT y.icerik AS yanit_icerik, y.tarih AS yanit_tarih, k.baslik AS konu_basligi, k.id AS konu_id
    FROM begeniler b
    JOIN yanitlar y ON b.yanit_id = y.id
    JOIN konular k ON y.konu_id = k.id
    WHERE b.kullanici_id = ?
    ORDER BY b.tarih DESC
");

$begeni_sorgu->bind_param("i", $kullanici_id);
$begeni_sorgu->execute();
$begeni_yanitlar = $begeni_sorgu->get_result();

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Profili</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- İçerik -->
    <div class="container mt-5">

        <h3 class="mb-4">Kullanıcı Bilgileri</h3>
        <hr>

        <p><strong>Kullanıcı Adı:</strong> <?= htmlspecialchars($kullanici['kullanici_adi']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($kullanici['email']) ?></p>

        <hr>

        <h4>Açtığınız Konular</h4>

        <?php if ($konular->num_rows > 0): ?>

            <ul class="list-group mb-4">
                <?php while ($konu = $konular->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <a href="konu.php?id=<?= $konu['id'] ?>" class="fw-bold"><?= htmlspecialchars($konu['baslik']) ?></a>
                        <br><small class="text-muted"><?= $konu['tarih'] ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>

        <?php else: ?>
            <p>Henüz konu açmamışsınız.</p>

        <?php endif; ?>

        <hr>

        <h4>Yanıtlarınız</h4>

        <?php if ($yanitlar->num_rows > 0): ?>
            <ul class="list-group mb-4">
                <?php while ($y = $yanitlar->fetch_assoc()): ?>

                    <li class="list-group-item">
                        <a href="konu.php?id=<?= $y['konu_id'] ?>"
                            class="fw-bold"><?= htmlspecialchars($y['konu_basligi']) ?></a>
                        <br><?= htmlspecialchars($y['icerik']) ?>
                        <br><small class="text-muted"><?= $y['tarih'] ?></small>
                    </li>

                <?php endwhile; ?>
            </ul>

        <?php else: ?>
            <p>Henüz yorum yapmamışsınız.</p>

        <?php endif; ?>

        <hr>

        <h4>Beğendiğiniz Yanıtlar</h4>

        <?php if ($begeni_yanitlar->num_rows > 0): ?>
            <ul class="list-group mb-4">
                <?php while ($b = $begeni_yanitlar->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <a href="konu.php?id=<?= $b['konu_id'] ?>"
                            class="fw-bold"><?= htmlspecialchars($b['konu_basligi']) ?></a>
                        <br><?= htmlspecialchars($b['yanit_icerik']) ?>
                        <br><small class="text-muted">Yanıt tarihi: <?= $b['yanit_tarih'] ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>

        <?php else: ?>
            <p>Henüz hiçbir yanıtı beğenmemişsiniz.</p>

        <?php endif; ?>
    </div>

</body>

</html>