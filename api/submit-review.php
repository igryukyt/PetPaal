<?php
/**
 * PetPal - Submit Review API
 * Handles review submission for logged-in users
 */

require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review.']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Please refresh and try again.']);
    exit;
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Get and validate input
$hospitalId = intval($_POST['hospital_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

$errors = [];

// Validate hospital
if ($hospitalId <= 0) {
    $errors[] = 'Please select a hospital.';
} else {
    $stmt = $conn->prepare("SELECT id FROM hospitals WHERE id = ?");
    $stmt->execute([$hospitalId]);
    if (!$stmt->fetch()) {
        $errors[] = 'Invalid hospital selected.';
    }
}

// Validate rating
if ($rating < 1 || $rating > 5) {
    $errors[] = 'Please provide a rating between 1 and 5.';
}

// Validate comment
if (strlen($comment) < 10) {
    $errors[] = 'Review must be at least 10 characters long.';
}

if (strlen($comment) > 1000) {
    $errors[] = 'Review cannot exceed 1000 characters.';
}

// Check if user already reviewed this hospital (optional - allow multiple reviews)
// Uncomment below to prevent duplicate reviews
/*
$stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND hospital_id = ?");
$stmt->execute([$userId, $hospitalId]);
if ($stmt->fetch()) {
    $errors[] = 'You have already reviewed this hospital.';
}
*/

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    // Insert review
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, hospital_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $hospitalId, $rating, $comment]);

    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully!'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
