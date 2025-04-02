<?php
session_start();
require 'db.php';
include 'header.php';

// GET üzerinden gelen değerler
$from_city = isset($_GET['from_city']) ? trim($_GET['from_city']) : '';
$to_city   = isset($_GET['to_city'])   ? trim($_GET['to_city'])   : '';
$kuryeler  = [];

// Eğer en az bir arama alanı doldurulmuşsa sorguyu çalıştırıyoruz
if (!empty($from_city) || !empty($to_city)) {
    if (!empty($from_city) && !empty($to_city)) {
        // Her iki alan da girilmişse: "Gönderim Şehri" ve "Teslimat Şehri" ayrı ayrı kontrol edilir.
        $sql = "SELECT 
                    k.id,
                    k.ad_soyad,
                    k.teslim_alinan_sehir,
                    k.teslim_edilecek_sehir,
                    k.teslim_edilecek_adres,
                    k.Fiyat,
                    k.eklenme_tarihi,
                    COUNT(m.id) AS mesaj_sayisi
                FROM kuryeler k
                LEFT JOIN messages m ON k.id = m.kurye_id
                WHERE k.teslim_alinan_sehir LIKE ? AND k.teslim_edilecek_sehir LIKE ?
                GROUP BY k.id
                ORDER BY k.eklenme_tarihi DESC";
        $params = ['%' . $from_city . '%', '%' . $to_city . '%'];
    } elseif (!empty($from_city)) {
        // Sadece "Gönderim Şehri" girilmişse: Girilen değer her iki sütunda aranır.
        $sql = "SELECT 
                    k.id,
                    k.ad_soyad,
                    k.teslim_alinan_sehir,
                    k.teslim_edilecek_sehir,
                    k.teslim_edilecek_adres,
                    k.Fiyat,
                    k.eklenme_tarihi,
                    COUNT(m.id) AS mesaj_sayisi
                FROM kuryeler k
                LEFT JOIN messages m ON k.id = m.kurye_id
                WHERE k.teslim_alinan_sehir LIKE ? OR k.teslim_edilecek_sehir LIKE ?
                GROUP BY k.id
                ORDER BY k.eklenme_tarihi DESC";
        $params = ['%' . $from_city . '%', '%' . $from_city . '%'];
    } elseif (!empty($to_city)) {
        // Sadece "Teslimat Şehri" girilmişse: Girilen değer her iki sütunda aranır.
        $sql = "SELECT 
                    k.id,
                    k.ad_soyad,
                    k.teslim_alinan_sehir,
                    k.teslim_edilecek_sehir,
                    k.teslim_edilecek_adres,
                    k.Fiyat,
                    k.eklenme_tarihi,
                    COUNT(m.id) AS mesaj_sayisi
                FROM kuryeler k
                LEFT JOIN messages m ON k.id = m.kurye_id
                WHERE k.teslim_alinan_sehir LIKE ? OR k.teslim_edilecek_sehir LIKE ?
                GROUP BY k.id
                ORDER BY k.eklenme_tarihi DESC";
        $params = ['%' . $to_city . '%', '%' . $to_city . '%'];
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $kuryeler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Veritabanı hatası: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kurye İlanları</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Turkuaz tonları ve modern tasarım için renk değişkenleri */
    :root {
      --primary-color: #1abc9c;    /* Canlı turkuaz */
      --secondary-color: #16a085;  /* Biraz daha koyu turkuaz */
      --background-color: #f8f9fa;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--background-color);
      padding-top: 80px;
    }
    .container-xl {
      max-width: 1320px;
      margin: 0 auto;
      padding: 0 15px;
    }
    /* Arama Formu Stili */
    .search-container {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 20px;
      max-width: 600px;
      margin: 20px auto;
      text-align: center;
    }
    .search-container form {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .search-container input[type="text"] {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
      min-width: 200px;
    }
    .search-container button {
      padding: 10px 20px;
      background: var(--primary-color);
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
      transition: background 0.3s ease;
    }
    .search-container button:hover {
      background: var(--secondary-color);
    }
    /* İlanların listeleneceği grid alanı */
    .kurye-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 1.5rem;
      padding: 2rem 0;
    }
    .kurye-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .kurye-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
    }
    .card-header {
      padding: 1.5rem;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: #fff;
      position: relative;
    }
    .card-header::after {
      content: '';
      position: absolute;
      bottom: -20px;
      right: -20px;
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    .profile-section {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: var(--primary-color);
      font-weight: 600;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .user-info h3 {
      margin: 0;
      font-weight: 600;
      font-size: 1.25rem;
    }
    .location {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
      margin-top: 0.5rem;
      color: rgba(255, 255, 255, 0.9);
    }
    .card-body {
      padding: 1.5rem;
    }
    .delivery-details {
      margin: 1rem 0;
    }
    .detail-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.75rem;
      padding: 0.5rem 0;
      border-bottom: 1px solid #e9ecef;
    }
    .price-tag {
      background: var(--primary-color);
      color: #fff;
      padding: 0.75rem 1.5rem;
      border-radius: 25px;
      font-weight: 600;
      text-align: center;
      margin: 1.5rem 0;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .action-buttons {
      display: grid;
      gap: 0.75rem;
    }
    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.75rem;
      border: none;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
    }
    .btn-primary {
      background: var(--primary-color);
      color: #fff;
    }
    .btn-secondary {
      background: var(--secondary-color);
      color: #fff;
    }
    .btn:hover {
      opacity: 0.9;
      transform: translateY(-2px);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>
  <div class="container-xl">
    <!-- Arama Formu: İki metin kutusu ve arama butonu -->
    <div class="search-container">
      <form method="get" action="kurye.php">
        <input type="text" name="from_city" placeholder="Gönderim Şehri (Nereden)" value="<?= htmlspecialchars($from_city) ?>">
        <input type="text" name="to_city" placeholder="Teslimat Şehri (Nereye)" value="<?= htmlspecialchars($to_city) ?>">
        <button type="submit"><i class="fas fa-search"></i> Ara</button>
      </form>
    </div>
    
    <!-- Arama yapıldıysa ilanlar listelenecek -->
    <?php if (!empty($from_city) || !empty($to_city)): ?>
      <div class="kurye-grid">
        <?php if(count($kuryeler) > 0): ?>
          <?php foreach($kuryeler as $kurye): ?>
            <div class="kurye-card">
              <div class="card-header">
                <div class="profile-section">
                  <div class="avatar">
                    <?= strtoupper(substr($kurye['ad_soyad'], 0, 1)); ?>
                  </div>
                  <div class="user-info">
                    <h3><?= htmlspecialchars($kurye['ad_soyad']); ?></h3>
                    <div class="location">
                      <i class="fas fa-route"></i>
                      <?= htmlspecialchars($kurye['teslim_alinan_sehir']); ?> → <?= htmlspecialchars($kurye['teslim_edilecek_sehir']); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="delivery-details">
                  <div class="detail-item">
                    <span>Teslimat Adresi:</span>
                    <span><?= htmlspecialchars(substr($kurye['teslim_edilecek_adres'], 0, 20)); ?>...</span>
                  </div>
                  <div class="detail-item">
                    <span>Toplam Mesaj:</span>
                    <span><?= $kurye['mesaj_sayisi']; ?></span>
                  </div>
                </div>
                <div class="price-tag">
                  <?= number_format($kurye['Fiyat'], 2); ?> TL
                </div>
                <div class="action-buttons">
                  <a href="kurye_detail.php?id=<?= $kurye['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-info-circle"></i> Detaylar
                  </a>
                  <a href="conversation.php?kurye_id=<?= $kurye['id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-comment-dots"></i> Mesajlaş
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; width:100%; color:#555; padding:20px;">Aradığınız kritere uygun ilan bulunamadı.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php include 'footer.php'; ?>
</body>
</html>
