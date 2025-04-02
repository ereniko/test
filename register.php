<?php
// register.php
session_start();
include 'db.php';  // PDO bağlantısı

// Değişkenleri başlat
$tckimlik = $name = $surname = $city = $district = $email = "";
$errors = [];
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini alalım
    $tckimlik = trim($_POST['tckimlik'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $surname  = trim($_POST['surname'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Temel validasyonlar (T.C., Ad, Soyad, E-Mail, Şifre)
    if(empty($tckimlik) || empty($name) || empty($surname) || empty($email) || empty($password)) {
        $errors[] = "Lütfen tüm zorunlu alanları doldurun (T.C., Ad, Soyad, E-Posta, Şifre).";
    }
    if(strlen($tckimlik) !== 11 || !ctype_digit($tckimlik)) {
        $errors[] = "T.C. Kimlik 11 haneli ve sadece rakamlardan oluşmalıdır.";
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir E-Mail adresi girin.";
    }
    if(strlen($password) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalıdır.";
    }

    // Fotoğraflar (3'ü de zorunlu):
    // Profil Fotoğrafı
    if(!isset($_FILES['photo_profile']) || $_FILES['photo_profile']['error'] !== UPLOAD_ERR_OK){
        $errors[] = "Profil fotoğrafı zorunludur. Lütfen yükleyin.";
    }
    // Kimlik Ön
    if(!isset($_FILES['photo_id_front']) || $_FILES['photo_id_front']['error'] !== UPLOAD_ERR_OK){
        $errors[] = "Kimlik ön yüz fotoğrafı zorunludur. Lütfen yükleyin.";
    }
    // Kimlik Arka
    if(!isset($_FILES['photo_id_back']) || $_FILES['photo_id_back']['error'] !== UPLOAD_ERR_OK){
        $errors[] = "Kimlik arka yüz fotoğrafı zorunludur. Lütfen yükleyin.";
    }

    // Duplicate kontrolü (TC veya E-Mail)
    if(empty($errors)){
        $stmtCheck = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE tckimlik = :tckimlik OR email = :email
        ");
        $stmtCheck->execute([
            ':tckimlik' => $tckimlik,
            ':email'    => $email
        ]);
        if($stmtCheck->fetch()){
            $errors[] = "Bu T.C. Kimlik numarası veya E-Mail adresi zaten kayıtlıdır. Lütfen farklı bilgiler girin.";
        }
    }

    // Eğer şu ana kadar error yoksa dosyaları yüklemeye çalış
    $uploadDir = 'uploads/';
    $photo_profile_path = '';
    $photo_id_front_path = '';
    $photo_id_back_path = '';

    if(empty($errors)){
        // Profil Fotoğrafı
        $tmp_name_profile = $_FILES['photo_profile']['tmp_name'];
        $filename_profile = basename($_FILES['photo_profile']['name']);
        $photo_profile_path = $uploadDir . uniqid('pp_') . '_' . $filename_profile;
        if(!move_uploaded_file($tmp_name_profile, $photo_profile_path)){
            $errors[] = "Profil fotoğrafı yüklenemedi. Lütfen tekrar deneyin.";
        }

        // Kimlik Ön
        $tmp_name_front = $_FILES['photo_id_front']['tmp_name'];
        $filename_front = basename($_FILES['photo_id_front']['name']);
        $photo_id_front_path = $uploadDir . uniqid('idfront_') . '_' . $filename_front;
        if(!move_uploaded_file($tmp_name_front, $photo_id_front_path)){
            $errors[] = "Kimlik ön yüz fotoğrafı yüklenemedi. Lütfen tekrar deneyin.";
        }

        // Kimlik Arka
        $tmp_name_back = $_FILES['photo_id_back']['tmp_name'];
        $filename_back = basename($_FILES['photo_id_back']['name']);
        $photo_id_back_path = $uploadDir . uniqid('idback_') . '_' . $filename_back;
        if(!move_uploaded_file($tmp_name_back, $photo_id_back_path)){
            $errors[] = "Kimlik arka yüz fotoğrafı yüklenemedi. Lütfen tekrar deneyin.";
        }
    }

    // Hala error yoksa veritabanına kayıt
    if(empty($errors)){
        // Şifre hash'le
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
          INSERT INTO users 
            (tckimlik, name, surname, city, district, email, password, 
             photo_profile, photo_id_front, photo_id_back, email_verified, is_approved)
          VALUES 
            (:tckimlik, :name, :surname, :city, :district, :email, :password, 
             :photo_profile, :photo_id_front, :photo_id_back, 0, 0)
        ");

        $result = $stmt->execute([
            ':tckimlik'       => $tckimlik,
            ':name'           => $name,
            ':surname'        => $surname,
            ':city'           => $city,
            ':district'       => $district,
            ':email'          => $email,
            ':password'       => $hashedPassword,
            ':photo_profile'  => $photo_profile_path,
            ':photo_id_front' => $photo_id_front_path,
            ':photo_id_back'  => $photo_id_back_path
        ]);

        if($result) {
            $successMessage = "Kayıt başarılı. Artık giriş yapabilirsiniz.";
            // Başarılı kayıt sonrası formu temizleme (opsiyonel)
            $tckimlik = $name = $surname = $city = $district = $email = "";
        } else {
            $errors[] = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
        }
    }
}

// header.php dahil
include 'header.php';
?>

<div class="register-container">
  <div class="register-header">
    <h2>Yeni Hesap Oluştur</h2>
    <p>Turkuaz renk temasıyla hazırlanmış modern kayıt formu</p>
  </div>

  <?php
  // Hata mesajları
  if(!empty($errors)){
    foreach($errors as $err){
      echo "<p style='color:red;'>$err</p>";
    }
  }
  // Başarı mesajı
  if($successMessage){
    echo "<p style='color:green;'>$successMessage</p>";
  }
  ?>

  <form class="form-grid" method="post" enctype="multipart/form-data" action="">
    <!-- Sol Sütun -->
    <div class="form-column">
      <div class="input-group">
        <label for="tckimlik"><i class="fas fa-id-card"></i> T.C. Kimlik No</label>
        <input type="text" id="tckimlik" name="tckimlik" 
               value="<?php echo htmlspecialchars($tckimlik); ?>" 
               required>
      </div>

      <div class="input-group">
        <label for="name"><i class="fas fa-user"></i> Adınız</label>
        <input type="text" id="name" name="name" 
               value="<?php echo htmlspecialchars($name); ?>" 
               required>
      </div>

      <div class="input-group">
        <label for="surname"><i class="fas fa-users"></i> Soyadınız</label>
        <input type="text" id="surname" name="surname" 
               value="<?php echo htmlspecialchars($surname); ?>" 
               required>
      </div>

      <div class="input-group">
        <label for="city"><i class="fas fa-city"></i> Şehir</label>
        <input type="text" id="city" name="city" 
               value="<?php echo htmlspecialchars($city); ?>">
      </div>
    </div>

    <!-- Sağ Sütun -->
    <div class="form-column">
      <div class="input-group">
        <label for="district"><i class="fas fa-map-marker-alt"></i> İlçe</label>
        <input type="text" id="district" name="district" 
               value="<?php echo htmlspecialchars($district); ?>">
      </div>

      <div class="input-group">
        <label for="email"><i class="fas fa-envelope"></i> E-Posta</label>
        <input type="email" id="email" name="email" 
               value="<?php echo htmlspecialchars($email); ?>" 
               required>
      </div>

      <div class="input-group">
        <label for="password"><i class="fas fa-lock"></i> Şifre</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="input-group">
        <label><i class="fas fa-shield-alt"></i> Doğrulama Kodu</label>
        <div class="verify-section">
          <input type="text" id="verifyCode" name="verifyCode" placeholder="Mail kodunuz">
          <button type="button" class="upload-btn" id="sendVerifyBtn">
            <i class="fas fa-paper-plane"></i> Gönder
          </button>
        </div>
      </div>
    </div>

    <!-- Fotoğraf Yükleme Alanı -->
    <div class="form-column" style="grid-column: 1/-1">
      <div class="input-group">
        <label><i class="fas fa-camera-retro"></i> Profil Fotoğrafı</label>
        
        <div class="photo-upload-group">
          <button type="button" class="upload-btn" 
                  onclick="document.getElementById('photo_profile').click()">
            <i class="fas fa-portrait"></i> Profil Fotoğrafı
          </button>
          <div class="preview-container">
            <img id="profilePreview" 
                 src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="
                 alt="Profile Preview">
          </div>
        </div>

        <div class="photo-upload-group">
          <button type="button" class="upload-btn" 
                  onclick="document.getElementById('photo_id_front').click()">
            <i class="fas fa-id-card-alt"></i> Ön Yüz
          </button>
          <div class="preview-container">
            <img id="frontPreview"
                 src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="
                 alt="Front ID Preview">
          </div>
        </div>

        <div class="photo-upload-group">
          <button type="button" class="upload-btn" 
                  onclick="document.getElementById('photo_id_back').click()">
            <i class="fas fa-id-card-alt"></i> Arka Yüz
          </button>
          <div class="preview-container">
            <img id="backPreview" 
                 src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="
                 alt="Back ID Preview">
          </div>
        </div>
      </div>
    </div>

    <!-- Gizli Inputlar -->
    <input type="file" id="photo_profile" name="photo_profile" accept="image/*" 
           onchange="previewImage(this, 'profilePreview')">
    <input type="file" id="photo_id_front" name="photo_id_front" accept="image/*" 
           onchange="previewImage(this, 'frontPreview')">
    <input type="file" id="photo_id_back" name="photo_id_back" accept="image/*" 
           onchange="previewImage(this, 'backPreview')">

    <div class="form-actions">
      <button type="submit">
        <i class="fas fa-user-plus"></i> HEMEN KAYDOL
      </button>
    </div>
  </form>
</div>

<script>
function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  const file = input.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(e) {
    preview.src = e.target.result;
    preview.style.opacity = '1';
  }
  reader.readAsDataURL(file);
}
</script>

<?php
include 'footer.php';
?>
