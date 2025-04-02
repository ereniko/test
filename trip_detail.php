<?php
session_start();
include 'header.php';
include 'db.php';

// URL'den ilan id'sini alıyoruz. URL ?id=... şeklinde olmalıdır.
$trip_id = $_GET['id'] ?? 0;

// İlan (trip) bilgilerini veritabanından çekiyoruz
$stmt = $pdo->prepare("
    SELECT t.*, 
           u.name, 
           u.surname, 
           u.photo_profile, 
           u.id AS owner_id, 
           DATE_FORMAT(t.trip_date, '%d.%m.%Y %H:%i') AS formatted_date,
           COALESCE(t.duration, 'Bilinmiyor') AS duration
    FROM trips t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = :id
");
$stmt->execute(['id' => $trip_id]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trip) {
    echo "<div class='container'><p>İlan bulunamadı!</p></div>";
    include 'footer.php';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message)) {
        $error = 'Mesaj boş olamaz.';
    } else {
        try {
            $pdo->beginTransaction();

            // İlgili ilan için, oturumdaki kullanıcı ile ilan sahibinin (owner) konuşmasını buluyoruz.
            $stmt = $pdo->prepare("
                SELECT * FROM conversations 
                WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
                  AND trip_id = ?
            ");
            $stmt->execute([
                $_SESSION['user']['id'],
                $trip['owner_id'],
                $trip['owner_id'],
                $_SESSION['user']['id'],
                $trip['id']
            ]);
            $conversation = $stmt->fetch();

            if (!$conversation) {
                $stmt = $pdo->prepare("
                    INSERT INTO conversations (user1_id, user2_id, trip_id)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user']['id'],
                    $trip['owner_id'],
                    $trip['id']
                ]);
                $conversation_id = $pdo->lastInsertId();
            } else {
                $conversation_id = $conversation['id'];
            }

            // Mesajı kaydet (messages tablosunda NOT NULL olan trip_id de ekleniyor)
            $stmt = $pdo->prepare("
                INSERT INTO messages (conversation_id, trip_id, from_user_id, to_user_id, message)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $conversation_id,
                $trip['id'],
                $_SESSION['user']['id'],
                $trip['owner_id'],
                $message
            ]);

            $pdo->commit();
            
            header("Location: conversation.php?id=$conversation_id");
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Mesaj gönderilemedi: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($trip['from_city'].' → '.$trip['to_city']) ?> İlan Detayı</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* TRIP DETAIL PAGE - MODERN & ANIMATED VERSION */
        .trip-detail-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 0 20px;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .trip-detail-card {
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(49, 160, 151, 0.15);
            overflow: hidden;
            position: relative;
            margin-bottom: 40px;
            transform: scale(0.98);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .trip-detail-card:hover {
            transform: scale(1);
            box-shadow: 0 20px 50px rgba(49, 160, 151, 0.25);
        }
        /* Profil Header Section */
        .trip-profile-header {
            display: flex;
            align-items: center;
            padding: 40px;
            background: linear-gradient(135deg, #31a097 0%, #26867e 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        .trip-profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 10%, transparent 10.01%);
            background-size: 20px 20px;
            animation: moveBackground 10s linear infinite;
            opacity: 0.3;
        }
        @keyframes moveBackground {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .trip-profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.3);
            margin-right: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        .trip-profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        }
        .trip-owner-info h1 {
            font-size: 2.4rem;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
            animation: slideInLeft 0.8s ease-out;
        }
        .trip-owner-info span {
            font-size: 1.1rem;
            opacity: 0.9;
            animation: slideInLeft 1s ease-out;
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        /* Route Information */
        .trip-route-section {
            padding: 40px;
            border-bottom: 1px solid #e0f2f1;
            background: #f8fafc;
        }
        .trip-route-display {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            animation: slideInUp 0.8s ease-out;
        }
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .route-city {
            flex: 1;
            text-align: center;
            padding: 25px;
            background: white;
            border-radius: 15px;
            margin: 0 20px;
            box-shadow: 0 5px 20px rgba(49, 160, 151, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .route-city:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(49, 160, 151, 0.2);
        }
        .route-city h2 {
            color: #31a097;
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .route-city p {
            color: #64748b;
            font-size: 1rem;
        }
        .route-arrow {
            font-size: 50px;
            color: #31a097;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0.6;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-10px); }
        }
        /* Trip Details Grid */
        .trip-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 40px;
            animation: fadeInUp 1s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 5px 20px rgba(49, 160, 151, 0.1);
        }
        .detail-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(49, 160, 151, 0.2);
        }
        .detail-icon {
            font-size: 2.5rem;
            color: #31a097;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .detail-card:hover .detail-icon {
            transform: scale(1.1);
        }
        .detail-value {
            font-size: 2rem;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .detail-label {
            color: #64748b;
            font-size: 1rem;
        }
        /* Description Section */
        .trip-description-section {
            padding: 40px;
            background: #f8fafc;
            margin: 40px;
            border-radius: 20px;
            animation: fadeIn 1.2s ease-out;
        }
        .trip-description-section h3 {
            color: #31a097;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0f2f1;
        }
        /* Modern Message Form */
        .trip-message-form {
            padding: 40px;
            background: white;
            border-top: 3px solid #31a097;
            animation: slideInUp 1s ease-out;
        }
        .message-form-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .message-form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .message-form-header h3 {
            color: #31a097;
            font-size: 2rem;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }
        .message-form-header h3:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, #31a097, rgba(49, 160, 151, 0.2));
            bottom: 0;
            left: 20%;
            animation: lineExpand 0.8s ease-out;
        }
        @keyframes lineExpand {
            from { width: 0; }
            to { width: 60%; }
        }
        /* Modern Textarea */
        .message-input {
            width: 100%;
            padding: 20px;
            border: 2px solid #e0f2f1;
            border-radius: 15px;
            min-height: 180px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            resize: none;
            background: #f8fafc;
        }
        .message-input:focus {
            border-color: #31a097;
            box-shadow: 0 0 0 4px rgba(49, 160, 151, 0.2);
            outline: none;
            background: white;
        }
        /* Modern Submit Button */
        .submit-button {
            background: linear-gradient(135deg, #31a097 0%, #26867e 100%);
            color: white;
            padding: 18px 45px;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: block;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        .submit-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 10%, transparent 10.01%);
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.5s ease;
        }
        .submit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(49, 160, 151, 0.3);
        }
        .submit-button:active {
            transform: translateY(0);
            box-shadow: 0 5px 15px rgba(49, 160, 151, 0.3);
        }
        .submit-button:hover::after {
            transform: translate(-50%, -50%) scale(1);
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .trip-profile-header {
                flex-direction: column;
                text-align: center;
                padding: 30px;
            }
            .trip-profile-image {
                margin: 0 0 20px 0;
                width: 100px;
                height: 100px;
            }
            .trip-route-display {
                flex-direction: column;
                gap: 30px;
            }
            .route-city {
                margin: 0;
                width: 100%;
            }
            .route-arrow {
                transform: rotate(90deg);
                left: auto;
                top: 50%;
                margin: 15px 0;
            }
        }
        @media (max-width: 480px) {
            .trip-detail-container {
                padding: 0 15px;
            }
            .trip-details-grid {
                grid-template-columns: 1fr;
            }
            .detail-card {
                padding: 25px;
            }
            .message-input {
                min-height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="trip-detail-container">
            <div class="trip-detail-card">
                <div class="trip-profile-header">
                    <img src="<?= htmlspecialchars($trip['photo_profile']) ?>" alt="Profile Photo" class="trip-profile-image">
                    <div class="trip-owner-info">
                        <h1><?= htmlspecialchars($trip['name'].' '.$trip['surname']) ?></h1>
                        <span>İlan Sahibi</span>
                    </div>
                </div>
                <div class="trip-route-section">
                    <div class="trip-route-display">
                        <div class="route-city">
                            <h2><?= htmlspecialchars($trip['from_city']) ?></h2>
                            <p>Çıkış Tarihi: <?= htmlspecialchars($trip['formatted_date']) ?></p>
                        </div>
                        <div class="route-arrow">→</div>
                        <div class="route-city">
                            <h2><?= htmlspecialchars($trip['to_city']) ?></h2>
                            <p>Varış Tarihi: Belirtilmedi</p>
                        </div>
                    </div>
                </div>
                <div class="trip-details-grid">
                    <div class="detail-card">
                        <div class="detail-icon">⏳</div>
                        <div class="detail-value"><?= htmlspecialchars($trip['duration']) ?></div>
                        <div class="detail-label">Süre</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-icon">📅</div>
                        <div class="detail-value"><?= htmlspecialchars($trip['formatted_date']) ?></div>
                        <div class="detail-label">Tarih</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-icon">💵</div>
                        <div class="detail-value"><?= htmlspecialchars($trip['price']) ?> TL</div>
                        <div class="detail-label">Fiyat</div>
                    </div>
                </div>
                <div class="trip-description-section">
                    <h3>İlan Açıklaması</h3>
                    <p><?= htmlspecialchars($trip['description']) ?></p>
                </div>
                <div class="trip-message-form">
                    <form method="POST">
                        <div class="message-form-header">
                            <h3>Mesaj Gönder</h3>
                        </div>
                        <textarea class="message-input" name="message" placeholder="Mesajınızı yazın..."></textarea>
                        <button type="submit" class="submit-button">Gönder</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
include 'footer.php';
?>
