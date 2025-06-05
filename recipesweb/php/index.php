<?php
session_start();

// Αν υπάρχει συνδεδεμένος χρήστης, αποθηκεύουμε το όνομά του
$user = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : null;

// Έλεγχοι για εμφάνιση των popups
$popupSeen = isset($_SESSION['popup_shown']);
$showGoodbye = isset($_GET['loggedout']) && !$user;
$showLoginPopup = !$user && !$popupSeen && !$showGoodbye;

if ($showLoginPopup) {
    $_SESSION['popup_shown'] = true;
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Recipes Web - Αρχική</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">

  <?php if ($showLoginPopup): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => {
        const popup = document.getElementById('popup-login');
        if (popup) popup.style.display = 'flex';
      }, 5000);
    });
  </script>
  <?php endif; ?>

  <?php if ($showGoodbye): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const popup = document.getElementById('popup-goodbye');
      if (popup) popup.style.display = 'flex';
    });
  </script>
  <?php endif; ?>

  <script>
    function closePopup(id) {
      const el = document.getElementById(id);
      if (el) el.style.display = 'none';
    }

    function closeGoodbyeAndShowLogin() {
      closePopup('popup-goodbye');
      setTimeout(() => {
        const loginPopup = document.getElementById('popup-login');
        if (loginPopup) loginPopup.style.display = 'flex';
      }, 5000);
    }
  </script>
</head>
<body>

<?php include("navbar.php"); ?>

<?php if ($user): ?>
  <h3 class="welcome-message">Καλωσήρθες, <?php echo htmlspecialchars($user); ?>!</h3>
<?php endif; ?>

<section class="categories">
  <div class="row1">

    <div class="category1">
      <a href="recipe_by_category.php?category=Indian">
        <img class="cuisine" src="../media/categories/indian.png" alt="Indian Κουζίνα">
      </a>
      <p>Indian Κουζίνα</p>
    </div>

    <div class="category2">
      <a href="recipe_by_category.php?category=Italian">
        <img class="cuisine" src="../media/categories/italian.png" alt="Italian Κουζίνα">
      </a>
      <p>Italian Κουζίνα</p>
    </div>

    <div class="category3">
      <a href="recipe_by_category.php?category=Thai">
        <img class="cuisine" src="../media/categories/thai.png" alt="Thai Κουζίνα">
      </a>
      <p>Thai Κουζίνα</p>
    </div>

  </div>
</section>

<?php include("footer.php"); ?>

<!-- Popup για μη συνδεδεμένους χρήστες -->
<div id="popup-login" class="popup-container" style="display: none;">
  <div class="popup-box">
    <span class="popup-close" onclick="closePopup('popup-login')">✕</span>
    <h3>Καλώς ήρθατε! Επιλέξτε:</h3>
    <a href="login.php" class="popup-button">Σύνδεση</a>
    <a href="signup.php" class="popup-button">Εγγραφή</a>
    <a href="index.php" class="popup-button">Συνέχεια ως επισκέπτης</a>
  </div>
</div>

<?php if ($showGoodbye): ?>
<div id="popup-goodbye" class="popup-container" style="display: none;">
  <div class="popup-box">
    <span class="popup-close" onclick="closePopup('popup-goodbye')">✕</span>
    <h3>Ευχαριστούμε για την επίσκεψη!</h3>
    <a class="popup-button" onclick="closeGoodbyeAndShowLogin()">Κλείσιμο</a>
  </div>
</div>
<?php endif; ?>

</body>
</html>