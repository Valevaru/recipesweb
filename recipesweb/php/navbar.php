<?php
// Έναρξη συνεδρίας αν δεν έχει ξεκινήσει ήδη
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ανάκτηση του μικρού ονόματος του χρήστη (αν υπάρχει)
$user = $_SESSION['first_name'] ?? null;

// Εύρεση της τρέχουσας σελίδας για επισήμανση ενεργού συνδέσμου
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Ενότητα πλοήγησης (navbar) -->
<section class="navbar">
  <div class="header">
    <h1>Κοιλιόδουλοι</h1>
  </div>

  <div>
    <ul class="menu-list">

      <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Εμφάνιση επιλογής για Σύνδεση/Εγγραφή αν δεν έχει γίνει σύνδεση -->
        <li class="menu-list-item">
          <a href="#" class="menu-link" onclick="openAuthPopup()">Σύνδεση ή Εγγραφή</a>
        </li>
      <?php endif; ?>

      <!-- Σύνδεσμος για Αρχική Σελίδα -->
      <li class="menu-list-item">
        <a class="menu-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="/recipesweb/php/index.php">Αρχική</a>
      </li>

      <!-- Σύνδεσμος για Ανέβασμα Συνταγής -->
      <li class="menu-list-item">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="/recipesweb/php/upload.php" class="menu-link">Ανέβασε Συνταγή</a>
        <?php else: ?>
          <a href="#" class="menu-link" onclick="openAuthPopup()">Ανέβασε Συνταγή</a>
        <?php endif; ?>
      </li>

      <!-- Σύνδεσμος για τις προσωπικές συνταγές του χρήστη -->
      <li class="menu-list-item">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="/recipesweb/php/my_recipes.php" class="menu-link">Συνταγές μου</a>
        <?php else: ?>
          <a href="#" class="menu-link" onclick="openAuthPopup()">Συνταγές μου</a>
        <?php endif; ?>
      </li>

      <!-- Σύνδεσμος για αγαπημένες συνταγές -->
      <li class="menu-list-item">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="/recipesweb/php/favorite.php" class="menu-link <?php echo ($current_page == 'favorite.php') ? 'active' : ''; ?>">Αγαπημένα</a>
        <?php else: ?>
          <a href="#" class="menu-link" onclick="openAuthPopup()">Αγαπημένα</a>
        <?php endif; ?>
      </li>

      <?php if ($user): ?>
        <!-- Σύνδεσμος για αποσύνδεση -->
        <li class="menu-list-item">
          <a class="menu-link" href="/recipesweb/php/logout.php">Αποσύνδεση</a>
        </li>
      <?php endif; ?>

    </ul>
  </div>
</section>

<!-- Ενότητα λογοτύπου και κεντρικής φράσης -->
<section class="logo">
  <img id="logo-img" src="/recipesweb/media/logo.png" alt="logo chef">
  <div class="phrase">
    <h2>Για όλα τα στομάχια</h2>
  </div>
</section>

<!-- Δευτερεύουσα φράση κάτω από το λογότυπο -->
<p id="subtitle">οι κουζίνες μας</p>

<!-- Popup για Σύνδεση ή Εγγραφή -->
<div id="authPopup" class="popup-container" style="display: none;">
  <div class="popup-box">
    <h3>Θέλετε να συνδεθείτε ή να εγγραφείτε;</h3>
    <a href="/recipesweb/php/login.php" class="popup-button">Σύνδεση</a>
    <a href="/recipesweb/php/signup.php" class="popup-button">Εγγραφή</a>
    <div class="popup-close" onclick="closeAuthPopup()">✕</div>
  </div>
</div>

<!-- JavaScript για το άνοιγμα και κλείσιμο του popup -->
<script>
function openAuthPopup() {
  document.getElementById('authPopup').style.display = 'flex';
}

function closeAuthPopup() {
  document.getElementById('authPopup').style.display = 'none';
}
</script>