<?php
include 'admin_header.php';
include 'db.php'; // PDO bağlantınız

// Liste
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Kullanıcılar</h2>
<table border="1" cellpadding="5" cellspacing="0">
  <tr>
    <th>ID</th>
    <th>T.C.</th>
    <th>Ad Soyad</th>
    <th>Email</th>
    <th>Şehir</th>
    <th>Onay Durumu</th>
    <th>Ban Durumu</th>
    <th>İşlem</th>
  </tr>
  <?php foreach($users as $u): ?>
  <tr>
    <td><?php echo $u['id']; ?></td>
    <td><?php echo htmlspecialchars($u['tckimlik']); ?></td>
    <td><?php echo htmlspecialchars($u['name'].' '.$u['surname']); ?></td>
    <td><?php echo htmlspecialchars($u['email']); ?></td>
    <td><?php echo htmlspecialchars($u['city'].' / '.$u['district']); ?></td>

    <!-- Onay Durumu (is_approved) -->
    <td>
      <?php if(!$u['is_approved']): ?>
        <span style="color:orange;">Bekliyor</span> |
        <a href="approve_user.php?id=<?php echo $u['id']; ?>">Onayla</a>
      <?php else: ?>
        <span style="color:green;">Onaylı</span>
      <?php endif; ?>
    </td>

    <!-- Ban Durumu (is_banned) -->
    <td>
      <?php if($u['is_banned']): ?>
        <span style="color:red;">Yasaklı</span>
      <?php else: ?>
        <span style="color:green;">Aktif</span>
      <?php endif; ?>
    </td>

    <td>
      <!-- Yasak/Unban Link -->
      <?php if(!$u['is_banned']): ?>
        <a href="ban_user.php?id=<?php echo $u['id']; ?>&action=ban" style="color:red;">Yasakla</a>
      <?php else: ?>
        <a href="ban_user.php?id=<?php echo $u['id']; ?>&action=unban" style="color:green;">Yasağı Kaldır</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php
include 'admin_footer.php';
?>
