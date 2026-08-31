<?php
require 'config/db.php';
require 'includes/functions.php';

/**
 * The cart lives entirely in $_SESSION['cart'] as [book_id => quantity].
 * No database table needed for it — it's per-visitor and temporary,
 * which keeps checkout.php simple: read the session, write one order row.
 */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle POSTed actions: add, update, remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bookId = (int)($_POST['book_id'] ?? 0);

    if ($action === 'add') {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $_SESSION['cart'][$bookId] = ($_SESSION['cart'][$bookId] ?? 0) + $qty;
    } elseif ($action === 'update') {
        $qty = (int)($_POST['quantity'] ?? 1);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$bookId]);
        } else {
            $_SESSION['cart'][$bookId] = $qty;
        }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$bookId]);
    }

    header('Location: cart.php');
    exit;
}

// Load the actual book details for whatever is in the cart
$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $book) {
        $qty = $_SESSION['cart'][$book['id']];
        $subtotal = $qty * $book['price'];
        $total += $subtotal;
        $items[] = ['book' => $book, 'qty' => $qty, 'subtotal' => $subtotal];
    }
}

$pageTitle = 'Your Cart';
require 'includes/header.php';
?>

<h1 class="text-3xl font-serif font-bold mb-6">Your Cart</h1>

<?php if (empty($items)): ?>
  <p class="text-stone-500">Your cart is empty. <a href="index.php" class="text-amber-600 underline">Browse books</a>.</p>
<?php else: ?>
  <div class="bg-white rounded-lg shadow-sm divide-y">
    <?php foreach ($items as $item): ?>
      <div class="flex items-center gap-4 p-4">
        <div class="w-16 h-20 bg-stone-200 rounded overflow-hidden flex-shrink-0">
          <?php if ($item['book']['cover_image']): ?>
            <img src="uploads/<?= h($item['book']['cover_image']) ?>" class="w-full h-full object-cover">
          <?php endif; ?>
        </div>
        <div class="flex-1">
          <p class="font-semibold"><?= h($item['book']['title']) ?></p>
          <p class="text-sm text-stone-500"><?= money($item['book']['price']) ?> each</p>
        </div>
        <form method="POST" class="flex items-center gap-2">
          <input type="hidden" name="book_id" value="<?= $item['book']['id'] ?>">
          <input type="hidden" name="action" value="update">
          <input type="number" name="quantity" value="<?= $item['qty'] ?>" min="0"
                 class="w-16 border border-stone-300 rounded px-2 py-1"
                 onchange="this.form.submit()">
        </form>
        <p class="w-20 text-right font-semibold"><?= money($item['subtotal']) ?></p>
        <form method="POST">
          <input type="hidden" name="book_id" value="<?= $item['book']['id'] ?>">
          <input type="hidden" name="action" value="remove">
          <button class="text-red-500 hover:underline text-sm">Remove</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-6 flex justify-between items-center bg-white rounded-lg shadow-sm p-4">
    <span class="text-lg font-bold">Total: <?= money($total) ?></span>
    <a href="checkout.php" class="bg-stone-900 text-white px-6 py-2 rounded hover:bg-amber-600 transition">
      Proceed to Checkout
    </a>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
