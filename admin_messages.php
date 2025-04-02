<?php
include 'admin_header.php';

// Tüm mesajlar
$stmt = $pdo->query("
  SELECT m.*, 
         uf.name as from_name, uf.surname as from_surname,
         ut.name as to_name,   ut.surname as to_surname,
         t.from_city, t.to_city
  FROM messages m
  JOIN users uf ON m.from_user_id = uf.id
  JOIN users ut ON m.to_user_id   = ut.id
  JOIN trips t  ON m.trip_id      = t.id
  ORDER BY m.id DESC
");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Mesajlar / Başvurular</h2>
<table>
  <tr>
    <th>ID</th>
    <th>İlan</th>
    <th>Gönderen</th>
    <th>Alıcı</th>
    <th>Mesaj</th>
    <th>Tarih</th>
  </tr>
  <?php foreach($messages as $msg): ?>
  <tr>
    <td><?php echo $msg['id']; ?></td>
    <td><?php echo $msg['from_city'].' → '.$msg['to_city']; ?></td>
    <td><?php echo $msg['from_name'].' '.$msg['from_surname']; ?></td>
    <td><?php echo $msg['to_name'].' '.$msg['to_surname']; ?></td>
    <td><?php echo nl2br($msg['message']); ?></td>
    <td><?php echo $msg['created_at']; ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php
include 'admin_footer.php';
?>
