<?php
require 'config/db.php';
require 'includes/functions.php';

$pageTitle = 'Categories';
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

require 'includes/header.php';
?>

<h1 class="text-3xl font-serif font-bold mb-6">Categories</h1>

<div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
  <?php foreach ($categories as $cat): ?>
    <a href="index.php?category=<?= $cat['id'] ?>"
       class="bg-white shadow-sm rounded-lg p-6 text-center font-medium hover:bg-amber-50 hover:text-amber-700 transition">
      <?= h($cat['name']) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php require 'includes/footer.php'; ?>
