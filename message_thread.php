<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$trip_id = $_GET['trip_id'] ?? 0;

// Fetch trip details
$stmt = $pdo->prepare("
    SELECT t.*, u.id as owner_id 
    FROM trips t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.id = ?
");
$stmt->execute([$trip_id]);
$trip = $stmt->fetch();

if (!$trip) {
    die('Trip not found');
}

$current_user_id = $_SESSION['user']['id'];
$is_owner = ($current_user_id == $trip['user_id']);

// Check if user is part of the conversation
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM messages 
    WHERE trip_id = ? 
    AND (from_user_id = ? OR to_user_id = ?)
");
$stmt->execute([$trip_id, $current_user_id, $current_user_id]);
$message_count = $stmt->fetchColumn();

if (!$is_owner && $message_count == 0) {
    die('You are not part of this conversation');
}

// Fetch all messages
$stmt = $pdo->prepare("
    SELECT m.*, u.name as sender_name, u.photo_profile 
    FROM messages m 
    JOIN users u ON m.from_user_id = u.id 
    WHERE m.trip_id = ? 
    ORDER BY m.created_at ASC
");
$stmt->execute([$trip_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new message
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    
    if (!empty($message)) {
        // Determine recipient
        if ($is_owner) {
            // Find the other user from existing messages
            $stmt = $pdo->prepare("
                SELECT from_user_id 
                FROM messages 
                WHERE trip_id = ? 
                AND from_user_id != ? 
                LIMIT 1
            ");
            $stmt->execute([$trip_id, $current_user_id]);
            $other_user = $stmt->fetch();
            $to_user_id = $other_user['from_user_id'] ?? null;
            
            if (!$to_user_id) {
                $error = "No recipient found.";
            }
        } else {
            $to_user_id = $trip['user_id'];
        }

        if (!$error) {
            $stmt = $pdo->prepare("
                INSERT INTO messages (trip_id, from_user_id, to_user_id, message)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $trip_id,
                $current_user_id,
                $to_user_id,
                $message
            ]);
            header("Location: message_thread.php?trip_id=$trip_id");
            exit;
        }
    } else {
        $error = "Message cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Thread</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .message-thread {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message {
            display: flex;
            margin: 15px 0;
            align-items: start;
        }
        .message.sent {
            flex-direction: row-reverse;
        }
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 10px;
        }
        .message-content {
            max-width: 70%;
            padding: 10px;
            border-radius: 10px;
            background: #f1f0f0;
        }
        .message.sent .message-content {
            background: #31a097;
            color: white;
        }
        .message-text {
            margin: 0;
        }
        .message-time {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        .message.sent .message-time {
            color: #e0e0e0;
        }
        .message-form {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .message-form textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        .message-form button {
            padding: 10px 20px;
            background: #31a097;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message-thread">
            <h2>Conversation for Trip: <?= htmlspecialchars($trip['from_city'] . ' → ' . $trip['to_city']) ?></h2>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['from_user_id'] == $current_user_id ? 'sent' : 'received' ?>">
                    <img src="<?= htmlspecialchars($msg['photo_profile']) ?>" class="profile-pic" alt="Profile">
                    <div class="message-content">
                        <p class="message-text"><?= htmlspecialchars($msg['message']) ?></p>
                        <div class="message-time">
                            <?= date('d M H:i', strtotime($msg['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <form method="post" class="message-form">
                <textarea name="message" rows="2" placeholder="Type your message..."></textarea>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>