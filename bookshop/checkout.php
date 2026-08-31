<?php
require 'config/db.php';
require 'includes/functions.php';

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Recalculate total server-side (never trust a hidden form field for money)
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM books WHERE id IN ($placeholders)");
$stmt->execute($ids);
$books = $stmt->fetchAll();

$total = 0;
foreach ($books as $book) {
    $total += $book['price'] * $_SESSION['cart'][$book['id']];
}

$pageTitle = 'Checkout';
require 'includes/header.php';
?>

<h1 class="text-3xl font-serif font-bold mb-6">Checkout</h1>

<div class="grid md:grid-cols-2 gap-10">
  <form method="POST" action="save-order.php" class="space-y-4 bg-white rounded-lg shadow-sm p-6">
    <div>
      <label class="block text-sm font-medium mb-1">Full Name</label>
      <input type="text" name="customer_name" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Email</label>
      <input type="email" name="email" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Phone</label>
      <input type="text" name="phone" class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Delivery Address</label>
      <textarea name="address" rows="3" required class="w-full border border-stone-300 rounded px-3 py-2"></textarea>
    </div>
    <button type="submit" class="w-full bg-stone-900 text-white py-3 rounded hover:bg-amber-600 transition">
      Place Order — <?= money($total) ?>
    </button>
  </form>

  <div class="bg-white rounded-lg shadow-sm p-6 h-fit">
    <h2 class="font-semibold mb-3">Order Summary</h2>
    <?php foreach ($books as $book): ?>
      <div class="flex justify-between text-sm py-1">
        <span><?= h($book['title']) ?> × <?= $_SESSION['cart'][$book['id']] ?></span>
        <span><?= money($book['price'] * $_SESSION['cart'][$book['id']]) ?></span>
      </div>
    <?php endforeach; ?>
    <div class="border-t mt-3 pt-3 flex justify-between font-bold">
      <span>Total</span>
      <span><?= money($total) ?></span>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
