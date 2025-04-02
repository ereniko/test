<?php
include 'admin_header.php';

// ban_user.php?id=XX&action=ban  veya  ban_user.php?id=XX&action=unban
$userId = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if($userId && $action){
  if($action === 'ban'){
    $stmt = $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = :id");
    $stmt->execute(['id'=>$userId]);
  } elseif($action === 'unban'){
    $stmt = $pdo->prepare("UPDATE users SET is_banned = 0 WHERE id = :id");
    $stmt->execute(['id'=>$userId]);
  }
}
header('Location: users.php');
exit;
?>
