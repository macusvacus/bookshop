<?php
require '../config/db.php';
require '../includes/functions.php';

$pageTitle = 'Dashboard';

$totalBooks = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders")->fetchColumn();
$lowStock = $pdo->query('SELECT COUNT(*) FROM books WHERE stock <= 3')->fetchColumn();

require 'includes/admin_header.php';
?>

<div class="grid grid-cols-4 gap-4">
  <div class="bg-white rounded-lg shadow-sm p-5">
    <p class="text-stone-500 text-sm">Total Books</p>
    <p class="text-2xl font-bold"><?= $totalBooks ?></p>
  </div>
  <div class="bg-white rounded-lg shadow-sm p-5">
    <p class="text-stone-500 text-sm">Total Orders</p>
    <p class="text-2xl font-bold"><?= $totalOrders ?></p>
  </div>
  <div class="bg-white rounded-lg shadow-sm p-5">
    <p class="text-stone-500 text-sm">Revenue</p>
    <p class="text-2xl font-bold"><?= money($revenue) ?></p>
  </div>
  <div class="bg-white rounded-lg shadow-sm p-5">
    <p class="text-stone-500 text-sm">Low Stock</p>
    <p class="text-2xl font-bold <?= $lowStock > 0 ? 'text-red-600' : '' ?>"><?= $lowStock ?></p>
  </div>
</div>

<div class="mt-8">
  <a href="add_book.php" class="inline-block bg-stone-900 text-white px-5 py-2.5 rounded hover:bg-amber-600 transition">
    + Add New Book
  </a>
</div>

<?php require 'includes/admin_footer.php'; ?>
