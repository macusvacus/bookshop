<?php
require '../config/db.php';
require '../includes/functions.php';
requireAdminLogin();

$pageTitle = 'Add New Book';
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$error = '';
$success = '';

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
            // Save the uploaded cover image (if any) to /uploads
            $coverFilename = handleCoverUpload('cover_image', '../uploads');

            $stmt = $pdo->prepare(
                'INSERT INTO books (title, author, description, price, stock, category_id, cover_image)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$title, $author, $description, $price, $stock, $categoryId, $coverFilename]);

            $success = 'Book added successfully!';
            // Clear form fields after a successful save
            $title = $author = $description = '';
            $price = $stock = 0;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

require 'includes/admin_header.php';
?>

<?php if ($error): ?>
  <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-4 text-sm"><?= h($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="bg-green-50 text-green-700 border border-green-200 rounded p-3 mb-4 text-sm"><?= h($success) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6 max-w-2xl space-y-4">
  <!--
    enctype="multipart/form-data" is REQUIRED whenever a form uploads a file —
    without it, $_FILES will be empty and the cover image silently won't upload.
  -->

  <div>
    <label class="block text-sm font-medium mb-1">Title</label>
    <input type="text" name="title" value="<?= h($title ?? '') ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Author</label>
    <input type="text" name="author" value="<?= h($author ?? '') ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Description</label>
    <textarea name="description" rows="5" class="w-full border border-stone-300 rounded px-3 py-2"><?= h($description ?? '') ?></textarea>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Price ($)</label>
      <input type="number" step="0.01" min="0.01" name="price" value="<?= h($price ?? '') ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Stock Quantity</label>
      <input type="number" min="0" name="stock" value="<?= h($stock ?? 0) ?>" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Category</label>
    <select name="category_id" class="w-full border border-stone-300 rounded px-3 py-2">
      <option value="">-- None --</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Cover Image</label>
    <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" class="w-full border border-stone-300 rounded px-3 py-2 bg-white">
    <p class="text-xs text-stone-400 mt-1">JPG, PNG, or WEBP. Max 3MB.</p>
  </div>

  <button type="submit" class="bg-stone-900 text-white px-6 py-2.5 rounded hover:bg-amber-600 transition">
    Save Book
  </button>
</form>

<?php require 'includes/admin_footer.php'; ?>
