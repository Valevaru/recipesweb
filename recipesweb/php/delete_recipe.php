<?php
session_start();

// Έλεγχος πρόσβασης: μόνο για χρήστες που έχουν συνδεθεί
if (!isset($_SESSION['user_id'])) {
    exit();
}

// Επιβεβαίωση ότι υπάρχει ID και είναι αριθμός
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: my_recipes.php?error=Η συνταγή δεν βρέθηκε.");
    exit();
}

$recipe_id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];

// Σύνδεση με τη βάση δεδομένων
include("db_connection.php");
$mysqli = $conn;

// Έλεγχος ιδιοκτησίας συνταγής από τον χρήστη
$stmt = $mysqli->prepare("SELECT image_path FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Αν δεν ανήκει στον χρήστη ή δεν υπάρχει, δεν συνεχίζεται η διαγραφή
if ($result->num_rows !== 1) {
    header("Location: my_recipes.php?error=Δεν έχετε δικαίωμα διαγραφής.");
    exit();
}

$row = $result->fetch_assoc();
$image_path = $row['image_path'];
$stmt->close();

// Διαγραφή σχετικών εγγραφών από likes, favorites και σχόλια
$mysqli->query("DELETE FROM likes WHERE recipe_id = $recipe_id");
$mysqli->query("DELETE FROM favorites WHERE recipe_id = $recipe_id");
$mysqli->query("DELETE FROM comments WHERE recipe_id = $recipe_id");

// Διαγραφή της ίδιας της συνταγής
$stmt_del = $mysqli->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
$stmt_del->bind_param("ii", $recipe_id, $user_id);
$stmt_del->execute();
$stmt_del->close();

// Αφαίρεση της εικόνας από τον server, αν υπάρχει
$absolute_path = "../" . $image_path;
if (file_exists($absolute_path)) {
    unlink($absolute_path);
}

// Τελική ανακατεύθυνση με μήνυμα επιβεβαίωσης
header("Location: my_recipes.php?message=Η διαγραφή ολοκληρώθηκε επιτυχώς.");
exit();
?>