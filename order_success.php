<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'Order Placed! – SmartTech-UG';
$oid = (int)($_SESSION['last_order'] ?? 0);
if(!$oid){ header('Location: orders.php'); exit; }
$order = $conn->query("SELECT * FROM orders WHERE id=$oid")->fetch_assoc();
unset($_SESSION['last_order']);
include 'includes/header.php';
?>
<div style="padding:120px 5% 80px;text-align:center;min-height:80vh;display:flex;flex-direction:column;align-items:center;justify-content:center">
  <div style="width:90px;height:90px;border-radius:50%;background:rgba(0,229,160,.15);border:2px solid rgba(0,229,160,.4);display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 2rem">✅</div>
  <h1 style="font-family:var(--font-head);font-size:2.5rem;font-weight:800;margin-bottom:1rem">Order Placed!</h1>
  <p style="color:var(--text);max-width:440px;line-height:1.75;margin-bottom:.8rem">Thank you for shopping with SmartTech-UG. Your order <strong style="color:var(--accent)">#<?= str_pad($oid,4,'0',STR_PAD_LEFT) ?></strong> has been received.</p>
  <p style="color:var(--text);font-size:.9rem;margin-bottom:2.5rem">Payment method: <strong style="color:var(--white)"><?= htmlspecialchars($order['payment_method']) ?></strong></p>
  <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center">
    <a href="orders.php" class="btn-primary">View My Orders</a>
    <a href="products.php" class="btn-outline">Continue Shopping</a>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
