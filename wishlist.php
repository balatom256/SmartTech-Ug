<?php
require_once 'includes/config.php';
$pageTitle = 'Wishlist – SmartTech-UG';

if($_SERVER['REQUEST_METHOD']==='POST'){
    requireLogin();
    $uid = (int)$_SESSION['user_id'];
    $pid = (int)$_POST['product_id'];
    $action = $_POST['action'] ?? '';
    $redirect = sanitize($conn, $_POST['redirect'] ?? 'wishlist.php');
    if($action==='add')    $conn->query("INSERT IGNORE INTO wishlist (user_id,product_id) VALUES($uid,$pid)");
    if($action==='remove') $conn->query("DELETE FROM wishlist WHERE user_id=$uid AND product_id=$pid");
    header("Location: $redirect"); exit;
}

requireLogin();
$uid = (int)$_SESSION['user_id'];
$items = $conn->query("SELECT p.*, w.id as wid FROM wishlist w JOIN products p ON w.product_id=p.id WHERE w.user_id=$uid ORDER BY w.added_at DESC")->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<div class="page-wrap">
  <div class="section-label">Account</div>
  <h2 class="section-title">My Wishlist <span style="color:var(--text);font-size:1rem;font-weight:400">(<?= count($items) ?>)</span></h2>
  <?php if(empty($items)): ?>
  <div style="text-align:center;padding:4rem;color:var(--text)">
    <i class="fa-regular fa-heart" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem"></i>
    <p>Your wishlist is empty. <a href="products.php" style="color:var(--accent)">Browse products</a></p>
  </div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:1.5rem">
    <?php foreach($items as $p): ?>
    <div class="card" style="padding:1.2rem;border-radius:18px">
      <div style="font-size:3rem;text-align:center;background:rgba(255,255,255,.03);border-radius:12px;padding:.8rem;margin-bottom:.8rem"><?= $p['emoji'] ?></div>
      <div style="color:var(--accent);font-size:.72rem;font-weight:600;margin-bottom:3px"><?= htmlspecialchars($p['brand']) ?></div>
      <div style="font-family:var(--font-head);font-weight:700;font-size:.9rem;margin-bottom:.6rem"><?= htmlspecialchars($p['name']) ?></div>
      <div style="font-family:var(--font-head);font-weight:800;color:var(--accent);margin-bottom:1rem"><?= formatPrice($p['price']) ?></div>
      <div style="display:flex;gap:8px">
        <form method="POST" action="cart.php" style="flex:1">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="redirect" value="wishlist.php">
          <button class="btn-primary btn-sm" style="width:100%;justify-content:center">Add to Cart</button>
        </form>
        <form method="POST">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <input type="hidden" name="action" value="remove">
          <button class="btn-outline btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
