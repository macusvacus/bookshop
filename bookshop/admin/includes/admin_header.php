<?php
// Every admin page (except login.php/setup.php) includes this first.
// It enforces login and renders the shared sidebar shell.
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= isset($pageTitle) ? h($pageTitle) . ' – ' : '' ?>Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-100 min-h-screen flex">

<aside class="w-56 bg-stone-900 text-stone-200 flex-shrink-0 min-h-screen">
  <div class="p-4 font-bold text-lg border-b border-stone-700">📚 Admin</div>
  <nav class="p-4 space-y-1 text-sm">
    <a href="dashboard.php" class="block px-3 py-2 rounded hover:bg-stone-800">Dashboard</a>
    <a href="books_list.php" class="block px-3 py-2 rounded hover:bg-stone-800">Manage Books</a>
    <a href="add_book.php" class="block px-3 py-2 rounded hover:bg-stone-800">Add New Book</a>
    <a href="import_csv.php" class="block px-3 py-2 rounded hover:bg-stone-800">Bulk Import (CSV)</a>
    <a href="import_api.php" class="block px-3 py-2 rounded hover:bg-stone-800">Fetch Books Online</a>
    <a href="orders.php" class="block px-3 py-2 rounded hover:bg-stone-800">Orders</a>
    <a href="logout.php" class="block px-3 py-2 rounded hover:bg-red-700 mt-4">Logout</a>
  </nav>
</aside>

<main class="flex-1 p-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold"><?= isset($pageTitle) ? h($pageTitle) : 'Dashboard' ?></h1>
    <span class="text-sm text-stone-500">Logged in as <?= h($_SESSION['admin_username']) ?></span>
  </div>