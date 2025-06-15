<?php
session_start();

$hata = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Veritabanı bağlantısı
    require_once 'config.php';

    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $sifre = $_POST['sifre'] ?? '';

    if (empty($kullanici_adi) || empty($sifre))
        $hata = "Lütfen kullanıcı adı ve şifrenizi girin.";

    else {
        //Kullanıcı bilgisi çek
        $sorgu = $baglanti->prepare("SELECT id, sifre FROM kullanicilar WHERE kullanici_adi = ?");
        $sorgu->bind_param("s", $kullanici_adi);
        $sorgu->execute();
        $sonuc = $sorgu->get_result();

        if ($sonuc->num_rows == 1) {

            $kullanici = $sonuc->fetch_assoc();

            //Şifre doğrula
            if (password_verify($sifre, $kullanici['sifre'])) {
                //Giriş başarılı, session başlat
                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['kullanici_adi'] = $kullanici_adi;

                //Ana sayfaya yönlendir
                header("Location: index.php");
                exit;
            } 
            else
                $hata = "Şifre yanlış.";
        } 
        else
            $hata = "Böyle bir kullanıcı bulunamadı.";

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
    <title>Kampüs Forum - Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?> 

    <div class="container mt-5" style="max-width: 400px;">

        <h2 class="mb-4 text-center">Kampüs Forum - Giriş Yap</h2>

        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" novalidate>

            <div class="mb-3">
                <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required
                    value="<?= htmlspecialchars($_POST['kullanici_adi'] ?? '') ?>" />
            </div>

            <div class="mb-3">
                <label for="sifre" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="sifre" name="sifre" required />
            </div>

            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>

        </form>

        <div class="mt-3 text-center">
            Üye değil misiniz? <a href="register.php">Kayıt olun</a>
        </div>

    </div>
</body>

</html>