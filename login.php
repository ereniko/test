<?php
// Oturum başlatılıyor
session_start();

// Veritabanı bağlantısı
include 'db.php';

// Header dosyası
include 'header.php';

// Eğer kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Lütfen E-Mail ve Şifre alanlarını doldurun.';
    } else {
        // Kullanıcıyı veritabanından çekiyoruz
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Şifreyi doğruluyoruz
            if (password_verify($password, $user['password']) || $user['password'] === $password) {
                if (!empty($user['is_banned']) && $user['is_banned']) {
                    $error = 'Hesabınız yasaklanmıştır.';
                } else {
                    // Oturum değişkenlerini ayarlıyoruz
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'surname' => $user['surname'],
                        'role' => $user['role'],
                        'email' => $user['email']
                    ];

                    // Ana sayfaya yönlendiriyoruz
                    header('Location: index.php');
                    exit;
                }
            } else {
                $error = 'Yanlış şifre.';
            }
        } else {
            $error = 'Kullanıcı bulunamadı.';
        }
    }
}
?>
<div class="login-container">
    <h2>Giriş Yap</h2>
    <?php if ($error): ?>
        <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form action="" method="post">
        <label for="email">E-Mail</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Şifre</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Giriş Yap</button>
    </form>
    <p class="register-link">Henüz bir hesabınız yok mu? <a href="/yolarkadasim/register.php">Kayıt Ol</a></p>
</div>
<?php include 'footer.php'; ?>