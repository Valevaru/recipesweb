<?php
// Ξεκινά η συνεδρία για τον χρήστη
session_start();

// Σύνδεση με τη βάση δεδομένων
include("db_connection.php"); 
$mysqli = $conn;

// Έλεγχος σύνδεσης
if ($mysqli->connect_error) {
    die("Πρόβλημα με τη σύνδεση στη βάση.");
}

$error = '';

// Επεξεργασία της φόρμας όταν γίνει υποβολή
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Λήψη των στοιχείων από τη φόρμα
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Έλεγχος αν υπάρχει χρήστης με αυτό το email
    $stmt = $mysqli->prepare("SELECT id, firstname, lastname, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // Λήψη στοιχείων χρήστη
        $stmt->bind_result($id, $firstname, $lastname, $hashed_password);
        $stmt->fetch();

        // Έλεγχος αν ο κωδικός είναι σωστός
        if (password_verify($password, $hashed_password)) {
            // Αποθήκευση στοιχείων στο session
            $_SESSION['user_id'] = $id;
            $_SESSION['first_name'] = $firstname;
            $_SESSION['last_name'] = $lastname;
            $_SESSION['user'] = $firstname . ' ' . $lastname;

            // Ανακατεύθυνση στην αρχική σελίδα
            header("Location: index.php");
            exit();
        } else {
            $error = "Λάθος κωδικός.";
        }
    } else {
        $error = "Δεν βρέθηκε λογαριασμός με αυτό το email.";
    }

    $stmt->close();
}

// Κλείσιμο σύνδεσης με τη βάση
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Σύνδεση</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .form-container {
        max-width: 400px;
        margin: 50px auto;
        padding: 30px;
        background-color: #1e1e2f;
        color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.4);
        font-family: 'Segoe UI', sans-serif;
    }

    .form-container h2 {
        text-align: center;
        margin-bottom: 25px;
        color: hotpink;
        font-size: 24px;
    }

    .form-container label {
        display: block;
        margin: 10px 0 5px;
        font-size: 15px;
    }

    .form-container input[type="email"],
    .form-container input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 12px;
        border: none;
        background-color: #2b2b3c;
        color: #fff;
        font-size: 14px;
    }

    .form-container input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: hotpink;
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .form-container input[type="submit"]:hover {
        background-color: #ff69b4;
    }

    .error {
        color: #ff7070;
        background-color: #2a1a1a;
        padding: 10px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
    }
    </style>
</head>
<body>

<?php include("navbar.php"); ?>

<div class="form-container">
    <h2>Σύνδεση Χρήστη</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Κωδικός:</label>
        <input type="password" name="password" required>

        <input type="submit" value="Σύνδεση">
    </form>
</div>

<?php include("footer.php"); ?>

</body>
</html>