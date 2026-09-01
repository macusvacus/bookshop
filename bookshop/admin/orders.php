<?php
require '../config/db.php';
require '../includes/functions.php';
requireAdminLogin();

$pageTitle = 'Orders';
$orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();

require 'includes/admin_header.php';
?>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-stone-100 text-left">
      <tr>
        <th class="p-3">#</th>
        <th class="p-3">Customer</th>
        <th class="p-3">Phone</th>
        <th class="p-3">Total</th>
        <th class="p-3">Status</th>
        <th class="p-3">Date</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      <?php foreach ($orders as $order): ?>
        <tr>
          <td class="p-3">#<?= $order['id'] ?></td>
          <td class="p-3"><?= h($order['customer_name']) ?></td>
          <td class="p-3"><?= h($order['phone']) ?></td>
          <td class="p-3 font-semibold"><?= money(ksh($order['total'])) ?></td>
          <td class="p-3"><span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-xs"><?= h($order['status']) ?></span></td>
          <td class="p-3 text-stone-500"><?= h($order['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
        <tr><td colspan="6" class="p-6 text-center text-stone-400">No orders yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require 'includes/admin_footer.php'; ?>
