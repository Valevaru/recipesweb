<?php
// Ξεκινά η συνεδρία
session_start();

// Καθαρίζουμε τις μεταβλητές της συνεδρίας
$_SESSION = [];
session_unset();
session_destroy();

// Διαγραφή του cookie της συνεδρίας (προληπτικά)
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Ανακατεύθυνση στην αρχική σελίδα με ένδειξη αποσύνδεσης
header("Location: index.php?loggedout=1");
exit();
?>