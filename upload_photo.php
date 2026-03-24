<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if file was sent
    if (!isset($_FILES['photo'])) {
        die('ERROR: No file received.');
    }

    $file = $_FILES['photo'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('ERROR: Upload error code: ' . $file['error']);
    }

    // Check file type
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        die('ERROR: Invalid file type: ' . $file['type']);
    }

    // Check file size (2MB max)
    if ($file['size'] > 40 * 1024 * 1024) {
        die('ERROR: File too large: ' . $file['size'] . ' bytes');
    }

    // Create uploads folder
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            die('ERROR: Could not create uploads folder at: ' . $upload_dir);
        }
    }

    // Check folder is writable
    if (!is_writable($upload_dir)) {
        die('ERROR: Uploads folder is not writable: ' . $upload_dir);
    }

    // Delete old photo
    if (!empty($_SESSION['user']['photo'])) {
        $old = $upload_dir . $_SESSION['user']['photo'];
        if (file_exists($old)) unlink($old);
    }

    // Save new photo
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'student_' . $_SESSION['user']['id'] . '_' . time() . '.' . $ext;
    $dest     = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        die('ERROR: Could not move file to: ' . $dest);
    }

    // Update database
    try {
        $stmt = $pdo->prepare("UPDATE students SET photo = ? WHERE id = ?");
        $stmt->execute([$filename, $_SESSION['user']['id']]);

        // Refresh session
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $_SESSION['user'] = $stmt->fetch();

    } catch (Exception $e) {
        die('ERROR: Database error: ' . $e->getMessage());
    }

    header('Location: dashboard.php');
    exit;

} else {
    die('ERROR: Not a POST request.');
}