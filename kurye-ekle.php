<?php
// Oturum başlatılıyor
session_start();

// Veritabanı bağlantısı
include 'db.php';

// Header dosyası
include 'header.php';

// Giriş kontrolü: Kullanıcı giriş yapmamışsa login sayfasına yönlendir
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Hata ve başarı mesajları için değişkenler
$error = '';
$success = '';

// Form gönderildiyse verileri işleyelim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Formdan gelen verileri alıyoruz
        $ad_soyad              = trim($_POST['ad_soyad']);
        $tckimlik              = trim($_POST['tckimlik']);
        $telefon               = trim($_POST['telefon']);
        $sehir                 = trim($_POST['sehir']);
        $acik_adres            = trim($_POST['acik_adres']);
        $teslim_alinan_adres   = trim($_POST['teslim_alinan_adres']);
        $teslim_edilecek_adres = trim($_POST['teslim_edilecek_adres']);
        $siparis_detay         = trim($_POST['siparis_detay']);

        // Fotoğraf yükleme işlemi
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Eğer "uploads" klasörü yoksa oluştur
        }

        $foto1 = "";
        $foto2 = "";

        // Fotoğraf 1 yükleme
        if (!empty($_FILES['foto1']['name'])) {
            $foto1 = $uploadDir . uniqid() . "_" . basename($_FILES['foto1']['name']);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['foto1']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Sadece JPG, PNG veya GIF dosyaları yüklenebilir!');
            }
            if (!move_uploaded_file($_FILES['foto1']['tmp_name'], $foto1)) {
                throw new Exception('Fotoğraf 1 yüklenirken bir hata oluştu.');
            }
        } else {
            throw new Exception('Lütfen en az bir fotoğraf yükleyin.');
        }

        // Fotoğraf 2 yükleme (opsiyonel)
        if (!empty($_FILES['foto2']['name'])) {
            $foto2 = $uploadDir . uniqid() . "_" . basename($_FILES['foto2']['name']);
            $fileType = mime_content_type($_FILES['foto2']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Sadece JPG, PNG veya GIF dosyaları yüklenebilir!');
            }
            if (!move_uploaded_file($_FILES['foto2']['tmp_name'], $foto2)) {
                throw new Exception('Fotoğraf 2 yüklenirken bir hata oluştu.');
            }
        }

        // SQL sorgusu ile veritabanına ekleme
        $stmt = $pdo->prepare("INSERT INTO kuryeler 
            (ad_soyad, tckimlik, telefon, sehir, acik_adres, teslim_alinan_adres, teslim_edilecek_adres, siparis_detay, foto1, foto2, eklenme_tarihi)
            VALUES 
            (:ad_soyad, :tckimlik, :telefon, :sehir, :acik_adres, :teslim_alinan_adres, :teslim_edilecek_adres, :siparis_detay, :foto1, :foto2, NOW())");

        $stmt->execute([
            ':ad_soyad' => $ad_soyad,
            ':tckimlik' => $tckimlik,
            ':telefon' => $telefon,
            ':sehir' => $sehir,
            ':acik_adres' => $acik_adres,
            ':teslim_alinan_adres' => $teslim_alinan_adres,
            ':teslim_edilecek_adres' => $teslim_edilecek_adres,
            ':siparis_detay' => $siparis_detay,
            ':foto1' => $foto1,
            ':foto2' => $foto2
        ]);

        $success = "Kurye ilanı başarıyla eklendi!";
    } catch (Exception $e) {
        $error = "Hata: " . $e->getMessage();
        // Yüklenen dosyaları sil
        if (!empty($foto1) && file_exists($foto1)) unlink($foto1);
        if (!empty($foto2) && file_exists($foto2)) unlink($foto2);
    }
}
?>

<div class="container">
    <div class="kurye-ekle-form">
        <h2>Yeni Kurye İlanı Oluştur</h2>
        <!-- Hata veya Başarı mesajlarını göster -->
        <?php if (!empty($error)): ?>
            <div class="error-message" style="color:red; margin-bottom:20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-message" style="color:green; margin-bottom:20px;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <!-- Ad Soyad -->
                <div class="form-group">
                    <label>Ad Soyad:</label>
                    <input type="text" name="ad_soyad" required placeholder="Kuryenin adı soyadı">
                </div>
                <!-- TC Kimlik No -->
                <div class="form-group">
                    <label>TC Kimlik No:</label>
                    <input type="text" name="tckimlik" required maxlength="11" pattern="\d{11}" placeholder="11 haneli TC Kimlik No">
                </div>
                <!-- Telefon -->
                <div class="form-group">
                    <label>Telefon:</label>
                    <input type="tel" name="telefon" required placeholder="05xx xxx xx xx">
                </div>
                <!-- Şehir -->
                <div class="form-group">
                    <label>Şehir:</label>
                    <input type="text" name="sehir" required placeholder="Şehir">
                </div>
                <!-- Açık Adres -->
                <div class="form-group full-width">
                    <label>Açık Adres:</label>
                    <textarea name="acik_adres" rows="3" required placeholder="Kuryenin açık adresi"></textarea>
                </div>
                <!-- Teslim Alınacak Adres -->
                <div class="form-group full-width">
                    <label>Teslim Alınacak Adres:</label>
                    <textarea name="teslim_alinan_adres" rows="3" required placeholder="Siparişin alınacağı adres"></textarea>
                </div>
                <!-- Teslim Edilecek Adres -->
                <div class="form-group full-width">
                    <label>Teslim Edilecek Adres:</label>
                    <textarea name="teslim_edilecek_adres" rows="3" required placeholder="Siparişin teslim edileceği adres"></textarea>
                </div>
                <!-- Sipariş Detayları -->
                <div class="form-group full-width">
                    <label>Sipariş Detayları:</label>
                    <textarea name="siparis_detay" rows="5" required placeholder="Sipariş içeriği ve özel notlar"></textarea>
                </div>
                <!-- Fotoğraf Yükleme Alanları -->
                <div class="form-group">
                    <label>Sipariş Fotoğrafı 1:</label>
                    <div class="file-upload">
                        <input type="file" id="foto1" name="foto1" required accept="image/*">
                        <span class="upload-text">Dosya Seç</span>
                    </div>
                    <img id="foto1Preview" src="#" alt="Fotoğraf Önizleme" style="display:none; max-width:150px; margin-top:10px;">
                </div>
                <div class="form-group">
                    <label>Sipariş Fotoğrafı 2:</label>
                    <div class="file-upload">
                        <input type="file" id="foto2" name="foto2" accept="image/*">
                        <span class="upload-text">Dosya Seç</span>
                    </div>
                    <img id="foto2Preview" src="#" alt="Fotoğraf Önizleme" style="display:none; max-width:150px; margin-top:10px;">
                </div>
            </div>
            <button type="submit" name="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> İlanı Yayınla
            </button>
        </form>
    </div>
</div>

<!-- JavaScript Kodları -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fotoğraf önizleme işlevi
    function previewImage(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        });
    }

    // Fotoğraf önizleme çağırma
    previewImage('foto1', 'foto1Preview');
    previewImage('foto2', 'foto2Preview');
});
</script>

<!-- CSS Tarzları -->
<style>
.container {
    max-width: 800px;
    margin: 30px auto;
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: #31a097;
    outline: none;
}
.full-width {
    grid-column: 1 / -1;
}
.file-upload {
    position: relative;
    overflow: hidden;
}
.file-upload input[type="file"] {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}
.upload-text {
    display: block;
    padding: 10px 15px;
    background: #f0f0f0;
    border: 2px dashed #ccc;
    border-radius: 8px;
    text-align: center;
    color: #666;
    transition: all 0.3s ease;
}
.file-upload:hover .upload-text {
    border-color: #31a097;
    color: #31a097;
}
.submit-btn {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #31a097, #26867e);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(49, 160, 151, 0.3);
}
.error-message {
    color: red;
    margin-bottom: 20px;
}
.success-message {
    color: green;
    margin-bottom: 20px;
}
</style>
<?php include 'footer.php'; ?>