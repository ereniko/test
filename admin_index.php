<?php
include 'admin_header.php';

// Kullanıcı sayısı
$stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmtUsers->fetchColumn();

// İlan sayısı
$stmtTrips = $pdo->query("SELECT COUNT(*) FROM trips");
$totalTrips = $stmtTrips->fetchColumn();

// Mesaj sayısı
$stmtMsg = $pdo->query("SELECT COUNT(*) FROM messages");
$totalMsgs = $stmtMsg->fetchColumn();
?>

<h2>Yönetim Paneli</h2>
<div class="admin-cards">
  <div class="admin-card">
    <h3>Kullanıcılar</h3>
    <p>Toplam: <?php echo $totalUsers; ?></p>
    <a href="users.php" class="btn" style="text-decoration:none;">Detay</a>
  </div>
  <div class="admin-card">
    <h3>Seyahat İlanları</h3>
    <p>Toplam: <?php echo $totalTrips; ?></p>
    <a href="trips.php" class="btn" style="text-decoration:none;">Detay</a>
  </div>
  <div class="admin-card">
    <h3>Mesajlar</h3>
    <p>Toplam: <?php echo $totalMsgs; ?></p>
    <a href="messages.php" class="btn" style="text-decoration:none;">Detay</a>
  </div>
</div>

<?php
include 'admin_footer.php';
?>
Kutucuklarla kısa özetleri gösterdik. CSS animasyonları “fadeInUp” devreye girecek.

