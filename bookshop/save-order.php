<?php
require 'config/db.php';
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['customer_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($name === '' || $email === '' || $address === '') {
    die('Please fill in all required fields. <a href="checkout.php">Go back</a>');
}

// Fetch cart books again server-side — never trust totals sent from the browser
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM books WHERE id IN ($placeholders)");
$stmt->execute($ids);
$books = $stmt->fetchAll();

$total = 0;
foreach ($books as $book) {
    $total += $book['price'] * $_SESSION['cart'][$book['id']];
}

// Wrap the order + its line items in a transaction: either both succeed,
// or neither does — we never want an order with no items.
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO orders (customer_name, email, phone, address, total) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $phone, $address, $total]);
    $orderId = $pdo->lastInsertId();

    $itemStmt = $pdo->prepare(
        'INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)'
    );
    $stockStmt = $pdo->prepare(
        'UPDATE books SET stock = stock - ? WHERE id = ?'
    );

    foreach ($books as $book) {
        $qty = $_SESSION['cart'][$book['id']];
        $itemStmt->execute([$orderId, $book['id'], $qty, $book['price']]);
        $stockStmt->execute([$qty, $book['id']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die('Something went wrong placing your order. Please try again.');
}

// Order saved — empty the cart
unset($_SESSION['cart']);

$pageTitle = 'Order Confirmed';
require 'includes/header.php';
?>

<div class="max-w-lg mx-auto text-center bg-white rounded-lg shadow-sm p-10">
  <h1 class="text-2xl font-serif font-bold text-green-600">Order Placed! 🎉</h1>
  <p class="mt-3 text-stone-600">
    Thanks, <?= h($name) ?>. Your order #<?= $orderId ?> for <?= money($total) ?> has been received.
    A confirmation will be sent to <?= h($email) ?>.
  </p>
  <a href="index.php" class="inline-block mt-6 bg-stone-900 text-white px-6 py-2 rounded hover:bg-amber-600 transition">
    Continue Shopping
  </a>
</div>

<?php require 'includes/footer.php'; ?>
