<?php
session_start();
header('Content-Type: application/json');

// Αν ο χρήστης δεν είναι συνδεδεμένος, επιστροφή αποτυχίας
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'not_logged_in']);
    exit();
}

// Έλεγχος ότι υπάρχει έγκυρο ID συνταγής στο POST
if (!isset($_POST['recipe_id']) || !is_numeric($_POST['recipe_id'])) {
    echo json_encode(['success' => false, 'message' => 'invalid_request']);
    exit();
}

$recipe_id = intval($_POST['recipe_id']);
$user_id = $_SESSION['user_id'];

include("db_connection.php");
$mysqli = $conn;

// Έλεγχος αν ο χρήστης έχει ήδη κάνει like
$stmt = $mysqli->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$stmt->store_result();
$already_liked = $stmt->num_rows > 0;
$stmt->close();

// Αν υπάρχει ήδη like, το αφαιρούμε. Αλλιώς, προσθέτουμε νέο like
if ($already_liked) {
    $stmt = $mysqli->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
} else {
    $stmt = $mysqli->prepare("INSERT INTO likes (user_id, recipe_id) VALUES (?, ?)");
}
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$stmt->close();

// Μετράμε τα συνολικά likes της συνταγής
$stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM likes WHERE recipe_id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
$like_count = $result->fetch_assoc()['total'];
$stmt->close();
$mysqli->close();

// Δημιουργούμε το κατάλληλο μήνυμα εμφάνισης
if ($already_liked) {
    $message = $like_count === 0 ? "" : "Αρέσει σε " . $like_count . " χρήστες";
    $liked = false;
} else {
    $message = $like_count === 1 
        ? "Αρέσει σε εσάς" 
        : "Αρέσει σε εσάς και σε άλλους " . ($like_count - 1);
    $liked = true;
}

// Επιστρέφουμε την τελική απάντηση σε μορφή JSON
echo json_encode([
    'success' => true,
    'likes' => $like_count,
    'liked' => $liked,
    'message' => $message
]);