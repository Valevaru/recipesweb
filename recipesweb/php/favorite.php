<?php
session_start();

// Έλεγχος αν ο χρήστης έχει κάνει login
if (!isset($_SESSION['user_id'])) {
    exit(); // Απόρριψη ανώνυμης πρόσβασης
}

include("db_connection.php"); 
$mysqli = $conn;

$user_id = $_SESSION['user_id'];

// Ανάκτηση των αγαπημένων συνταγών του χρήστη
$query = "
SELECT r.id, r.title, r.description, r.image_path, r.created_at, u.firstname, u.lastname
FROM favorites f
JOIN recipes r ON f.recipe_id = r.id
JOIN users u ON r.user_id = u.id
WHERE f.user_id = ?
ORDER BY f.id DESC
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Οι αγαπημένες μου</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="page-container">
  <h2 class="section-title">Οι αγαπημένες μου συνταγές</h2>

  <?php if ($result->num_rows === 0): ?>
    <!-- Ενημέρωση σε περίπτωση που δεν υπάρχουν αποθηκευμένες συνταγές -->
    <div class="empty-cart-message">
      Δεν έχετε προσθέσει ακόμη κάποια συνταγή στα αγαπημένα σας.
    </div>
  <?php else: ?>
    <!-- Εμφάνιση συνταγών σε μορφή πλέγματος -->
    <div class="recipe-grid">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="recipe-card" data-id="<?php echo $row['id']; ?>">
          <img src="/ChefWeb/<?php echo htmlspecialchars($row['image_path']); ?>" alt="Εικόνα" class="recipe-thumb">
          <h3><?php echo htmlspecialchars($row['title']); ?></h3>
          <small>Από: <?php echo htmlspecialchars($row['firstname'] . " " . $row['lastname']); ?></small><br>
          <small>Ημερομηνία καταχώρησης: <?php echo $row['created_at']; ?></small>
          <p><?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 100))) . "..."; ?></p>
          <a href="view_recipe.php?id=<?php echo $row['id']; ?>" class="btn-view">Δες συνταγή</a>
          <button class="remove-btn saved" data-id="<?php echo $row['id']; ?>">🗑️ Αφαίρεση</button>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php include("footer.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const removeButtons = document.querySelectorAll(".remove-btn");

  removeButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      const recipeId = this.dataset.id;
      const recipeCard = this.closest(".recipe-card");

      fetch("save_favorite_ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `recipe_id=${recipeId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && recipeCard) {
          recipeCard.remove();
        } else {
          alert("Δεν ήταν δυνατή η αφαίρεση.");
        }
      });
    });
  });
});
</script>
</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>