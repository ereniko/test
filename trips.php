<?php
// trips.php
include 'db.php';
include 'header.php';

// Tahmini süre hesaplama fonksiyonu
function getTravelTime($from, $to) {
    $dummyTimes = [
        'diyarbakır-istanbul' => '16 saat',
        'istanbul-ankara'     => '5 saat',
        'izmir-ankara'        => '6.5 saat',
        'antalya-izmir'       => '6.2 saat'
    ];
    $key = strtolower($from . '-' . $to);
    return isset($dummyTimes[$key]) ? $dummyTimes[$key] : 'Bilinmiyor';
}

// İlanları çek (JOIN ile kullanıcı bilgileri dahil: name, surname, photo_profile, trip_date, duration, created_at)
$stmt = $pdo->query("
    SELECT t.*, u.name, u.surname, u.photo_profile, t.trip_date, t.duration, t.created_at
    FROM trips t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC
");
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
  <h2>Seyahat İlanları</h2>
  <div class="cards-container">
    <?php foreach ($trips as $tr): 
      $displayDuration = !empty($tr['duration']) ? htmlspecialchars($tr['duration']) : getTravelTime($tr['from_city'], $tr['to_city']);
      $tripDate = !empty($tr['trip_date'])
                  ? date('d.m.Y H:i', strtotime($tr['trip_date']))
                  : date('d.m.Y H:i', strtotime($tr['created_at']));
    ?>
      <div class="ilan-card">
        <div class="profil-alani">
          <?php if (!empty($tr['photo_profile']) && file_exists($tr['photo_profile'])): ?>
            <img src="<?php echo htmlspecialchars($tr['photo_profile']); ?>" alt="Profil" class="profil-resim">
          <?php else: ?>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Varsayılan Profil" class="profil-resim">
          <?php endif; ?>
          <div class="kullanici-bilgi">
            <?php echo htmlspecialchars($tr['name'] . ' ' . $tr['surname']); ?><br>
            <span style="color: #666; font-size: 0.9em;">Kullanıcı</span>
          </div>
        </div>
        <div class="sehirler">
          <div class="sehir">
            <span><?php echo htmlspecialchars($tr['from_city']); ?></span>
          </div>
          <div class="sehir">
            <span><?php echo htmlspecialchars($tr['to_city']); ?></span>
          </div>
        </div>
        <div class="detaylar" style="flex-wrap: wrap;">
          <div class="detay-kutu" style="margin-bottom: 8px;">
            <span>Süre</span>
            <div class="deger"><?php echo $displayDuration; ?></div>
          </div>
          <div class="detay-kutu" style="margin-bottom: 8px;">
            <span>Ücret</span>
            <div class="deger"><?php echo htmlspecialchars($tr['price']); ?> TL</div>
          </div>
          <div class="detay-kutu" style="margin-bottom: 8px;">
            <span>Ne Zaman</span>
            <div class="deger"><?php echo $tripDate; ?></div>
          </div>
        </div>
        <a href="trip_detail.php?id=<?php echo $tr['id']; ?>" style="text-decoration:none;">
          <button class="incele-btn">İlanı İncele</button>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
include 'footer.php';
?>
