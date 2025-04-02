<?php
session_start();
require 'db.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$conversation_id = $_GET['id'] ?? null;

// Konuşma detaylarını getir
if ($conversation_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u1.id AS user1_id, u1.name AS user1_name, u1.photo_profile AS user1_photo,
               u2.id AS user2_id, u2.name AS user2_name, u2.photo_profile AS user2_photo,
               t.from_city AS trip_from, t.to_city AS trip_to,
               k.teslim_alinan_sehir AS kurye_from, k.teslim_edilecek_sehir AS kurye_to
        FROM conversations c
        LEFT JOIN trips t ON c.trip_id = t.id
        LEFT JOIN kuryeler k ON c.kurye_id = k.id
        JOIN users u1 ON c.user1_id = u1.id
        JOIN users u2 ON c.user2_id = u2.id
        WHERE c.id = ? AND (c.user1_id = ? OR c.user2_id = ?)
    ");
    $stmt->execute([$conversation_id, $user_id, $user_id]);
    $conversation = $stmt->fetch();

    if (!$conversation) {
        header("Location: messages.php");
        exit;
    }

    // Mesajları getir (burada sender_id yerine from_user_id kullanıyoruz)
    $stmt = $pdo->prepare("
        SELECT m.*, u.name AS sender_name, u.photo_profile
        FROM messages m
        JOIN users u ON m.from_user_id = u.id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$conversation_id]);
    $messages = $stmt->fetchAll();
}

// Mesaj gönderme işlemi (INSERT sorgusunda da sütun adını güncelliyoruz)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);

    if (!empty($message) && $conversation) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO messages (conversation_id, from_user_id, message)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$conversation_id, $user_id, $message]);

            $pdo->commit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Mesaj gönderilemedi: " . $e->getMessage();
        }
    }
    header("Location: conversation.php?id=$conversation_id");
    exit;
}

// Mesaj silme işlemi (DELETE sorgusunda da sütun adını güncelliyoruz)
if (isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            DELETE FROM messages 
            WHERE id = ? AND from_user_id = ?
        ");
        $stmt->execute([$message_id, $user_id]);

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Mesaj silinemedi: " . $e->getMessage();
    }
    header("Location: conversation.php?id=$conversation_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sohbet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .chat-container {
            max-width: 1200px;
            margin: 2rem auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .chat-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .chat-subtitle {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .message-container {
            max-height: 60vh;
            overflow-y: auto;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message {
            display: flex;
            gap: 15px;
            margin: 15px 0;
        }
        .message.self {
            flex-direction: row-reverse;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 20px;
            background: #e9ecef;
            position: relative;
        }
        .message.self .message-bubble {
            background: #31a097;
            color: white;
        }
        .message-time {
            font-size: 0.75rem;
            color: #95a5a6;
            margin-top: 5px;
        }
        .message-actions {
            display: none;
            position: absolute;
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
        }
        .message:hover .message-actions {
            display: block;
        }
        .delete-btn {
            color: #e74c3c;
            cursor: pointer;
            background: none;
            border: none;
        }
        .message-input-container {
            display: flex;
            gap: 10px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
            min-height: 50px;
        }
        .send-btn {
            background: #31a097;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .send-btn:hover {
            background: #26867e;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <?php if ($conversation): ?>
        <div class="chat-header">
            <div class="chat-title">
                <?= $conversation['type'] == 'trip' ? 
                    htmlspecialchars($conversation['trip_from'] . ' → ' . $conversation['trip_to']) : 
                    htmlspecialchars($conversation['kurye_from'] . ' → ' . $conversation['kurye_to']) ?>
            </div>
            <div class="chat-subtitle">
                <?= htmlspecialchars($conversation['user1_name'] . ' ↔ ' . $conversation['user2_name']) ?>
            </div>
        </div>

        <div class="message-container">
            <?php foreach ($messages as $msg): 
                $is_self = $msg['from_user_id'] == $user_id;
            ?>
            <div class="message <?= $is_self ? 'self' : '' ?>">
                <img src="<?= htmlspecialchars($msg['photo_profile']) ?>" 
                     class="message-avatar" 
                     alt="Profil">
                
                <div class="message-bubble">
                    <div class="message-text"><?= htmlspecialchars($msg['message']) ?></div>
                    <div class="message-time">
                        <?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?>
                        <?php if ($is_self): ?>
                        <form method="POST" 
                              onsubmit="return confirm('Bu mesajı silmek istediğinize emin misiniz?')"
                              style="display: inline-block; margin-left: 10px;">
                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                            <button type="submit" name="delete_message" class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" class="message-input-container">
            <textarea class="message-input" 
                      name="message" 
                      placeholder="Mesajınızı yazın..."
                      required></textarea>
            <button type="submit" class="send-btn">
                <i class="fas fa-paper-plane"></i> Gönder
            </button>
        </form>
        <?php else: ?>
        <div class="alert alert-danger">Konuşma bulunamadı!</div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>
