<?php
require '../config/db.php';
require '../includes/functions.php';
requireAdminLogin();

$id = (int)($_GET['id'] ?? 0);

// Optionally remove the uploaded cover file too, to avoid orphaned images
$stmt = $pdo->prepare('SELECT cover_image FROM books WHERE id = ?');
$stmt->execute([$id]);
$cover = $stmt->fetchColumn();

$stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
$stmt->execute([$id]);

if ($cover && file_exists("../uploads/$cover")) {
    unlink("../uploads/$cover");
}

header('Location: books_list.php');
exit;
