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

$kullanici_id = $_SESSION['kullanici_id'];

//Yanıtın beğenilerini sil
$stmt = $baglanti->prepare("DELETE FROM begeniler WHERE yanit_id = ?");
$stmt->bind_param("i", $yanit_id);
$stmt->execute();
$stmt->close();

//Yanıtın geçmiş sürümlerini sil
$stmt = $baglanti->prepare("DELETE FROM yanit_gecmisi WHERE yanit_id = ?");
$stmt->bind_param("i", $yanit_id);
$stmt->execute();
$stmt->close();

//Yanıtı sil
$stmt = $baglanti->prepare("DELETE FROM yanitlar WHERE id = ?");
$stmt->bind_param("i", $yanit_id);
$stmt->execute();
$stmt->close();

$baglanti->close();
header("Location: konu.php?id=$konu_id");
exit;
?>