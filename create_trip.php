<?php
// create_trip.php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_city = trim($_POST['from_city'] ?? '');
    $to_city   = trim($_POST['to_city'] ?? '');
    $price     = trim($_POST['price'] ?? '');
    $trip_date = trim($_POST['trip_date'] ?? '');
    $trip_time = trim($_POST['trip_time'] ?? '');
    $details   = trim($_POST['details'] ?? '');

    // Şehir validasyonu (sadece harf ve boşluk, 2-50 karakter)
    $city_pattern = '/^[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,50}$/u';
    $validation_errors = [];
    
    if(empty($from_city) || !preg_match($city_pattern, $from_city)) {
        $validation_errors[] = 'Geçerli bir kalkış şehri giriniz (sadece harf ve boşluk)';
    }
    
    if(empty($to_city) || !preg_match($city_pattern, $to_city)) {
        $validation_errors[] = 'Geçerli bir varış şehri giriniz (sadece harf ve boşluk)';
    }
    
    if(empty($price) || !is_numeric($price) || $price <= 0) {
        $validation_errors[] = 'Geçerli bir fiyat giriniz';
    }
    
    if(empty($trip_date) || empty($trip_time)) {
        $validation_errors[] = 'Tarih ve saat zorunludur';
    } else {
        $datetime = DateTime::createFromFormat('Y-m-d H:i', $trip_date . ' ' . $trip_time);
        if(!$datetime || $datetime < new DateTime()) {
            $validation_errors[] = 'Geçerli bir tarih ve saat seçiniz';
        }
    }
    
    if(!empty($validation_errors)) {
        $error = implode('<br>', $validation_errors);
    } else {
        try {
            // Tarih ve saati birleştir
            $trip_datetime = $trip_date . ' ' . $trip_time . ':00';
            
            $stmt = $pdo->prepare("INSERT INTO trips 
                (user_id, from_city, to_city, price, details, duration, trip_date) 
                VALUES (:uid, :fc, :tc, :p, :dt, :dr, :td)");
            // Burada "duration" sütunu formumuzda "Kalkış Saati" yerine "Tahmini Süre" olarak alınabilir.
            // Ancak örneğimizde "duration" olarak kullanıyoruz; isteğe göre input adını değiştirebilirsiniz.
            $stmt->execute([
                'uid' => $_SESSION['user']['id'],
                'fc'  => $from_city,
                'tc'  => $to_city,
                'p'   => $price,
                'dt'  => $details,
                'dr'  => $trip_time,  // Alternatif olarak "duration" olarak alınacaksa input adını "duration" yapabilirsiniz.
                'td'  => $trip_datetime
            ]);
            header('Location: trips.php');
            exit;
        } catch(PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yeni Seyahat İlanı</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script>
    // JS: Şehir alanında yalnızca Türk şehirlerinden seçilmesi için kontrol
    document.addEventListener('DOMContentLoaded', function() {
      const turkishCities = [
        "Adana","Adıyaman","Afyonkarahisar","Ağrı","Aksaray","Amasya",
        "Ankara","Antalya","Ardahan","Artvin","Balıkesir","Bartın",
        "Batman","Bayburt","Bilecik","Bingöl","Bitlis","Bolu","Burdur",
        "Bursa","Çanakkale","Çankırı","Çorum","Denizli","Diyarbakır",
        "Düzce","Edirne","Elazığ","Erzincan","Erzurum","Eskişehir",
        "Gaziantep","Giresun","Gümüşhane","Hakkari","Hatay","Iğdır",
        "Isparta","İstanbul (Anadolu)","İstanbul (Avrupa)","İzmir",
        "Kahramanmaraş","Karabük","Karaman","Kars","Kastamonu","Kayseri",
        "Kırıkkale","Kırklareli","Kırşehir","Kilis","Kocaeli","Konya",
        "Kütahya","Malatya","Manisa","Mardin","Mersin","Muğla","Muş",
        "Nevşehir","Niğde","Ordu","Osmaniye","Rize","Sakarya","Samsun",
        "Siirt","Sinop","Sivas","Şanlıurfa","Şırnak","Tekirdağ","Tokat",
        "Trabzon","Tunceli","Uşak","Van","Yalova","Yozgat","Zonguldak"
      ];
      
      function restrictCityInput(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('blur', function() {
          const entered = input.value.trim();
          if (entered && !turkishCities.includes(entered)) {
            alert("Lütfen sadece geçerli Türk şehirlerinden birini seçin!");
            input.value = '';
            input.focus();
          }
        });
      }
      
      restrictCityInput('from_city');
      restrictCityInput('to_city');

      // Takvim ve saat inputları için ek animasyon (hover/focus)
      const dateInputs = document.querySelectorAll('input[type="date"], input[type="time"]');
      dateInputs.forEach(input => {
        input.addEventListener('focus', function() {
          input.style.transition = 'box-shadow 0.3s ease';
          input.style.boxShadow = '0 0 8px rgba(49,160,151,0.3)';
        });
        input.addEventListener('blur', function() {
          input.style.boxShadow = 'none';
        });
      });

      // Tarih inputu: bugünün tarihini minimum yap
      const today = new Date().toISOString().split('T')[0];
      const dateInput = document.querySelector('input[type="date"]');
      if (dateInput) {
        dateInput.min = today;
      }
    });
  </script>
</head>
<body>
  <div class="trip-form-container">
    <div class="trip-form-header">
      <h2>Yeni Seyahat İlanı</h2>
      <p>Yolculuk planınızı bize iletin, size uygun eşleşmeleri bulalım!</p>
      <?php if ($error): ?>
        <div class="trip-form-error"><?php echo $error; ?></div>
      <?php endif; ?>
    </div>
    
    <form method="POST">
      <div class="trip-form-group" style="animation-delay: 0.2s;">
        <label>Kalkış Yeri</label>
        <input type="text" id="from_city" name="from_city" required 
               pattern="[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,50}" 
               title="Sadece harf ve boşluk kullanın (2-50 karakter)" 
               placeholder="İstanbul">
      </div>

      <div class="trip-form-group" style="animation-delay: 0.4s;">
        <label>Varış Yeri</label>
        <input type="text" id="to_city" name="to_city" required 
               pattern="[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,50}" 
               title="Sadece harf ve boşluk kullanın (2-50 karakter)" 
               placeholder="Ankara">
      </div>

      <div class="trip-form-group" style="animation-delay: 0.6s;">
        <label>Yolculuk Tarihi</label>
        <input type="date" name="trip_date" required>
      </div>

      <div class="trip-form-group" style="animation-delay: 0.8s;">
        <label>Kalkış Saati</label>
        <input type="time" name="trip_time" required>
      </div>

      <div class="trip-form-group" style="animation-delay: 1.0s;">
        <label>Kişi Başı Ücret (TL)</label>
        <input type="number" step="0.01" name="price" required min="1" max="9999" placeholder="150.00">
      </div>

      <div class="trip-form-group" style="animation-delay: 1.2s;">
        <label>Ek Notlar</label>
        <textarea name="details" placeholder="Araç tipi, bagaj kuralları..."></textarea>
      </div>

      <div class="trip-form-group" style="animation-delay: 1.4s;">
        <button type="submit" class="trip-submit-btn">İlanı Yayınla</button>
      </div>
    </form>
  </div>

  <?php include 'footer.php'; ?>
</body>
</html>
