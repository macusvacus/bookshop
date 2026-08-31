<?php
require '../config/db.php';
require '../includes/functions.php';
requireAdminLogin();

$pageTitle = 'Import Books from CSV';
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$categoryByName = [];
foreach ($categories as $cat) {
    $categoryByName[strtolower($cat['name'])] = $cat['id'];
}

$results = [];   // one entry per row: ['title' => ..., 'status' => 'ok'|'error', 'message' => ...]
$imported = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['csv_file']['tmp_name'])) {

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if ($handle === false) {
        $results[] = ['title' => '(file)', 'status' => 'error', 'message' => 'Could not read the uploaded file.'];
    } else {
        // First row must be the header: title,author,description,price,stock,category
        $header = fgetcsv($handle);
        $header = array_map('strtolower', array_map('trim', $header));

        $required = ['title', 'author', 'price'];
        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            $results[] = [
                'title' => '(header)',
                'status' => 'error',
                'message' => 'CSV is missing required column(s): ' . implode(', ', $missing),
            ];
        } else {
            $insertStmt = $pdo->prepare(
                'INSERT INTO books (title, author, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?, ?)'
            );

            while (($row = fgetcsv($handle)) !== false) {
                // Map each CSV column to its header name, e.g. $data['title']
                $data = array_combine($header, $row);

                $title = trim($data['title'] ?? '');
                $author = trim($data['author'] ?? '');
                $price = (float)($data['price'] ?? 0);

                if ($title === '' || $author === '' || $price <= 0) {
                    $results[] = ['title' => $title ?: '(blank)', 'status' => 'error', 'message' => 'Missing title/author or invalid price — row skipped.'];
                    continue;
                }

                $description = trim($data['description'] ?? '');
                $stock = (int)($data['stock'] ?? 0);
                $categoryName = strtolower(trim($data['category'] ?? ''));
                $categoryId = $categoryByName[$categoryName] ?? null;

                try {
                    $insertStmt->execute([$title, $author, $description, $price, $stock, $categoryId]);
                    $results[] = ['title' => $title, 'status' => 'ok', 'message' => 'Imported.'];
                    $imported++;
                } catch (Exception $e) {
                    $results[] = ['title' => $title, 'status' => 'error', 'message' => $e->getMessage()];
                }
            }
        }
        fclose($handle);
    }
}

require 'includes/admin_header.php';
?>

<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
  <p class="text-sm text-stone-600 mb-4">
    Upload a CSV with a header row: <code class="bg-stone-100 px-1 rounded">title,author,description,price,stock,category</code>
    (<code>description</code>, <code>stock</code>, and <code>category</code> are optional — category must match an existing category name exactly).
    No cover image is set this way; add one later by editing the book.
  </p>

  <form method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
    <input type="file" name="csv_file" accept=".csv" required class="border border-stone-300 rounded px-3 py-2 flex-1">
    <button type="submit" class="bg-stone-900 text-white px-5 py-2 rounded hover:bg-amber-600 transition">
      Import
    </button>
  </form>

  <a href="data:text/csv;charset=utf-8,title%2Cauthor%2Cdescription%2Cprice%2Cstock%2Ccategory%0AThe%20Hobbit%2CJ.R.R.%20Tolkien%2CA%20fantasy%20adventure.%2C14.99%2C10%2CFiction"
     download="sample.csv" class="text-xs text-amber-600 hover:underline mt-2 inline-block">
    Download a sample CSV
  </a>
</div>

<?php if (!empty($results)): ?>
  <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mt-6">
    <h2 class="font-semibold mb-3"><?= $imported ?> of <?= count($results) ?> rows imported</h2>
    <table class="w-full text-sm">
      <?php foreach ($results as $r): ?>
        <tr class="border-t">
          <td class="py-2"><?= h($r['title']) ?></td>
          <td class="py-2 <?= $r['status'] === 'ok' ? 'text-green-600' : 'text-red-500' ?>"><?= h($r['message']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php require 'includes/admin_footer.php'; ?>
