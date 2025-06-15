<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}

//Veritabanı bağlantısı
require_once 'config.php';

$yanit_id = intval($_GET['id'] ?? 0);
$konu_id = intval($_GET['konu_id'] ?? 0);

if ($yanit_id <= 0 || $konu_id <= 0) 
    die("Geçersiz ID.");

$kullanici_id = $_SESSION['kullanici_id'];

//Yanıtın sahibini ve içeriğini çek
$stmt = $baglanti->prepare("
    SELECT y.icerik, y.kullanici_id
    FROM yanitlar y
    WHERE y.id = ?
");

$stmt->bind_param("i", $yanit_id);
$stmt->execute();
$sonuc = $stmt->get_result();

$yanit = $sonuc->fetch_assoc();
$stmt->close();

//Yanıt geçmişini çek
$stmt = $baglanti->prepare("SELECT eski_icerik, duzenleme_tarihi FROM yanit_gecmisi WHERE yanit_id = ? ORDER BY duzenleme_tarihi DESC");
$stmt->bind_param("i", $yanit_id);
$stmt->execute();
$gecmis = $stmt->get_result();
$stmt->close();

$hata = "";
$basari = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $yeni_icerik = trim($_POST['icerik'] ?? '');

    if (empty($yeni_icerik)) 
        $hata = "Lütfen içeriği doldurun.";

    else {
        //Eski içeriği yanit_gecmisi tablosuna kaydet
        $stmt = $baglanti->prepare("INSERT INTO yanit_gecmisi (yanit_id, eski_icerik, duzenleme_tarihi) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $yanit_id, $yanit['icerik']);
        $stmt->execute();
        $stmt->close();

        //Yeni içeriği güncelle
        $stmt = $baglanti->prepare("UPDATE yanitlar SET icerik = ?, tarih = NOW() WHERE id = ?");
        $stmt->bind_param("si", $yeni_icerik, $yanit_id);

        if ($stmt->execute()) {
            $stmt->close();
            $baglanti->close();
            header("Location: konu.php?id=$konu_id");
            exit;
        } 
        else 
            $hata = "Düzenleme sırasında hata oluştu.";

        $stmt->close();
    }
}

$baglanti->close();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yanıt Düzenle - Kampüs Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container mt-5" style="max-width: 600px;">

        <h2>Yanıt Düzenle</h2>

        <hr>
        
        <?php if ($gecmis->num_rows > 0): ?>
            <h5>Yanıt Geçmişi</h5>
            <ul class="list-group mb-3">
                <?php while ($eski = $gecmis->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <p><?= nl2br(htmlspecialchars($eski['eski_icerik'])) ?></p>
                        <small class="text-muted">Düzenleme Tarihi: <?= $eski['duzenleme_tarihi'] ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>

        <?php endif; ?>

        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>

        <?php endif; ?>
        <br>
        <form method="post" action="duzenle_yanit.php?id=<?= $yanit_id ?>&konu_id=<?= $konu_id ?>" novalidate>

            <div class="mb-3">
                <h5>Yeni Yanıt</h5>
                <textarea class="form-control" id="icerik" name="icerik" rows="6" required><?= htmlspecialchars($yanit['icerik']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="konu.php?id=<?= $konu_id ?>" class="btn btn-secondary ms-2">İptal</a>
        </form>
    </div>
</body>
</html>