<!-- Kullanıcı girişi yoksa -->
<?php if (!isset($_SESSION['kullanici_id'])): ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html"><strong>Kampüs Forum</strong></a>

            <button class="navbar-toggler" type="button"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-outline-light me-2">Giriş Yap</a>
                    <a href="register.php" class="btn btn-primary">Kayıt Ol</a>
                </div>
            </div>

        </div>
    </nav>

<!-- Kullanıcı girişi varsa -->
<?php else: ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">

            <a class="navbar-brand fw-bold" href="index.php">Kampüs Forum</a>

            <div class="d-flex align-items-center">

                <!-- Kullanıcı bilgilerine gidiş butonu -->
                <a href="user.php" class="text-white text-decoration-none me-4 fw-semibold">
                    <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>
                </a>

                <!-- "Çıkış Yap" butonu -->
                <form method="post" action="logout.php">
                    <button type="submit" class="btn btn-outline-light btn-sm px-3 py-1 rounded">
                        Çıkış Yap
                    </button>
                </form>

            </div>
        </div>
    </nav>

<?php endif; ?>