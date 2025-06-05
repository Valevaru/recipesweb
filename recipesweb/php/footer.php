<!-- Footer -->

<footer>

  <!-- Δημοφιλείς Κατηγορίες -->
  <div class="popular">
    <p class="footer-heading">Οι κατηγορίες μας</p>
    <p><a href="recipe_by_category.php?category=Indian">Indian Κουζίνα</a></p>
    <p><a href="recipe_by_category.php?category=Italian">Italian Κουζίνα</a></p>
    <p><a href="recipe_by_category.php?category=Thai">Thai Κουζίνα</a></p>
  </div>

  <!-- Δραστηριότητα -->
  <div class="activity">
    <p class="footer-heading">Πράγματα να κάνεις</p>

    <!-- Ανέβασε Συνταγή -->
    <?php if (!isset($_SESSION['user_id'])): ?>
      <p><a href="#" onclick="openAuthPopup()">Ανέβασε Συνταγή</a></p>
    <?php else: ?>
      <p><a href="upload.php">Ανέβασε Συνταγή</a></p>
    <?php endif; ?>

    <!-- Οι Συνταγές μου -->
    <?php if (!isset($_SESSION['user_id'])): ?>
      <p><a href="#" onclick="openAuthPopup()">Οι Συνταγές μου</a></p>
    <?php else: ?>
      <p><a href="my_recipes.php">Οι Συνταγές μου</a></p>
    <?php endif; ?>

    <!-- Αγαπημένα -->
    <?php if (!isset($_SESSION['user_id'])): ?>
      <p><a href="#" onclick="openAuthPopup()">Αγαπημένα</a></p>
    <?php else: ?>
      <p><a href="favorite.php">Αγαπημένα</a></p>
    <?php endif; ?>
  </div>

  <!-- Social Media -->
  <div class="social-media">
    <p class="footer-heading">Social</p>
    <img id="social" src="../media/imgSocial.png" alt="Social media">
    <small class="copyright">&copy; 2025 RecipesWeb</small>
  </div>

</footer>