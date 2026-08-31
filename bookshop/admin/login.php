<?php
require '../config/db.php';
require '../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // password_verify checks the plain password against the stored bcrypt
    // hash — we never store or compare raw passwords.
    if ($admin && password_verify($password, $admin['password'])) {
        // Regenerate session id on login to prevent session fixation attacks
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-900 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-lg shadow max-w-sm w-full">
    <h1 class="text-xl font-bold mb-1">Admin Login</h1>
    <p class="text-sm text-stone-500 mb-4">Pageturner Books dashboard</p>

    <?php if ($error): ?>
      <p class="text-red-500 text-sm mb-3"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-3">
      <input type="text" name="username" placeholder="Username" required class="w-full border rounded px-3 py-2">
      <input type="password" name="password" placeholder="Password" required class="w-full border rounded px-3 py-2">
      <button class="w-full bg-stone-900 text-white py-2 rounded hover:bg-amber-600 transition">Log In</button>
    </form>
  </div>
</body>
</html>
