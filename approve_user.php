<?php
session_start();
include 'db.php'; // PDO bağlantınız

if(!isset($_SESSION['admin_id'])){
  // Admin girişi kontrol, vs.
  header('Location: admin_login.php');
  exit;
}

// GET param
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id > 0){
  // is_approved=1 yap
  $stmt = $pdo->prepare("UPDATE users SET is_approved=1 WHERE id=?");
  $stmt->execute([$id]);
}

// Yönlendir
header('Location: admin_users.php');
exit;
