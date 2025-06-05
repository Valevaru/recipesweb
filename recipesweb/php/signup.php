<?php
// Ενεργοποίηση πλήρους αναφοράς σφαλμάτων για debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Εκκίνηση συνεδρίας
session_start();

// Ενσωμάτωση αρχείου σύνδεσης με βάση δεδομένων
include("db_connection.php");
$mysqli = $conn;

// Έλεγχος σύνδεσης
if ($mysqli->connect_error) {
    die("Αποτυχία σύνδεσης με τη βάση: " . $mysqli->connect_error);
}

// Αρχικές μεταβλητές
$firstname = '';
$lastname = '';
$email = '';
$success = false;
$error_message = '';

// Αν γίνει υποβολή φόρμας
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Μη έγκυρη διεύθυνση email.";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password_raw)) {
        $error_message = "Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες, γράμματα και αριθμούς.";
    } elseif ($password_raw !== $password_confirm) {
        $error_message = "Οι κωδικοί δεν ταιριάζουν.";
    } else {
        // Έλεγχος αν υπάρχει χρήστης με ίδιο email
        $stmt_check = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_message = "Υπάρχει ήδη λογαριασμός με αυτό το email.";
        }
        $stmt_check->close();

        // Εισαγωγή αν δεν υπάρχει ήδη
        if (empty($error_message)) {
            $hashed_password = password_hash($password_raw, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare("INSERT INTO users (firstname, lastname, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $mysqli->insert_id;
                $_SESSION['first_name'] = $firstname;
                $_SESSION['last_name'] = $lastname;
                $_SESSION['user'] = $firstname . " " . $lastname;
                $success = true;
            } else {
                $error_message = "Σφάλμα κατά την εγγραφή. Προσπαθήστε ξανά.";
            }

            $stmt->close();
        }
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Εγγραφή Χρήστη</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="form-container">
  <?php if ($success): ?>
    <h2 class="success-message">Η εγγραφή ολοκληρώθηκε με επιτυχία!</h2>
    <form method="post" action="index.php" class="continue-form">
      <input type="submit" value="Μετάβαση στην Αρχική">
    </form>
  <?php else: ?>
    <h2 class="form-title">Δημιουργία Λογαριασμού</h2>

    <?php if (!empty($error_message)): ?>
      <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="POST" action="signup.php">
      <label>Όνομα:</label>
      <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required pattern="[A-Za-zΑ-Ωα-ωΆ-Ώά-ώ\s]+" title="Ελληνικά ή λατινικά γράμματα">
      
      <label>Επώνυμο:</label>
      <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required pattern="[A-Za-zΑ-Ωα-ωΆ-Ώά-ώ\s]+" title="Ελληνικά ή λατινικά γράμματα">
      
      <label>Email:</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      
      <label>Κωδικός:</label>
      <input type="password" name="password" required minlength="6" pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$" title="Τουλάχιστον 6 χαρακτήρες, γράμματα και αριθμοί">
      
      <label>Επιβεβαίωση Κωδικού:</label>
      <input type="password" name="password_confirm" required minlength="6" pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$">
      
      <input type="submit" value="Εγγραφή">
    </form>
  <?php endif; ?>
</div>

<?php include("footer.php"); ?>

</body>
</html>