<?php
// header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Paylaşımlı Yolculuk</title>
  <!-- Ortak CSS -->
  <link rel="stylesheet" href="style.css">
  <!-- Font Awesome (ikonlar için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pc1xZdNjTTdXcbiUBefadGdf5jT+68mMR6EKgfpShl6hOHYQlGmKpV8XuUXu+4jRZHBSKLzZCjhLmn0sv2LQ9A=="
        crossorigin="anonymous" 
        referrerpolicy="no-referrer" />
>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        
  <!-- Ortak JS (profil menüsü, foto önizleme vs.) -->
  <script src="script.js" defer></script>
</head>
<body>

<header class="header-container">
  <div class="header-logo">
    <h1>YolArkadaşım</h1>
  </div>
  <nav class="nav-links">
    <a href="index.php">Anasayfa</a>
    <a href="trips.php">Yolculuğa Başla</a>
    <a href="kurye.php">Kurye Ara</a>


    <!-- "Yolculuğa Başla" CTA Butonu -->
    

    <!-- Profil Simge ve Dropdown -->
    <div class="profile-section" id="profileSection">
      <div class="profile-icon" id="profileIcon">
        <?php if(isset($_SESSION['user']) && !empty($_SESSION['user']['photo_profile'])): ?>
          <img src="<?php echo htmlspecialchars($_SESSION['user']['photo_profile']); ?>" alt="Profil">
        <?php else: ?>
          <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profil">
        <?php endif; ?>
      </div>
      <div class="profile-dropdown" id="profileDropdown">
        <?php if(isset($_SESSION['user'])): ?>
          <a href="profile.php"><i class="fas fa-user"></i> Profilim</a>
          <a href="my_ads.php"><i class="fas fa-list"></i> İlanlarım</a>
          <a href="my_applications.php"><i class="fas fa-clipboard-list"></i> Başvurularım</a>
          <a href="messages.php"><i class="fas fa-clipboard-list"></i>Mesajlarım</a>
          <a href="create_trip.php" class="cta">Yolculuğa Ekle</a>
          <a href="kurye-ekle.php" class="cta">Kurye Ekle</a>
          <?php if($_SESSION['user']['role'] === 'admin'): ?>
            <a href="admin/index.php" style="color:blue;"><i class="fas fa-shield-halved"></i> Admin Panel</a>
          <?php endif; ?>
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        <?php else: ?>
          <a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a>
          <a href="register.php"><i class="fas fa-user-plus"></i> Kayıt Ol</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</header>
