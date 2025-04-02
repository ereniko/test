<?php
session_start();
include 'db.php';
include 'header.php';

// Kullanıcının tüm konuşmalarını getir
$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("
    SELECT c.*, 
           u.name AS other_user_name,
           u.photo_profile AS other_user_photo,
           m.message AS last_message,
           m.created_at AS last_message_time,
           SUM(CASE WHEN m.has_read = 0 AND m.to_user_id = ? THEN 1 ELSE 0 END) AS unread_count
    FROM conversations c
    JOIN users u ON (c.user1_id = u.id OR c.user2_id = u.id) AND u.id != ?
    LEFT JOIN messages m ON c.id = m.conversation_id
    WHERE c.user1_id = ? OR c.user2_id = ?
    GROUP BY c.id
    ORDER BY m.created_at DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sohbetlerim</title>
    <style>
        .chat-menu {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .conversation-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        .conversation-item:hover {
            background: #f8f9fa;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .unread-badge {
            background: #31a097;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="chat-menu">
        <h2 style="padding:20px; border-bottom:2px solid #31a097;">Sohbetlerim</h2>
        
        <?php foreach ($conversations as $conv): ?>
        <a href="conversation.php?id=<?= $conv['id'] ?>" style="text-decoration:none; color:inherit;">
            <div class="conversation-item">
                <img src="<?= $conv['other_user_photo'] ?>" class="user-avatar">
                <div>
                    <h3><?= $conv['other_user_name'] ?></h3>
                    <p><?= substr($conv['last_message'], 0, 30) ?>...</p>
                    <small><?= date('d.m.Y H:i', strtotime($conv['last_message_time'])) ?></small>
                </div>
                <?php if ($conv['unread_count'] > 0): ?>
                <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</body>
</html>