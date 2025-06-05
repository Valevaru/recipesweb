<?php
// Ξεκινά το session για να αναγνωρίσουμε τον χρήστη
session_start();

// Έλεγχος αν έχει οριστεί κατηγορία μέσω GET
if (!isset($_GET['category']) || empty(trim($_GET['category']))) {
    header("Location: index.php");
    exit();
}

// Καθαρισμός της παραμέτρου κατηγορίας
$category = urldecode(trim($_GET['category']));

// Σύνδεση με τη βάση δεδομένων
include("db_connection.php"); 
$mysqli = $conn;

// Έλεγχος αποτυχίας σύνδεσης
if ($mysqli->connect_error) {
    die("Δεν ήταν δυνατή η σύνδεση στη βάση.");
}

// Ερώτημα για τις συνταγές της συγκεκριμένης κατηγορίας
$stmt = $mysqli->prepare("
    SELECT r.id, r.title, r.description, r.image_path, r.created_at, u.firstname, u.lastname
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    WHERE r.category = ?
    ORDER BY r.created_at DESC
");

$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Κατηγορία: <?php echo htmlspecialchars($category); ?></title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="page-container">
  <h2 class="section-title">Συνταγές της κατηγορίας: <em><?php echo htmlspecialchars($category); ?></em></h2>

  <?php if ($result->num_rows === 0): ?>
    <p>Δεν βρέθηκαν συνταγές που να ανήκουν σε αυτή την κατηγορία.</p>
  <?php else: ?>
    <div class="recipe-grid">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="recipe-card">
          <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" alt="Φωτογραφία συνταγής" class="recipe-thumb">
          <h3><?php echo htmlspecialchars($row['title']); ?></h3>
          <small>Δημιουργός: <?php echo htmlspecialchars($row['firstname'] . " " . $row['lastname']); ?></small><br>
          <small>Ανέβηκε στις: <?php echo $row['created_at']; ?></small>
          <p><?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 100))) . "..."; ?></p>
          <a href="view_recipe.php?id=<?php echo $row['id']; ?>" class="btn-view">Δείτε τη συνταγή</a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php include("footer.php"); ?>

</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>