<?php
session_start();

// Ελέγχει αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    exit();
}

// Συμπερίληψη αρχείου σύνδεσης με τη βάση δεδομένων
include("db_connection.php"); 
$mysqli = $conn;

// Επιβεβαιώνει ότι έχουν σταλεί τα απαραίτητα δεδομένα για την καταχώριση σχολίου
if (isset($_POST['recipe_id']) && !empty(trim($_POST['comment']))) {
    $user_id = $_SESSION['user_id'];
    $recipe_id = intval($_POST['recipe_id']);
    $comment = trim($_POST['comment']);

    // Εκτελεί την εισαγωγή του σχολίου στον πίνακα της βάσης
    $stmt = $mysqli->prepare("INSERT INTO comments (user_id, recipe_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $recipe_id, $comment);
    $stmt->execute();
    $stmt->close();
}

// Κλείνει η σύνδεση με τη βάση και γίνεται ανακατεύθυνση πίσω στη σελίδα προέλευσης
$mysqli->close();
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>