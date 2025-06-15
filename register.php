<?php
//Mesajları tutacak değişkenler
$hata = "";
$basarili = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Veritabanı bağlantısı
    require_once 'config.php';
    
    //Formdan gelen veriler
    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';

    //Alan kontrolü
    if (empty($kullanici_adi) || empty($email) || empty($sifre)) 
        $hata = "Lütfen tüm alanları doldurun.";

    else {
        //Kullanıcı adı ve e-posta kontrolü
        $kontrol = $baglanti -> prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ? OR email = ?");
        $kontrol -> bind_param("ss", $kullanici_adi, $email);
        $kontrol -> execute();
        $sonuc = $kontrol -> get_result();

        if ($sonuc->num_rows > 0) 
            $hata = "Bu kullanıcı adı veya e-posta zaten kayıtlı.";

        else {
            $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT); //Şifre hash

            //Kayıt ekleme
            $ekle = $baglanti->prepare("INSERT INTO kullanicilar (kullanici_adi, email, sifre) VALUES (?, ?, ?)");
            $ekle->bind_param("sss", $kullanici_adi, $email, $hashli_sifre);

            if ($ekle->execute()) 
                $basarili = "Kayıt başarılı. <a href='login.php'>Giriş yapabilirsiniz.</a>";

            else 
                $hata = "Kayıt sırasında hata oluştu: " . $ekle->error;
        }

        $kontrol -> close();
        $baglanti -> close();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kampüs Forum - Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?> 

    <div class="container mt-5" style="max-width: 450px;">

        <h2 class="mb-4 text-center">Kampüs Forum - Kayıt Ol</h2>
        <br>

        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>

        <?php if ($basarili): ?>
            <div class="alert alert-success"><?= $basarili ?></div>
        <?php else: ?>

            <form method="post" action="register.php" novalidate>

                <div class="mb-3">
                    <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                    <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required
                        value="<?= htmlspecialchars($_POST['kullanici_adi'] ?? '') ?>" />
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-posta</label>
                    <input type="email" class="form-control" id="email" name="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                </div>

                <div class="mb-3">
                    <label for="sifre" class="form-label">Şifre</label>
                    <input type="password" class="form-control" id="sifre" name="sifre" required />
                </div>

                <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>

            </form>

            <div class="mt-3 text-center">
                Zaten üyeyseniz <a href="login.php">giriş yapın</a>.
            </div>

        <?php endif; ?>

    </div>
</body>

</html>