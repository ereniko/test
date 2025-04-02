<?php
// index.php
include 'db.php';
include 'header.php';
?>

<!-- Hero Section -->
<div class="hero">
  <div class="hero-content">
    <h2>Paylaşımlı Yolculuklar</h2>
    <p>Masrafları bölüş, yeni insanlarla tanış, konforlu bir yolculuk yap!</p>

    <!-- Arama Formu -->
    <form class="search-form" action="#" method="post">
      <!-- Kalkış yeri -->
      <div class="search-item" id="departureSection">
        <div class="search-icon">
          <svg fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.478 2 2 6.478 
                     2 12c0 5.522 4.478 10 
                     10 10s10-4.478 10-10
                     C22 6.478 17.522 2 
                     12 2zm0 16a6 6 0 
                     110-12 6 6 0 010 12z"/>
          </svg>
        </div>
        <input type="text" id="departureInput" autocomplete="off" placeholder="Kalkış Yeri">
        <ul class="autocomplete-list" id="departureList"></ul>
      </div>

      <div class="divider"></div>

      <!-- Varış yeri -->
      <div class="search-item" id="arrivalSection">
        <div class="search-icon">
          <svg fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.478 2 2 6.478 
                     2 12c0 5.522 4.478 10 
                     10 10s10-4.478 10-10
                     C22 6.478 17.522 2 
                     12 2zm0 16a6 6 0 
                     110-12 6 6 0 010 12z"/>
          </svg>
        </div>
        <input type="text" id="arrivalInput" autocomplete="off" placeholder="Varış Yeri">
        <ul class="autocomplete-list" id="arrivalList"></ul>
      </div>

      <div class="divider"></div>

      <!-- Tarih (pop-up takvim) -->
      <div class="search-item" id="dateSection">
        <div class="search-icon">
          <svg fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 4h-1V2h-2v2H8V2H6
                     v2H5c-1.11 0-2 .89-2
                     2v13c0 1.11.89 2 2
                     2h14c1.11 0 2-.89
                     2-2V6c0-1.11-.89-2
                     -2-2zM5 19V8h14v11H5z"/>
          </svg>
        </div>
        <div class="search-label" id="dateLabel">Tarih</div>
      </div>

      <div class="divider"></div>

      <!-- Yolcu (pop-up) -->
      <div class="search-item" id="seatsSection">
        <div class="search-icon">
          <svg fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M12 12c2.21 
                     0 4-1.79 4-4s-1.79-4
                     -4-4-4 1.79-4 4
                     1.79 4 4 4zm6
                     8v-1c0-2.21-3.58-4
                     -6-4s-6 1.79-6 
                     4v1h12z"/>
          </svg>
        </div>
        <div class="search-label" id="seatsLabel">1 Yolcu</div>
      </div>

      <!-- Ara Butonu -->
      <button type="submit" class="search-btn">Ara</button>
    </form>
  </div>
</div>


    </div>
  </div>
</div>

<?php
include 'footer.php';
?>
