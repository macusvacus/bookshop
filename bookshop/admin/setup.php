<?php
/**
 * Run this ONCE after importing database/schema.sql, to create your
 * first admin login. Visit /admin/setup.php in the browser, fill the
 * form, then DELETE THIS FILE — leaving it live lets anyone create
 * an admin account.
 */
require '../config/db.php';

$existing = $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($password) < 6) {
        $message = 'Username required, password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT); // bcrypt, salted
        $stmt = $pdo->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $hash]);
        $message = 'Admin account created! You can now delete setup.php and log in.';
        $existing++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-100 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-lg shadow max-w-sm w-full">
    <h1 class="text-xl font-bold mb-4">Create Admin Account</h1>

    <?php if ($existing > 0): ?>
      <p class="text-amber-600 text-sm mb-4">
        Note: <?= $existing ?> admin account(s) already exist. Delete this file if it's no longer needed.
      </p>
    <?php endif; ?>

    <?php if ($message): ?>
      <p class="text-sm mb-4 <?= str_starts_with($message, 'Admin') ? 'text-green-600' : 'text-red-600' ?>">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>

    <form method="POST" class="space-y-3">
      <input type="text" name="username" placeholder="Username" required class="w-full border rounded px-3 py-2">
      <input type="password" name="password" placeholder="Password (min 6 chars)" required class="w-full border rounded px-3 py-2">
      <button class="w-full bg-stone-900 text-white py-2 rounded">Create Account</button>
    </form>
  </div>
</body>
</html>
