<?php
include 'admin_header.php';

// Tüm ilanlar
$stmt = $pdo->query("
  SELECT t.*, u.name, u.surname
  FROM trips t
  JOIN users u ON t.user_id = u.id
  ORDER BY t.id DESC
");
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Silme işlemi
if(isset($_GET['delete'])){
  $delId = (int)$_GET['delete'];
  $stmtdel = $pdo->prepare("DELETE FROM trips WHERE id=:id");
  $stmtdel->execute(['id'=>$delId]);
  header('Location: trips.php');
  exit;
}
?>

<h2>Seyahat İlanları</h2>
<table>
  <tr>
    <th>ID</th>
    <th>Paylaşan</th>
    <th>Nereden</th>
    <th>Nereye</th>
    <th>Fiyat</th>
    <th>Tarih</th>
    <th>İşlem</th>
  </tr>
  <?php foreach($trips as $tr): ?>
  <tr>
    <td><?php echo $tr['id']; ?></td>
    <td><?php echo $tr['name'].' '.$tr['surname']; ?></td>
    <td><?php echo $tr['from_city']; ?></td>
    <td><?php echo $tr['to_city']; ?></td>
    <td><?php echo $tr['price']; ?></td>
    <td><?php echo $tr['created_at']; ?></td>
    <td>
      <a href="trips.php?delete=<?php echo $tr['id']; ?>" 
         onclick="return confirm('Bu ilanı silmek istediğinize emin misiniz?')">
        Sil
      </a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php
include 'admin_footer.php';
?>