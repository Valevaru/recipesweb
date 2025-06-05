<?php
// Στοιχεία σύνδεσης με τη βάση δεδομένων
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "recipesweb";

// Δημιουργία σύνδεσης με τη MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος επιτυχούς σύνδεσης
if ($conn->connect_error) {
    die("Αποτυχία σύνδεσης με τη βάση: " . htmlspecialchars($conn->connect_error));
}
?>