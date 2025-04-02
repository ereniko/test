<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kurye.php");
    exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM kuryeler WHERE id = ?");
    $stmt->execute([$id]);
    $kurye = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kurye) {
        header("Location: kurye.php");
        exit;
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kurye['ad_soyad']) ?> - Kurye Detayları</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #31a097;
            --secondary-color: #26867e;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding-top: 80px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.6s ease-out;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 40px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .info-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .info-item {
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #f1f3f5;
            transform: translateX(10px);
        }

        .info-item strong {
            color: var(--secondary-color);
            min-width: 150px;
        }

        .photo-gallery {
            display: grid;
            gap: 20px;
            margin-top: 30px;
        }

        .photo-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .photo-card:hover {
            transform: scale(1.03);
        }

        .photo-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .btn-mesaj {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 30px;
            transition: all 0.3s ease;
        }

        .btn-mesaj:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(49, 160, 151, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($kurye['ad_soyad']) ?> - Kurye Detayları</h1>
        
        <div class="detail-grid">
            <div class="info-card">
                <h3><i class="fas fa-user-tie"></i> Temel Bilgiler</h3>
                <div class="info-item">
                    <strong>Ad Soyad:</strong>
                    <span><?= htmlspecialchars($kurye['ad_soyad']) ?></span>
                </div>
                <div class="info-item">
                    <strong>TC Kimlik No:</strong>
                    <span><?= htmlspecialchars($kurye['tckimlik']) ?></span>
                </div>
                <div class="info-item">
                    <strong>Telefon:</strong>
                    <span><?= htmlspecialchars($kurye['telefon']) ?></span>
                </div>
            </div>

            <div class="info-card">
                <h3><i class="fas fa-map-marked-alt"></i> Güzergah Bilgileri</h3>
                <div class="info-item">
                    <strong>Alınış Şehir:</strong>
                    <span><?= htmlspecialchars($kurye['teslim_alinan_sehir']) ?></span>
                </div>
                <div class="info-item">
                    <strong>Teslim Şehir:</strong>
                    <span><?= htmlspecialchars($kurye['teslim_edilecek_sehir']) ?></span>
                </div>
                <div class="info-item">
                    <strong>Teslimat Adresi:</strong>
                    <span><?= htmlspecialchars($kurye['teslim_edilecek_adres']) ?></span>
                </div>
            </div>

            <div class="info-card">
                <h3><i class="fas fa-file-invoice-dollar"></i> Fiyatlandırma</h3>
                <div class="info-item">
                    <strong>Toplam Ücret:</strong>
                    <span class="text-success fw-bold"><?= number_format($kurye['Fiyat'], 2) ?> TL</span>
                </div>
            </div>

            <div class="info-card">
                <h3><i class="fas fa-box-open"></i> Sipariş Detayları</h3>
                <div class="info-item">
                    <?= nl2br(htmlspecialchars($kurye['siparis_detay'])) ?>
                </div>
            </div>
        </div>

        <div class="photo-gallery">
            <?php foreach (['foto1', 'foto2'] as $foto): ?>
                <?php if (!empty($kurye[$foto])): ?>
                <div class="photo-card">
                    <img src="<?= htmlspecialchars($kurye[$foto]) ?>" alt="Ürün fotoğrafı">
                </div>
                <?php else: ?>
                <div class="info-card text-center py-4">
                    <i class="fas fa-image fa-2x text-muted"></i>
                    <p class="text-muted mt-2 mb-0">Fotoğraf bulunamadı</p>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="text-center">
            <a href="conversation.php?kurye_id=<?= $kurye['id'] ?>" class="btn-mesaj">
                <i class="fas fa-comment-dots"></i> Mesaj Gönder
            </a>
        </div>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>