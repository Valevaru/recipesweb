<?php
session_start();
if (!isset($_SESSION['user_id'])) exit();
include("db_connection.php");
$mysqli = $conn;

$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT id, title, description, image_path, created_at FROM recipes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Οι Δημιουργίες Μου</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .btn-remove {
      background-color: #ff4444;
      color: white;
      font-size: 0.95rem;
      border: none;
      padding: 7px 12px;
      border-radius: 6px;
      margin-top: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn-remove:hover {
      background-color: #cc0000;
    }
    .flash {
      background-color: #e0ffe0;
      color: #006600;
      padding: 10px;
      border-radius: 5px;
      margin: 10px auto;
      text-align: center;
      width: fit-content;
    }
  </style>
</head>
<body>

<?php if (isset($_GET['success'])): ?>
  <div class="flash">
    ✅ <?php echo htmlspecialchars($_GET['success']); ?>
  </div>
  <script>
    setTimeout(() => {
      document.querySelector('.flash')?.remove();
    }, 4000);
  </script>
<?php endif; ?>

<?php include("navbar.php"); ?>

<div class="page-container">
  <h2 class="section-title">Οι Δημιουργίες Μου</h2>

  <?php if ($result->num_rows === 0): ?>
    <div class="empty-cart-message">
      Δεν έχετε προσθέσει ακόμη καμία συνταγή.
    </div>
  <?php else: ?>
    <div class="recipe-grid">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="recipe-card">
          <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" alt="Εικόνα" class="recipe-thumb">
          <h3><?php echo htmlspecialchars($row['title']); ?></h3>
          <small>Δημιουργήθηκε: <?php echo $row['created_at']; ?></small>
          <p><?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 100))) . "..."; ?></p>

          <a href="view_recipe.php?id=<?php echo $row['id']; ?>" class="btn-view">Προβολή</a>
          <a href="edit_recipe.php?id=<?php echo $row['id']; ?>" class="btn-edit">Επεξεργασία</a>

          <form method="POST" action="delete_recipe.php" class="delete-form">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <button type="button" class="btn-remove" onclick="confirmDelete(this.form)">🗑 Διαγραφή Συνταγής</button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php include("footer.php"); ?>

<script>
function confirmDelete(form) {
  Swal.fire({
    title: 'Θέλετε σίγουρα να συνεχίσετε;',
    text: "Αυτή η ενέργεια δεν μπορεί να αναιρεθεί.",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#888',
    confirmButtonText: 'Διαγραφή',
    cancelButtonText: 'Ακύρωση'
  }).then((result) => {
    if (result.isConfirmed) form.submit();
  });
}
</script>

</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>