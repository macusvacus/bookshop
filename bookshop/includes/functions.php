<?php
/**
 * Small reusable helpers. Included by config/db.php's callers, or directly.
 */

// Escape output to prevent XSS when printing user-influenced data (titles, etc.)
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Format a price consistently everywhere: 12.5 -> "$12.50"
function money($amount) {
    return '$' . number_format((float)$amount, 2);
}

// True if an admin is currently logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Call at the top of any admin page that requires login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Total number of items currently in the cart (for the header badge)
function cartCount() {
    if (empty($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

// Handles a book cover upload, returns the saved filename or null.
// Centralized here so both add_book.php and edit_book.php reuse it.
function handleCoverUpload($fileInputName, $uploadDir) {
    if (empty($_FILES[$fileInputName]['name'])) {
        return null; // no file selected
    }

    $file = $_FILES[$fileInputName];
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        throw new Exception('Only JPG, PNG, or WEBP images are allowed.');
    }
    if ($file['size'] > 3 * 1024 * 1024) { // 3MB limit
        throw new Exception('Image must be under 3MB.');
    }

    // Unique filename so two books named "cover.jpg" never collide
    $filename = uniqid('book_', true) . '.' . $ext;
    $destination = rtrim($uploadDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to save uploaded image.');
    }

    return $filename;
}
