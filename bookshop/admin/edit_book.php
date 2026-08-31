<?php
require '../config/db.php';
require '../includes/functions.php';
requireAdminLogin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    die('Book not found. <a href="books_list.php">Back to list</a>');
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$pageTitle = 'Edit Book';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;

    if ($title === '' || $author === '' || $price <= 0) {
        $error = 'Title, author, and a valid price are required.';
    } else {
        try {
            // Only replace the cover image if a new one was uploaded;
            // otherwise keep the existing filename.
            $newCover = handleCoverUpload('cover_image', '../uploads');
            $coverFilename = $newCover ?? $book['cover_image'];

            $stmt = $pdo->prepare(
                'UPDATE books SET title=?, author=?, description=?, price=?, stock=?, category_id=?, cover_image=?
                 WHERE id = ?'
            );
            $stmt->execute([$title, $author, $description, $price, $stock, $categoryId, $coverFilename, $id]);

            header('Location: books_list.php');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    // Keep the (possibly invalid) submitted values in the form on error
    $book = array_merge($book, compact('title', 'author', 'description', 'price', 'stock'));
}

require 'includes/admin_header.php';
?>

<?php if ($error): ?>
  <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-4 text-sm"><?= h($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6 max-w-2xl space-y-4">
  <div>
    <label class="block text-sm font-medium mb-1">Title</label>
    <input type="text" name="title" value="<?= h($book['title']) ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Author</label>
    <input type="text" name="author" value="<?= h($book['author']) ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Description</label>
    <textarea name="description" rows="5" class="w-full border border-stone-300 rounded px-3 py-2"><?= h($book['description']) ?></textarea>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Price ($)</label>
      <input type="number" step="0.01" min="0.01" name="price" value="<?= h($book['price']) ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Stock Quantity</label>
      <input type="number" min="0" name="stock" value="<?= h($book['stock']) ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Category</label>
    <select name="category_id" class="w-full border border-stone-300 rounded px-3 py-2">
      <option value="">-- None --</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $book['category_id'] ? 'selected' : '' ?>>
          <?= h($cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Cover Image</label>
    <?php if ($book['cover_image']): ?>
      <img src="../uploads/<?= h($book['cover_image']) ?>" class="w-16 h-20 object-cover rounded mb-2">
    <?php endif; ?>
    <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" class="w-full border border-stone-300 rounded px-3 py-2 bg-white">
    <p class="text-xs text-stone-400 mt-1">Leave empty to keep the current cover.</p>
  </div>

  <button type="submit" class="bg-stone-900 text-white px-6 py-2.5 rounded hover:bg-amber-600 transition">
    Update Book
  </button>
</form>

<?php require 'includes/admin_footer.php'; ?>
