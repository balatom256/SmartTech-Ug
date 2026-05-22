<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'Checkout – SmartTech-UG';

$items = getCartItems($conn);
if(empty($items)){ header('Location: cart.php'); exit; }
$subtotal = getCartTotal($conn);
$delivery = $subtotal >= 500000 ? 0 : 15000;
$total    = $subtotal + $delivery;

$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $address = sanitize($conn, $_POST['address']);
    $payment = sanitize($conn, $_POST['payment']);
    $notes   = sanitize($conn, $_POST['notes'] ?? '');
    $uid     = (int)$_SESSION['user_id'];

    if(!$address || !$payment){ $error = 'Please fill in all required fields.'; }
    else {
        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO orders (user_id,total,status,payment_method,delivery_address,notes) 
                          VALUES($uid,$total,'pending','$payment','$address','$notes')");
            $oid = $conn->insert_id;
            foreach($items as $it){
                $pid=(int)$it['product_id']; $qty=(int)$it['quantity']; $price=(float)$it['price'];
                $conn->query("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES($oid,$pid,$qty,$price)");
                $conn->query("UPDATE products SET stock=stock-$qty WHERE id=$pid");
            }
            $conn->query("DELETE FROM cart WHERE user_id=$uid");
            $conn->commit();
            $_SESSION['last_order'] = $oid;
            header('Location: order_success.php'); exit;
        } catch(Exception $e){
            $conn->rollback();
            $error = 'Something went wrong. Please try again.';
        }
    }
}

include 'includes/header.php';
?>
<style>
.checkout-wrap{display:grid;grid-template-columns:1fr 360px;gap:2rem;padding:90px 5% 60px;min-height:80vh}
@media(max-width:800px){.checkout-wrap{grid-template-columns:1fr}}
.order-item{display:flex;align-items:center;gap:.8rem;padding:.8rem 0;border-bottom:1px solid var(--border)}
.order-item:last-child{border-bottom:none}
</style>

<div class="checkout-wrap">
  <div>
    <h2 style="font-family:var(--font-head);font-weight:800;margin-bottom:1.8rem">Checkout</h2>
    <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <form method="POST">
      <div class="card" style="margin-bottom:1.5rem">
        <h3 style="font-family:var(--font-head);font-weight:700;margin-bottom:1.5rem"><i class="fa-solid fa-location-dot" style="color:var(--accent);margin-right:8px"></i>Delivery Details</h3>
        <div class="form-group">
          <label>Full Name *</label>
          <input class="form-control" value="<?= htmlspecialchars($_SESSION['name']) ?>" readonly style="opacity:.7"/>
        </div>
        <div class="form-group">
          <label>Delivery Address *</label>
          <textarea class="form-control" name="address" rows="3" placeholder="e.g. Nakasero, Plot 12, Kampala" required></textarea>
        </div>
        <div class="form-group">
          <label>Phone Number *</label>
          <input class="form-control" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" readonly style="opacity:.7" placeholder="+256..."/>
        </div>
        <div class="form-group">
          <label>Order Notes (optional)</label>
          <textarea class="form-control" name="notes" rows="2" placeholder="Any special delivery instructions…"></textarea>
        </div>
      </div>

      <div class="card" style="margin-bottom:1.5rem">
        <h3 style="font-family:var(--font-head);font-weight:700;margin-bottom:1.5rem"><i class="fa-solid fa-credit-card" style="color:var(--accent);margin-right:8px"></i>Payment Method</h3>
        <?php foreach([
          ['MTN Mobile Money','fa-solid fa-mobile-signal','Pay via MTN MoMo. You will receive a prompt on your phone.'],
          ['Airtel Money','fa-solid fa-mobile-screen','Pay via Airtel Money. Fast and secure.'],
          ['VISA / Mastercard','fa-brands fa-cc-visa','Pay with your debit or credit card.'],
          ['Pay on Delivery','fa-solid fa-truck','Cash or mobile money on delivery.'],
        ] as [$val,$icon,$desc]): ?>
        <label style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;border:1px solid var(--border);border-radius:12px;cursor:pointer;margin-bottom:.8rem;transition:border-color .2s" 
               onmouseover="this.style.borderColor='rgba(0,229,160,.4)'" onmouseout="this.style.borderColor='var(--border)'">
          <input type="radio" name="payment" value="<?= $val ?>" style="margin-top:3px;accent-color:var(--accent)" required>
          <div>
            <div style="font-weight:600;display:flex;align-items:center;gap:8px"><i class="<?= $icon ?>" style="color:var(--accent)"></i><?= $val ?></div>
            <div style="color:var(--text);font-size:.82rem;margin-top:3px"><?= $desc ?></div>
          </div>
        </label>
        <?php endforeach; ?>
      </div>

      <button class="btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:1rem">
        <i class="fa-solid fa-lock"></i> Place Order – <?= formatPrice($total) ?>
      </button>
    </form>
  </div>

  <!-- ORDER SUMMARY -->
  <div>
    <div class="card" style="position:sticky;top:90px">
      <h3 style="font-family:var(--font-head);font-weight:700;margin-bottom:1.5rem">Your Order</h3>
      <?php foreach($items as $it): ?>
      <div class="order-item">
        <div style="font-size:1.8rem"><?= $it['emoji'] ?></div>
        <div style="flex:1">
          <div style="font-weight:600;font-size:.88rem"><?= htmlspecialchars($it['name']) ?></div>
          <div style="color:var(--text);font-size:.78rem">Qty: <?= $it['quantity'] ?></div>
        </div>
        <div style="font-weight:700;font-size:.9rem"><?= formatPrice($it['price'] * $it['quantity']) ?></div>
      </div>
      <?php endforeach; ?>
      <div style="border-top:1px solid var(--border);margin-top:1rem;padding-top:1rem">
        <div style="display:flex;justify-content:space-between;font-size:.88rem;color:var(--text);margin-bottom:.5rem"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
        <div style="display:flex;justify-content:space-between;font-size:.88rem;color:var(--text);margin-bottom:.5rem"><span>Delivery</span><span><?= $delivery==0?'<span style="color:var(--accent)">FREE</span>':formatPrice($delivery) ?></span></div>
        <div style="display:flex;justify-content:space-between;font-family:var(--font-head);font-weight:800;font-size:1.1rem;margin-top:.8rem;color:var(--accent)"><span>Total</span><span><?= formatPrice($total) ?></span></div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
