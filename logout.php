<?php
session_start();
session_unset();     //Tüm oturum değişkenlerini siler
session_destroy();   //Oturumu sonlandırır

//Ana sayfaya (giriş yapılmadan önceki sayfa) yönlendir
header("Location: index.html");
exit;
?>