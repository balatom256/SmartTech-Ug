<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'My Orders – SmartTech-UG';
$uid = (int)$_SESSION['user_id'];
$orders = $conn->query("SELECT * FROM orders WHERE user_id=$uid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
$statusColors = ['pending'=>'badge-gray','processing'=>'badge-orange','shipped'=>'badge-orange','delivered'=>'badge-accent','cancelled'=>'badge-orange'];
?>
<div class="page-wrap">
  <div class="section-label">Account</div>
  <h2 class="section-title">My Orders</h2>
  <?php if(empty($orders)): ?>
  <div style="text-align:center;padding:4rem;color:var(--text)">
    <i class="fa-solid fa-box-open" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem"></i>
    <p>No orders yet. <a href="products.php" style="color:var(--accent)">Start shopping!</a></p>
  </div>
  <?php else: ?>
  <div class="card" style="overflow:auto">
    <table class="data-table">
      <thead>
        <tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php foreach($orders as $o):
        $itemCount = $conn->query("SELECT SUM(quantity) c FROM order_items WHERE order_id={$o['id']}")->fetch_assoc()['c'];
      ?>
        <tr>
          <td><strong style="color:var(--accent)">#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></strong></td>
          <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td><?= $itemCount ?> item<?= $itemCount!=1?'s':'' ?></td>
          <td><strong><?= formatPrice($o['total']) ?></strong></td>
          <td><?= htmlspecialchars($o['payment_method']) ?></td>
          <td><span class="badge <?= $statusColors[$o['status']] ?? 'badge-gray' ?>"><?= ucfirst($o['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
