<?php
session_start();

// Giriş yapılmamışsa login sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}

$hata = "";
$basari = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $baslik = trim($_POST['baslik'] ?? '');
    $icerik = trim($_POST['icerik'] ?? '');

    if (empty($baslik) || empty($icerik))
        $hata = "Lütfen başlık ve içerik girin.";
    else {
        //Veritabanı bağlantısı
        require_once 'config.php';

        $kullanici_id = $_SESSION['kullanici_id'];

        $sorgu = $baglanti->prepare("INSERT INTO konular (kullanici_id, baslik, icerik) VALUES (?, ?, ?)");
        $sorgu->bind_param("iss", $kullanici_id, $baslik, $icerik);

        if ($sorgu->execute()) {
            $sorgu->close();
            $baglanti->close();
            header("Location: index.php");
            exit;
        } else
            $hata = "Konu eklenirken hata oluştu.";

        $sorgu->close();
        $baglanti->close();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Yeni Konu - Kampüs Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mt-5" style="max-width: 600px;">

        <h2>Yeni Konu Aç</h2>
        <hr>

        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>

        <form method="post" action="yeni_konu.php" novalidate>

            <div class="mb-3">
                <label for="baslik" class="form-label">Konu Başlığı</label>
                <input type="text" class="form-control" id="baslik" name="baslik" required
                    value="<?= htmlspecialchars($_POST['baslik'] ?? '') ?>" />
            </div>

            <div class="mb-3">
                <label for="icerik" class="form-label">İçerik</label>
                <textarea class="form-control" id="icerik" name="icerik" rows="6"
                    required><?= htmlspecialchars($_POST['icerik'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Konu Oluştur</button>
            <a href="index.php" class="btn btn-secondary ms-2">İptal</a>

        </form>
    </div>

</body>

</html>