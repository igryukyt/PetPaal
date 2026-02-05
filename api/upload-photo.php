<?php
/**
 * PetPal - Upload Photo API
 * Handles pet photo uploads
 */

require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to upload photos.']);
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

// Validate input
$petName = trim($_POST['pet_name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($petName)) {
    echo json_encode(['success' => false, 'message' => 'Please enter your pet\'s name.']);
    exit;
}

if (strlen($petName) > 100) {
    echo json_encode(['success' => false, 'message' => 'Pet name is too long.']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File is too large.',
        UPLOAD_ERR_FORM_SIZE => 'File is too large.',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file.',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by extension.'
    ];

    $errorCode = $_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $errorMessages[$errorCode] ?? 'File upload failed.';

    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$file = $_FILES['photo'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload JPG, PNG, GIF, or WebP.']);
    exit;
}

// Validate file size (5MB max)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB.']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../uploads/pets/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('pet_') . '_' . time() . '.' . strtolower($extension);
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file. Please try again.']);
    exit;
}

// Create URL for the uploaded file
$photoUrl = SITE_URL . '/uploads/pets/' . $filename;

try {
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO pet_photos (user_id, pet_name, photo_url, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $petName, $photoUrl, $description]);

    echo json_encode([
        'success' => true,
        'message' => 'Photo uploaded successfully!',
        'photo_url' => $photoUrl
    ]);

} catch (PDOException $e) {
    // Delete uploaded file if database insert fails
    unlink($filepath);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
