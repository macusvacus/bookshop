<?php
require 'config/db.php';
require 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    http_response_code(404);
    die('Book not found.');
}

$pageTitle = $book['title'];
require 'includes/header.php';
?>

<div class="grid md:grid-cols-2 gap-10">
  <div class="aspect-[2/3] bg-stone-200 rounded-lg overflow-hidden max-w-sm">
    <?php if ($book['cover_image']): ?>
      <img src="uploads/<?= h($book['cover_image']) ?>" alt="<?= h($book['title']) ?>" class="w-full h-full object-cover">
    <?php else: ?>
      <div class="w-full h-full flex items-center justify-center text-stone-400">No cover</div>
    <?php endif; ?>
  </div>

  <div>
    <h1 class="text-3xl font-serif font-bold"><?= h($book['title']) ?></h1>
    <p class="text-stone-500 mt-1">by <?= h($book['author']) ?></p>
    <p class="text-2xl font-bold text-amber-600 mt-4"><?= money($book['price']) ?></p>

    <p class="mt-6 text-stone-700 leading-relaxed"><?= nl2br(h($book['description'])) ?></p>

    <p class="mt-4 text-sm <?= $book['stock'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
      <?= $book['stock'] > 0 ? $book['stock'] . ' in stock' : 'Out of stock' ?>
    </p>

    <?php if ($book['stock'] > 0): ?>
      <form method="POST" action="cart.php" class="mt-6 flex items-center gap-3">
        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
        <input type="hidden" name="action" value="add">
        <input type="number" name="quantity" value="1" min="1" max="<?= $book['stock'] ?>"
               class="w-20 border border-stone-300 rounded px-3 py-2">
        <button type="submit" class="bg-stone-900 text-white px-6 py-2 rounded hover:bg-amber-600 transition">
          Add to Cart
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
