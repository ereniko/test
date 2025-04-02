<?php
// admin_header.php
session_start();
include 'db.php';

// Eğer admin değilse, yönlendir
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
  header('Location:index.php');
  exit;
}

// Çevrimiçi kullanıcı sayısını hesapla (son 5 dk)
$stmtOnline = $pdo->query("
  SELECT COUNT(*) as count 
  FROM users 
  WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
");
$onlineCount = $stmtOnline->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>
<div class="admin-wrapper">
  <div class="side-nav">
    <h3 style="color:#fff;">Admin Panel</h3>
    <p style="color:#ccc;">Çevrimiçi: <?php echo $onlineCount; ?></p>
    <a href="index.php">Anasayfa</a>
    <a href="users.php">Kullanıcılar</a>
    <a href="trips.php">Seyahat İlanları</a>
    <a href="messages.php">Mesajlar</a>
    <a href="index.php" style="color:yellow;">Siteye Dön</a>
  </div>
  <div class="main-content">
Dikkat: “son 5 dakika içinde aktif olan” kullanıcı sayısını “Çevrimiçi” olarak gösteriyoruz.

