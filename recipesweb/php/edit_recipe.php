<?php
session_start();

// Έλεγχος σύνδεσης χρήστη
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Πρέπει να είστε συνδεδεμένος.");
    exit();
}

// Έλεγχος εγκυρότητας ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_recipes.php");
    exit();
}

$recipe_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

include("db_connection.php");
$mysqli = $conn;

$stmt = $mysqli->prepare("SELECT title, description, image_path, category FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: my_recipes.php?error=Δεν επιτρέπεται η επεξεργασία.");
    exit();
}

$recipe = $result->fetch_assoc();
$error = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_title = trim($_POST['title']);
    $new_desc = trim($_POST['description']);
    $new_category = $_POST['category'];
    $new_image_path = $recipe['image_path'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_name = basename($_FILES['image']['name']);
        $upload_path = "../uploads/" . uniqid() . "_" . $img_name;

        if (move_uploaded_file($img_tmp, $upload_path)) {
            if (file_exists($recipe['image_path'])) {
                unlink($recipe['image_path']);
            }
            $new_image_path = $upload_path;
        } else {
            $error = "Αποτυχία αποθήκευσης της νέας εικόνας.";
        }
    }

    if (empty($error)) {
        $stmt_update = $mysqli->prepare("UPDATE recipes SET title = ?, description = ?, category = ?, image_path = ? WHERE id = ? AND user_id = ?");
        $stmt_update->bind_param("ssssii", $new_title, $new_desc, $new_category, $new_image_path, $recipe_id, $user_id);

        if ($stmt_update->execute()) {
            $success = true;

            $recipe['title'] = $new_title;
            $recipe['description'] = $new_desc;
            $recipe['category'] = $new_category;
            $recipe['image_path'] = $new_image_path;

            header("Location: my_recipes.php?success=Η συνταγή ενημερώθηκε.");
            exit();
        } else {
            $error = "Σφάλμα κατά την ενημέρωση: " . $stmt_update->error;
        }

        $stmt_update->close();
    }
}

$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Επεξεργασία Συνταγής</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="form-container">
  <h2 class="form-title">Επεξεργασία Συνταγής</h2>

  <?php if ($success): ?>
    <p class="success-message">Η συνταγή ενημερώθηκε με επιτυχία!</p>
  <?php elseif (!empty($error)): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data">
    <label>Τίτλος:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>

    <label>Περιγραφή:</label>
    <textarea name="description" rows="5" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>

    <label>Κατηγορία:</label>
    <select name="category" required>
      <option value="Indian" <?php if ($recipe['category'] === "Indian") echo 'selected'; ?>>Indian</option>
      <option value="Italian" <?php if ($recipe['category'] === "Italian") echo 'selected'; ?>>Italian</option>
      <option value="Thai" <?php if ($recipe['category'] === "Thai") echo 'selected'; ?>>Thai</option>
    </select>

    <label>Τρέχουσα Εικόνα:</label>
    <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="Τρέχουσα Εικόνα" class="recipe-thumb">

    <label>Αλλαγή Εικόνας (προαιρετικό):</label>
    <input type="file" name="image" accept="image/*">

    <input type="submit" value="Αποθήκευση Αλλαγών">
  </form>
</div>

<?php include("footer.php"); ?>

</body>
</html>