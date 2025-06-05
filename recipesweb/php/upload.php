<?php
session_start();

// Αν ο χρήστης δεν είναι συνδεδεμένος, μεταφορά στη σελίδα εγγραφής με σχετικό μήνυμα
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php?message=Χρειάζεται να έχετε λογαριασμό για να ανεβάσετε συνταγή.");
    exit();
}

// Σύνδεση με τη βάση
include("db_connection.php"); 
$mysqli = $conn;

if ($mysqli->connect_error) {
    die("Αποτυχία σύνδεσης με τη βάση: " . $mysqli->connect_error);
}

// Αρχικές μεταβλητές για εμφάνιση κατάστασης
$success = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];

    // Έλεγχος αν ανέβηκε εικόνα χωρίς σφάλμα
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_folder = "../media/uploads/";
        $img_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_path_fs = $upload_folder . $img_name;
        $img_path_db = "media/uploads/" . $img_name;

        // Προσπάθεια αποθήκευσης της εικόνας στον φάκελο
        if (move_uploaded_file($img_tmp, $img_path_fs)) {
            $stmt = $mysqli->prepare("INSERT INTO recipes (user_id, title, description, image_path, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $title, $description, $img_path_db, $category);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Δεν ήταν δυνατή η αποθήκευση της συνταγής.";
            }

            $stmt->close();
        } else {
            $error = "Η αποθήκευση της εικόνας απέτυχε.";
        }
    } else {
        $error = "Δεν επιλέξατε εικόνα ή υπήρξε πρόβλημα κατά την αποστολή.";
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ανάρτηση Συνταγής</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="form-container">
    <h2 class="form-title">Καταχώρηση Νέας Συνταγής</h2>

    <?php if ($success): ?>
        <p class="success-message">Η συνταγή ανέβηκε επιτυχώς!</p>
    <?php elseif (!empty($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <label>Συνταγή:</label>
        <input type="text" name="title" required>

        <label>Οδηγίες:</label>
        <textarea name="description" rows="5" required></textarea>

        <label>Κατηγορίες:</label>
        <select name="category" required>
            <option value="Indian">Indian</option>
            <option value="Italian">Italian</option>
            <option value="Thai">Thai</option>
        </select>

        <label>Ανέβασε:</label>
        <input type="file" name="image" accept="image/*" required>

        <input type="submit" value="Αποθήκευση">
    </form>
</div>

<?php if ($success): ?>
  <div class="flash-message">✔ Η συνταγή προστέθηκε!</div>
  <script>
    setTimeout(() => {
      const el = document.querySelector('.flash-message');
      if (el) el.style.display = 'none';
    }, 4000);
  </script>
<?php endif; ?>

<?php include("footer.php"); ?>
</body>
</html>