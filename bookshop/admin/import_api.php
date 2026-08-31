<?php
require '../config/db.php';
require '../includes/functions.php';
requireAdminLogin();

$pageTitle = 'Fetch Books Online';
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$searchResults = [];
$searchError = '';
$importMessage = '';

/**
 * Calls the Google Books API and returns a simplified array of results.
 * No API key required for basic searches, though Google rate-limits
 * anonymous requests — for heavier use, get a free key from Google Cloud
 * Console and append &key=YOUR_KEY to the URL below.
 */
function searchGoogleBooks($query) {
    $url = 'https://www.googleapis.com/books/v1/volumes?q=' . urlencode($query) . '&maxResults=12';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        throw new Exception('Could not reach Google Books API right now.');
    }

    $data = json_decode($response, true);
    $items = [];

    foreach ($data['items'] ?? [] as $item) {
        $info = $item['volumeInfo'] ?? [];
        $items[] = [
            'google_id'   => $item['id'],
            'title'       => $info['title'] ?? 'Untitled',
            'author'      => implode(', ', $info['authors'] ?? ['Unknown']),
            'description' => $info['description'] ?? '',
            'thumbnail'   => str_replace('http://', 'https://', $info['imageLinks']['thumbnail'] ?? ''),
        ];
    }

    return $items;
}

/**
 * Downloads a cover image from a URL and saves it into /uploads,
 * the same way handleCoverUpload() does for manually-uploaded covers.
 */
function downloadCoverImage($url, $uploadDir) {
    if (!$url) return null;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $imageData = curl_exec($ch);
    curl_close($ch);

    if (!$imageData) return null;

    $filename = uniqid('book_', true) . '.jpg';
    file_put_contents(rtrim($uploadDir, '/') . '/' . $filename, $imageData);
    return $filename;
}

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['q'])) {
    try {
        $searchResults = searchGoogleBooks($_GET['q']);
    } catch (Exception $e) {
        $searchError = $e->getMessage();
    }
}

// Handle importing one selected result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $thumbnail = $_POST['thumbnail'] ?? '';
    $price = (float)($_POST['price'] ?? 9.99);
    $stock = (int)($_POST['stock'] ?? 5);
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;

    if ($title === '' || $author === '') {
        $importMessage = 'Missing title or author — could not import.';
    } else {
        $coverFilename = downloadCoverImage($thumbnail, '../uploads');

        $stmt = $pdo->prepare(
            'INSERT INTO books (title, author, description, price, stock, category_id, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $author, $description, $price, $stock, $categoryId, $coverFilename]);
        $importMessage = "Imported \"$title\" successfully.";
    }

    // Re-run the same search so results stay on screen after importing
    if (!empty($_GET['q'])) {
        $searchResults = searchGoogleBooks($_GET['q']);
    }
}

require 'includes/admin_header.php';
?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
  <form method="GET" class="flex gap-3">
    <input type="text" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Search by title, author, or ISBN..."
           class="flex-1 border border-stone-300 rounded px-3 py-2" required>
    <button class="bg-stone-900 text-white px-6 py-2 rounded hover:bg-amber-600 transition">Search</button>
  </form>
  <p class="text-xs text-stone-400 mt-2">Pulls title, author, description, and cover image from Google Books. You'll set the price and stock before each import.</p>
</div>

<?php if ($searchError): ?>
  <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-4 text-sm"><?= h($searchError) ?></div>
<?php endif; ?>
<?php if ($importMessage): ?>
  <div class="bg-green-50 text-green-700 border border-green-200 rounded p-3 mb-4 text-sm"><?= h($importMessage) ?></div>
<?php endif; ?>

<?php if (!empty($searchResults)): ?>
  <div class="grid md:grid-cols-2 gap-4">
    <?php foreach ($searchResults as $book): ?>
      <div class="bg-white rounded-lg shadow-sm p-4 flex gap-4">
        <div class="w-16 h-24 bg-stone-200 rounded overflow-hidden flex-shrink-0">
          <?php if ($book['thumbnail']): ?>
            <img src="<?= h($book['thumbnail']) ?>" class="w-full h-full object-cover">
          <?php endif; ?>
        </div>

        <form method="POST" class="flex-1 space-y-2">
          <input type="hidden" name="action" value="import">
          <input type="hidden" name="title" value="<?= h($book['title']) ?>">
          <input type="hidden" name="author" value="<?= h($book['author']) ?>">
          <input type="hidden" name="description" value="<?= h($book['description']) ?>">
          <input type="hidden" name="thumbnail" value="<?= h($book['thumbnail']) ?>">

          <p class="font-semibold text-sm leading-snug"><?= h($book['title']) ?></p>
          <p class="text-xs text-stone-500"><?= h($book['author']) ?></p>

          <div class="flex gap-2">
            <input type="number" step="0.01" name="price" placeholder="Price" value="9.99" required
                   class="w-20 border border-stone-300 rounded px-2 py-1 text-sm">
            <input type="number" name="stock" placeholder="Stock" value="5" required
                   class="w-16 border border-stone-300 rounded px-2 py-1 text-sm">
            <select name="category_id" class="flex-1 border border-stone-300 rounded px-2 py-1 text-sm">
              <option value="">No category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <button class="bg-amber-600 text-white text-sm px-4 py-1.5 rounded hover:bg-amber-700 transition">
            Import This Book
          </button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
<?php elseif (!empty($_GET['q']) && !$searchError): ?>
  <p class="text-stone-500">No results found.</p>
<?php endif; ?>

<?php require 'includes/admin_footer.php'; ?>
