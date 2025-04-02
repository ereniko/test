<?php
session_start();
require 'db.php';
include 'header.php';

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Konuşmaları getir
$stmt = $pdo->prepare("
    SELECT c.id, c.type, c.created_at,
           u1.id AS user1_id, u1.name AS user1_name, u1.photo_profile AS user1_photo,
           u2.id AS user2_id, u2.name AS user2_name, u2.photo_profile AS user2_photo,
           t.from_city AS trip_from, t.to_city AS trip_to,
           k.teslim_alinan_sehir AS kurye_from, k.teslim_edilecek_sehir AS kurye_to,
           m.message AS last_message, m.created_at AS last_message_time
    FROM conversations c
    JOIN users u1 ON c.user1_id = u1.id
    JOIN users u2 ON c.user2_id = u2.id
    LEFT JOIN trips t ON c.trip_id = t.id
    LEFT JOIN kuryeler k ON c.kurye_id = k.id
    LEFT JOIN messages m ON m.id = (
        SELECT id FROM messages 
        WHERE conversation_id = c.id 
        ORDER BY created_at DESC LIMIT 1
    )
    WHERE (c.user1_id = ? OR c.user2_id = ?)
    ORDER BY m.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$conversations = $stmt->fetchAll();

// Konuşma silme
if(isset($_POST['delete_conversation'])) {
    $conversation_id = $_POST['conversation_id'];
    
    // Yetki kontrolü
    $stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
    $stmt->execute([$conversation_id, $user_id, $user_id]);
    
    if($stmt->rowCount() > 0) {
        try {
            $pdo->beginTransaction();
            
            // Mesajları sil
            $pdo->prepare("DELETE FROM messages WHERE conversation_id = ?")->execute([$conversation_id]);
            
            // Konuşmayı sil
            $pdo->prepare("DELETE FROM conversations WHERE id = ?")->execute([$conversation_id]);
            
            $pdo->commit();
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Konuşma silinemedi: ".$e->getMessage();
        }
    }
    header("Location: messages.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlarım</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .chat-list-container {
            max-width: 1200px;
            margin: 2rem auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .chat-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .chat-item:hover {
            transform: translateX(5px);
            background: #f1f3f5;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .chat-info {
            flex: 1;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .chat-title {
            font-weight: 600;
            color: #2c3e50;
        }

        .chat-type {
            font-size: 0.85rem;
            color: #31a097;
            padding: 3px 8px;
            border-radius: 5px;
            background: #e8f7f6;
        }

        .chat-preview {
            color: #666;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .chat-time {
            font-size: 0.8rem;
            color: #95a5a6;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="chat-list-container">
        <h1>Mesajlarım</h1>
        
        <?php foreach($conversations as $conv): 
            $other_user = ($conv['user1_id'] == $user_id) ? 
                ['name' => $conv['user2_name'], 'photo' => $conv['user2_photo']] : 
                ['name' => $conv['user1_name'], 'photo' => $conv['user1_photo']];
        ?>
        <div class="chat-item">
            <img src="<?= htmlspecialchars($other_user['photo']) ?>" 
                 alt="Profil" 
                 class="chat-avatar">
            
            <div class="chat-info">
                <div class="chat-header">
                    <span class="chat-title"><?= htmlspecialchars($other_user['name']) ?></span>
                    <span class="chat-type">
                        <?= $conv['type'] == 'trip' ? 'Paylaşımlı Yolculuk' : 'Kurye Sohbeti' ?>
                    </span>
                </div>
                <div class="chat-preview">
                    <?= htmlspecialchars($conv['last_message'] ?? 'Mesaj bulunamadı') ?>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="conversation.php?id=<?= $conv['id'] ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-comment"></i>
                </a>
                <form method="POST" 
                      onsubmit="return confirm('Bu konuşmayı silmek istediğinize emin misiniz?');">
                    <input type="hidden" name="conversation_id" value="<?= $conv['id'] ?>">
                    <button type="submit" name="delete_conversation" class="btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>