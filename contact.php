<?php
include 'header.php';
include 'db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // XSS ve SQL Injection korumalı veri temizleme
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    // Validasyon
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Tüm alanların doldurulması zorunludur!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçersiz e-posta formatı!";
    } else {
        try {
            // Veritabanına kayıt
            $stmt = $pdo->prepare("
                INSERT INTO contacts 
                (name, email, subject, message) 
                VALUES (:name, :email, :subject, :message)
            ");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);
            $success = "Mesajınız başarıyla gönderildi!";
        } catch (PDOException $e) {
            $error = "Hata: " . $e->getMessage();
        }
    }
}
?>

<!-- Özel CSS -->
<style>
    .contact-section {
        max-width: 800px;
        margin: 50px auto;
        padding: 40px;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(42,92,130,0.1);
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .contact-title {
        text-align: center;
        color: #2A5C82;
        margin-bottom: 30px;
        font-size: 2.2em;
        position: relative;
    }

    .contact-title:after {
        content: '';
        display: block;
        width: 60px;
        height: 3px;
        background: #FF9800;
        margin: 15px auto;
    }

    .contact-form .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .contact-form input:focus,
    .contact-form textarea:focus {
        border-color: #2A5C82;
        box-shadow: 0 4px 15px rgba(42,92,130,0.1);
    }

    .form-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #2A5C82;
        font-size: 18px;
    }

    .submit-btn {
        background: linear-gradient(135deg, #2A5C82, #1F4661);
        color: #fff;
        padding: 12px 35px;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(42,92,130,0.3);
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        text-align: center;
    }

    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
</style>

<div class="contact-section">
    <h2 class="contact-title">🚗 Bize Ulaşın</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form class="contact-form" method="POST">
        <div class="form-group">
            <i class="fas fa-user form-icon"></i>
            <input type="text" name="name" placeholder="Adınız Soyadınız" required>
        </div>

        <div class="form-group">
            <i class="fas fa-envelope form-icon"></i>
            <input type="email" name="email" placeholder="E-posta Adresiniz" required>
        </div>

        <div class="form-group">
            <i class="fas fa-tag form-icon"></i>
            <input type="text" name="subject" placeholder="Konu" required>
        </div>

        <div class="form-group">
            <i class="fas fa-comment form-icon" style="top: 28px"></i>
            <textarea name="message" rows="5" placeholder="Mesajınız..." required></textarea>
        </div>

        <button type="submit" class="submit-btn">
            <i class="fas fa-paper-plane"></i>
            Mesaj Gönder
        </button>
    </form>
</div>

<?php include 'footer.php'; ?>