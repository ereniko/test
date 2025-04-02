<?php
session_start();
include 'db.php';

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if($id > 0 && in_array($action, ['ban','unban'])){
  $isBanned = ($action=='ban') ? 1 : 0;
  $stmt = $pdo->prepare("UPDATE users SET is_banned=? WHERE id=?");
  $stmt->execute([$isBanned, $id]);
}
header('Location: admin_users.php');
exit;
