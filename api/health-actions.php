<?php
/**
 * PetPal - Health Actions API
 * Handles health record creation and deletion
 */

require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']);
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
$action = $_POST['action'] ?? 'add';

if ($action === 'delete') {
    // Delete record
    $recordId = intval($_POST['record_id'] ?? 0);

    if ($recordId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid record.']);
        exit;
    }

    // Verify ownership
    $stmt = $conn->prepare("SELECT id FROM health_records WHERE id = ? AND user_id = ?");
    $stmt->execute([$recordId, $userId]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    // Delete
    $stmt = $conn->prepare("DELETE FROM health_records WHERE id = ?");
    $stmt->execute([$recordId]);

    echo json_encode(['success' => true, 'message' => 'Record deleted successfully.']);
    exit;
}

// Add new record
$petName = trim($_POST['pet_name'] ?? '');
$checkupDate = $_POST['checkup_date'] ?? '';
$vetName = trim($_POST['vet_name'] ?? '');
$diagnosis = trim($_POST['diagnosis'] ?? '');
$treatment = trim($_POST['treatment'] ?? '');
$nextAppointment = $_POST['next_appointment'] ?? null;
$notes = trim($_POST['notes'] ?? '');

// Validation
$errors = [];

if (empty($petName)) {
    $errors[] = 'Pet name is required.';
}

if (strlen($petName) > 100) {
    $errors[] = 'Pet name is too long.';
}

if (empty($checkupDate)) {
    $errors[] = 'Checkup date is required.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkupDate)) {
    $errors[] = 'Invalid date format.';
} elseif (strtotime($checkupDate) > strtotime('today')) {
    $errors[] = 'Checkup date cannot be in the future.';
}

if (!empty($nextAppointment)) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $nextAppointment)) {
        $errors[] = 'Invalid next appointment date format.';
    }
}

if (strlen($vetName) > 100) {
    $errors[] = 'Veterinarian name is too long.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO health_records 
        (user_id, pet_name, checkup_date, vet_name, diagnosis, treatment, next_appointment, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $petName,
        $checkupDate,
        $vetName ?: null,
        $diagnosis ?: null,
        $treatment ?: null,
        $nextAppointment ?: null,
        $notes ?: null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Health record saved successfully!',
        'record_id' => $conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
