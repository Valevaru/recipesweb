<?php
session_start();
header('Content-Type: application/json');

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Ο χρήστης δεν είναι συνδεδεμένος.'
    ]);
    exit();
}

// Έλεγχος εγκυρότητας ID συνταγής
if (!isset($_POST['recipe_id']) || !is_numeric($_POST['recipe_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Μη έγκυρο αίτημα.'
    ]);
    exit();
}

$recipe_id = intval($_POST['recipe_id']);
$user_id = $_SESSION['user_id'];

include("db_connection.php");
$mysqli = $conn;

// Έλεγχος αν υπάρχει ήδη καταχώρηση στα αγαπημένα
$stmt = $mysqli->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Δεν υπάρχει – το προσθέτουμε
    $stmt->close();
    $stmt_insert = $mysqli->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $user_id, $recipe_id);
    $stmt_insert->execute();
    $stmt_insert->close();
    echo json_encode([
        'success' => true,
        'message' => 'Η συνταγή προστέθηκε στα αγαπημένα.'
    ]);
} else {
    // Υπάρχει – το αφαιρούμε
    $stmt->close();
    $stmt_delete = $mysqli->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $stmt_delete->bind_param("ii", $user_id, $recipe_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    echo json_encode([
        'success' => true,
        'message' => 'Η συνταγή αφαιρέθηκε από τα αγαπημένα.'
    ]);
}

$mysqli->close();