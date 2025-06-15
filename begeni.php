<?php
session_start();

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}

//Veritabanı bağlantısı
require_once 'config.php';

$yanit_id = intval($_POST['yanit_id'] ?? 0);
$konu_id = intval($_POST['konu_id'] ?? 0);

$kullanici_id = $_SESSION['kullanici_id'];

//Beğeni var mı kontrol et
$kontrol = $baglanti->prepare("SELECT id FROM begeniler WHERE yanit_id = ? AND kullanici_id = ?");
$kontrol->bind_param("ii", $yanit_id, $kullanici_id);
$kontrol->execute();
$sonuc = $kontrol->get_result();

//Beğeni varsa kaldır
if ($sonuc->num_rows > 0) {
    $stmt = $baglanti->prepare("DELETE FROM begeniler WHERE yanit_id = ? AND kullanici_id = ?");
    $stmt->bind_param("ii", $yanit_id, $kullanici_id);
} 

//Beğeni yoksa ekle
else {
    $stmt = $baglanti->prepare("INSERT INTO begeniler (kullanici_id, yanit_id, tarih) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $kullanici_id, $yanit_id);
}

$stmt->execute();
$stmt->close();
$kontrol->close();
$baglanti->close();

header("Location: konu.php?id=$konu_id");
exit;
?>