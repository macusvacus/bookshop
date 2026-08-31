<?php
require '../config/db.php';
require '../includes/functions.php';
requireAdminLogin();

$pageTitle = 'Manage Books';

$books = $pdo->query('SELECT b.*, c.name AS category_name
                       FROM books b LEFT JOIN categories c ON b.category_id = c.id
                       ORDER BY b.created_at DESC')->fetchAll();

require 'includes/admin_header.php';
?>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-stone-100 text-left">
      <tr>
        <th class="p-3">Cover</th>
        <th class="p-3">Title</th>
        <th class="p-3">Author</th>
        <th class="p-3">Category</th>
        <th class="p-3">Price</th>
        <th class="p-3">Stock</th>
        <th class="p-3">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      <?php foreach ($books as $book): ?>
        <tr>
          <td class="p-3">
            <div class="w-10 h-14 bg-stone-200 rounded overflow-hidden">
              <?php if ($book['cover_image']): ?>
                <img src="../uploads/<?= h($book['cover_image']) ?>" class="w-full h-full object-cover">
              <?php endif; ?>
            </div>
          </td>
          <td class="p-3 font-medium"><?= h($book['title']) ?></td>
          <td class="p-3"><?= h($book['author']) ?></td>
          <td class="p-3"><?= h($book['category_name'] ?? '—') ?></td>
          <td class="p-3"><?= money($book['price']) ?></td>
          <td class="p-3 <?= $book['stock'] <= 3 ? 'text-red-600 font-semibold' : '' ?>"><?= $book['stock'] ?></td>
          <td class="p-3 space-x-2">
            <a href="edit_book.php?id=<?= $book['id'] ?>" class="text-amber-600 hover:underline">Edit</a>
            <a href="delete_book.php?id=<?= $book['id'] ?>"
               onclick="return confirm('Delete this book permanently?');"
               class="text-red-500 hover:underline">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($books)): ?>
        <tr><td colspan="7" class="p-6 text-center text-stone-400">No books yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require 'includes/admin_footer.php'; ?>
