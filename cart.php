<?php
require_once 'includes/config.php';
$pageTitle = 'My Cart – SmartTech-UG';

// ── Cart Actions ──
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    requireLogin();
    $uid = (int)$_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);
    $redirect = sanitize($conn, $_POST['redirect'] ?? 'cart.php');

    if($action === 'add' && $pid){
        $qty = (int)($_POST['quantity'] ?? 1);
        $conn->query("INSERT INTO cart (user_id,product_id,quantity) VALUES($uid,$pid,$qty)
                      ON DUPLICATE KEY UPDATE quantity=quantity+$qty");
        $_SESSION['msg'] = 'Item added to cart! 🛒';
        $_SESSION['msg_type'] = 'success';
        header("Location: $redirect"); exit;
    }

    if($action === 'update' && $pid){
        $qty = (int)$_POST['quantity'];
        if($qty < 1) $conn->query("DELETE FROM cart WHERE user_id=$uid AND product_id=$pid");
        else $conn->query("UPDATE cart SET quantity=$qty WHERE user_id=$uid AND product_id=$pid");
        header('Location: cart.php'); exit;
    }

    if($action === 'remove' && $pid){
        $conn->query("DELETE FROM cart WHERE user_id=$uid AND product_id=$pid");
        header('Location: cart.php'); exit;
    }

    if($action === 'clear'){
        $conn->query("DELETE FROM cart WHERE user_id=$uid");
        header('Location: cart.php'); exit;
    }
}

include 'includes/header.php';
$items = getCartItems($conn);
$total = getCartTotal($conn);
?>
<style>
.cart-wrap{display:grid;grid-template-columns:1fr 340px;gap:2rem;padding:90px 5% 60px;min-height:80vh}
.cart-item{display:flex;align-items:center;gap:1.2rem;padding:1.2rem 0;border-bottom:1px solid var(--border)}
.cart-item:last-child{border-bottom:none}
.item-emoji{font-size:2.8rem;width:70px;height:70px;background:rgba(255,255,255,.04);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.item-info{flex:1}
.item-name{font-family:var(--font-head);font-weight:700;margin-bottom:.3rem}
.item-price{color:var(--accent);font-weight:700;font-size:.95rem}
.qty-control{display:flex;align-items:center;gap:8px;margin-top:.5rem}
.qty-btn{background:rgba(255,255,255,.08);border:1px solid var(--border);color:var(--white);width:28px;height:28px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem;transition:background .2s}
.qty-btn:hover{background:rgba(0,229,160,.15);color:var(--accent)}
.qty-num{font-weight:600;min-width:24px;text-align:center}
.summary-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:1.8rem;position:sticky;top:90px;height:fit-content}
.summary-row{display:flex;justify-content:space-between;padding:.6rem 0;font-size:.9rem;color:var(--text)}
.summary-total{display:flex;justify-content:space-between;padding-top:1rem;margin-top:.5rem;border-top:1px solid var(--border);font-family:var(--font-head);font-weight:800;font-size:1.1rem;color:var(--white)}
.empty-cart{text-align:center;padding:5rem 2rem;grid-column:1/-1}
.empty-cart i{font-size:4rem;color:var(--accent);opacity:.3;display:block;margin-bottom:1.5rem}
@media(max-width:800px){.cart-wrap{grid-template-columns:1fr}}
</style>

<div class="cart-wrap">
  <?php if(empty($items)): ?>
  <div class="empty-cart">
    <i class="fa-solid fa-cart-shopping"></i>
    <h2 style="font-family:var(--font-head);font-weight:800;margin-bottom:.8rem">Your cart is empty</h2>
    <p style="color:var(--text);margin-bottom:2rem">Add some awesome tech products to get started!</p>
    <a href="products.php" class="btn-primary">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
  </div>

  <?php else: ?>
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
      <h2 style="font-family:var(--font-head);font-weight:800">Shopping Cart <span style="color:var(--text);font-size:1rem;font-weight:400">(<?= count($items) ?> items)</span></h2>
      <form method="POST">
        <input type="hidden" name="action" value="clear">
        <button class="btn-outline btn-sm btn-danger" onclick="return confirm('Clear cart?')">Clear All</button>
      </form>
    </div>

    <?php if(isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?>"><?= $_SESSION['msg'] ?></div>
    <?php unset($_SESSION['msg'],$_SESSION['msg_type']); endif; ?>

    <?php foreach($items as $item): ?>
    <div class="cart-item">
      <div class="item-emoji"><?= $item['emoji'] ?></div>
      <div class="item-info">
        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
        <div class="item-price"><?= formatPrice($item['price']) ?></div>
        <div class="qty-control">
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
            <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
            <button class="qty-btn">−</button>
          </form>
          <span class="qty-num"><?= $item['quantity'] ?></span>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
            <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
            <button class="qty-btn">+</button>
          </form>
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-family:var(--font-head);font-weight:800;margin-bottom:.8rem"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
        <form method="POST">
          <input type="hidden" name="action" value="remove">
          <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
          <button class="btn-outline btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- SUMMARY -->
  <div>
    <div class="summary-card">
      <h3 style="font-family:var(--font-head);font-weight:700;margin-bottom:1.5rem">Order Summary</h3>
      <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($total) ?></span></div>
      <div class="summary-row"><span>Delivery</span><span><?= $total >= 500000 ? '<span style="color:var(--accent)">FREE</span>' : formatPrice(15000) ?></span></div>
      <div class="summary-row"><span>Tax (0%)</span><span>UGX 0</span></div>
      <div class="summary-total">
        <span>Total</span>
        <span style="color:var(--accent)"><?= formatPrice($total + ($total < 500000 ? 15000 : 0)) ?></span>
      </div>
      <?php if($total >= 500000): ?>
      <div style="background:rgba(0,229,160,.08);border:1px solid rgba(0,229,160,.2);border-radius:10px;padding:.7rem 1rem;margin-top:1rem;font-size:.82rem;color:var(--accent)">
        <i class="fa-solid fa-truck"></i> You qualify for FREE delivery!
      </div>
      <?php else: ?>
      <div style="color:var(--text);font-size:.82rem;margin-top:1rem">
        Add <?= formatPrice(500000 - $total) ?> more for free delivery
      </div>
      <?php endif; ?>
      <a href="checkout.php" class="btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem">
        Proceed to Checkout <i class="fa-solid fa-arrow-right"></i>
      </a>
      <a href="products.php" class="btn-outline" style="width:100%;justify-content:center;margin-top:.8rem">
        Continue Shopping
      </a>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
