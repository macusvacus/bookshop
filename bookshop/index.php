<?php
require 'config/db.php';
require 'includes/functions.php';

$pageTitle = 'Shop';

// Optional category filter via ?category=3
$categoryId = $_GET['category'] ?? null;

if ($categoryId) {
    $stmt = $pdo->prepare('SELECT * FROM books WHERE category_id = ? ORDER BY created_at DESC');
    $stmt->execute([$categoryId]);
} else {
    $stmt = $pdo->query('SELECT * FROM books ORDER BY created_at DESC');
}
$books = $stmt->fetchAll();

require 'includes/header.php';
?>

<h1 class="text-3xl font-serif font-bold mb-6">Browse Books</h1>

<?php if (empty($books)): ?>
  <p class="text-stone-500">No books available yet. Check back soon.</p>
<?php else: ?>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
    <?php foreach ($books as $book): ?>
      <a href="book.php?id=<?= $book['id'] ?>" class="group bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
        <div class="aspect-[2/3] bg-stone-200">
          <?php if ($book['cover_image']): ?>
            <img src="uploads/<?= h($book['cover_image']) ?>" alt="<?= h($book['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition">
          <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-stone-400 text-sm">No cover</div>
          <?php endif; ?>
        </div>
        <div class="p-3">
          <h2 class="font-semibold text-sm leading-snug line-clamp-2"><?= h($book['title']) ?></h2>
          <p class="text-xs text-stone-500 mt-1"><?= h($book['author']) ?></p>
          <p class="text-amber-600 font-bold mt-2"><?= money($book['price']) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
