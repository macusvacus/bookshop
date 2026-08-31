<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' – ' : '' ?>Vintage Bookstore</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50 text-stone-800 min-h-screen flex flex-col">

<header class="bg-stone-900 text-stone-100 shadow">
  <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
    <a href="index.php" class="text-xl font-serif font-bold tracking-wide">📚 Vintage Bookstore</a>

    <nav class="flex items-center gap-6 text-sm">
      <a href="index.php" class="hover:text-amber-400">Shop</a>
      <a href="categories.php" class="hover:text-amber-400">Categories</a>
      <a href="cart.php" class="relative hover:text-amber-400">
        Cart
        <?php $count = cartCount(); if ($count > 0): ?>
          <span class="absolute -top-2 -right-3 bg-amber-500 text-stone-900 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
            <?= $count ?>
          </span>
        <?php endif; ?>
      </a>
    </nav>
  </div>
</header>

<main class="flex-1 max-w-6xl w-full mx-auto px-4 py-8">
