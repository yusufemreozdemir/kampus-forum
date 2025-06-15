<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}

//Veritabanı bağlantısı
require_once 'config.php';

$konu_id = intval($_GET['id'] ?? 0);

//Yanıt ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['yanit_icerik'])) {

    $yanit_icerik = trim($_POST['yanit_icerik']);
    $kullanici_id = $_SESSION['kullanici_id'];

    $stmt = $baglanti->prepare("INSERT INTO yanitlar (konu_id, kullanici_id, icerik, tarih) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $konu_id, $kullanici_id, $yanit_icerik);
    $stmt->execute();
    $stmt->close();

    //Sayfayı yenile ki POST tekrar gönderilmesin
    header("Location: konu.php?id=$konu_id");
    exit;
}

//Konu bilgilerini çek
$stmt = $baglanti->prepare("
    SELECT k.icerik, k.baslik, k.tarih, u.kullanici_adi
    FROM konular k
    JOIN kullanicilar u ON k.kullanici_id = u.id
    WHERE k.id = ?"
);

$stmt->bind_param("i", $konu_id);
$stmt->execute();
$sonuc = $stmt->get_result();

if ($sonuc->num_rows !== 1)
    die("Konu bulunamadı.");

$konu = $sonuc->fetch_assoc();
$stmt->close();

//Yanıtları ve beğeni durumlarını çek
$stmt = $baglanti->prepare("
    SELECT y.id, y.icerik, y.tarih, u.kullanici_adi,
           (SELECT COUNT(*) FROM begeniler b WHERE b.yanit_id = y.id AND b.kullanici_id = ?) AS begeni_durumu
    FROM yanitlar y
    JOIN kullanicilar u ON y.kullanici_id = u.id
    WHERE y.konu_id = ?
    ORDER BY y.tarih ASC
");

$stmt->bind_param("ii", $_SESSION['kullanici_id'], $konu_id);
$stmt->execute();
$yanitlar = $stmt->get_result();
$stmt->close();

$baglanti->close();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Konu: <?= htmlspecialchars($konu['baslik']) ?> - Kampüs Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        .btn-mini {
            padding: 2px 5px;
            font-size: 0.75rem;
            line-height: 1.2;
            margin: 6.5px;
            margin-left: 25px;
        }
    </style>
</head>

<body>
    
    <!-- Navbar -->
    <?php include 'navbar.php'; ?> 
    
    <br>
    <div class="container mt-4">
        <h3><?= htmlspecialchars($konu['baslik']) ?></h3>
        <textarea name="konu_icerik" class="form-control" rows="4"
            readonly><?= htmlspecialchars($konu['icerik']) ?></textarea>
        <p class="text-muted">Oluşturan: <?= htmlspecialchars($konu['kullanici_adi']) ?> - <?= $konu['tarih'] ?></p>

        <hr />

        <h5>Yanıtlar</h5>

        <?php if ($yanitlar->num_rows === 0): ?>
            <p>Henüz yanıt yok.</p>

        <?php else: ?>

            <ul class="list-group mb-4">

                <?php while ($yanit = $yanitlar->fetch_assoc()): ?>

                    <li class="list-group-item">

                        <p><?= nl2br(htmlspecialchars($yanit['icerik'])) ?></p>
                        <small class="text-muted">Yazan: <?= htmlspecialchars($yanit['kullanici_adi']) ?> -
                            <?= $yanit['tarih'] ?></small>

                        <?php if ($yanit['kullanici_adi'] == $_SESSION['kullanici_adi']): ?>

                             <a href="duzenle_yanit.php?id=<?= $yanit['id'] ?>&konu_id=<?= $konu_id ?>"
                                class="btn btn-mini btn-outline-primary">
                                Düzenle
                            </a>

                            <a href="sil_yanit.php?id=<?= $yanit['id'] ?>&konu_id=<?= $konu_id ?>"
                                class="btn btn-mini btn-outline-danger ms-1"
                                onclick="return confirm('Bu yanıtı silmek istediğinizden emin misiniz?')">
                                Sil
                            </a>

                        <?php else: ?>

                            <form method="post" action="begeni.php" class="d-inline">

                                <input type="hidden" name="yanit_id" value="<?= $yanit['id'] ?>">
                                <input type="hidden" name="konu_id" value="<?= $konu_id ?>">

                                <button type="submit"
                                    class="btn btn-mini <?= $yanit['begeni_durumu'] ? 'btn-success' : 'btn-outline-success' ?>">
                                    <?= $yanit['begeni_durumu'] ? 'Beğeniyi Kaldır' : 'Beğen' ?>
                                </button>

                            </form>

                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>

        <?php endif; ?>

        <h5>Yanıt Ekle</h5>

        <form method="post" action="konu.php?id=<?= $konu_id ?>">

            <div class="mb-3">
                <textarea name="yanit_icerik" class="form-control" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Gönder</button>

        </form>
    </div>
</body>

</html>