<?php
require_once '../includes/config.php';
$pageTitle = 'Dashboard';
include 'admin_layout.php';

$totalProducts  = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$totalOrders    = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$totalRevenue   = $conn->query("SELECT COALESCE(SUM(total),0) c FROM orders WHERE status='delivered'")->fetch_assoc()['c'];
$pendingCount   = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$todaySales     = $conn->query("SELECT COALESCE(SUM(total),0) c FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetch_assoc()['c'];
$outOfStock     = $conn->query("SELECT COUNT(*) c FROM products WHERE stock=0")->fetch_assoc()['c'];
$lowStockItems  = $conn->query("SELECT COUNT(*) c FROM products WHERE stock > 0 AND stock <= 5")->fetch_assoc()['c'];
$recentOrders   = $conn->query("SELECT o.*,u.name as uname,u.phone FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$topProducts    = $conn->query("SELECT p.name,p.emoji,SUM(oi.quantity) as sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY p.id ORDER BY sold DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$monthly        = $conn->query("SELECT DATE_FORMAT(created_at,'%b') as mon, SUM(total) as rev FROM orders WHERE status='delivered' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY MONTH(created_at) ORDER BY created_at")->fetch_all(MYSQLI_ASSOC);

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['quick_status'])){
    $oid = (int)$_POST['order_id'];
    $st  = sanitize($conn,$_POST['status']);
    if(in_array($st,['pending','processing','shipped','delivered','cancelled'])){
        $conn->query("UPDATE orders SET status='$st' WHERE id=$oid");
        $_SESSION['admin_msg']='Order #'.str_pad($oid,4,'0',STR_PAD_LEFT).' → '.ucfirst($st);
        $_SESSION['admin_msg_type']='success';
    }
    header('Location: index.php'); exit;
}
$sc = ['pending'=>'badge-gray','processing'=>'badge-blue','shipped'=>'badge-orange','delivered'=>'badge-green','cancelled'=>'badge-orange'];
?>

<div class="stats-row">
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon"><i class="fa-solid fa-chart-line"></i></div><span class="stat-trend">All time</span></div><div class="stat-num" style="font-size:1.2rem"><?= formatPrice($totalRevenue) ?></div><div class="stat-label">Total Revenue</div></div>
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon orange"><i class="fa-solid fa-bag-shopping"></i></div><span class="stat-trend" style="color:var(--accent2)"><?= $pendingCount ?> pending</span></div><div class="stat-num"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div>
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon blue"><i class="fa-solid fa-users"></i></div></div><div class="stat-num"><?= $totalCustomers ?></div><div class="stat-label">Customers</div></div>
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon purple"><i class="fa-solid fa-box"></i></div><?php if($outOfStock): ?><span class="stat-trend down"><?= $outOfStock ?> out</span><?php endif; ?></div><div class="stat-num"><?= $totalProducts ?></div><div class="stat-label">Products</div></div>
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div><span class="stat-trend">Today</span></div><div class="stat-num" style="font-size:1.1rem"><?= formatPrice($todaySales) ?></div><div class="stat-label">Today's Sales</div></div>
</div>

<?php if($outOfStock>0 || $lowStockItems>0): ?>
<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i>
  <?php if($outOfStock) echo "<strong>$outOfStock product(s) out of stock.</strong> "; ?>
  <?php if($lowStockItems) echo "<strong>$lowStockItems product(s) low stock (≤5).</strong> "; ?>
  <a href="products.php" style="color:inherit;text-decoration:underline">Fix stock →</a>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start">

  <div>
    <div class="card">
      <div class="card-title flex justify-between flex-center" style="flex-wrap:wrap;gap:.5rem">
        <span><i class="fa-solid fa-receipt"></i> Recent Orders</span>
        <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th><th>Update</th></tr></thead>
          <tbody>
          <?php foreach($recentOrders as $o): ?>
            <tr>
              <td><strong class="text-accent">#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></strong></td>
              <td><div class="fw-bold" style="font-size:.85rem"><?= htmlspecialchars($o['uname']) ?></div><div class="text-muted" style="font-size:.72rem"><?= $o['phone'] ?></div></td>
              <td class="text-muted" style="font-size:.8rem"><?= date('d M y',strtotime($o['created_at'])) ?></td>
              <td><strong><?= formatPrice($o['total']) ?></strong></td>
              <td><span class="badge <?= $sc[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
              <td>
                <form method="POST" style="display:flex;gap:5px">
                  <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                  <select name="status" class="form-control" style="padding:.28rem .5rem;font-size:.75rem;border-radius:6px;width:110px">
                    <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button name="quick_status" class="btn btn-primary btn-sm">✓</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:1.5rem">
    <div class="card">
      <div class="card-title"><i class="fa-solid fa-fire"></i> Top Products</div>
      <?php if(empty($topProducts)): ?>
      <p class="text-muted" style="font-size:.82rem">No sales yet.</p>
      <?php else: foreach($topProducts as $tp): ?>
      <div style="display:flex;align-items:center;gap:.7rem;padding:.5rem 0;border-bottom:1px solid var(--border)">
        <span style="font-size:1.4rem"><?= $tp['emoji'] ?></span>
        <div style="flex:1;font-size:.82rem;font-weight:600"><?= htmlspecialchars(substr($tp['name'],0,22)) ?>…</div>
        <span class="badge badge-green"><?= $tp['sold'] ?>×</span>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="card">
      <div class="card-title"><i class="fa-solid fa-chart-bar"></i> Monthly Revenue</div>
      <?php if(empty($monthly)): ?>
      <p class="text-muted" style="font-size:.82rem">No delivered orders.</p>
      <?php else: $maxR=max(array_column($monthly,'rev'))?:1; ?>
      <div style="display:flex;align-items:flex-end;gap:6px;height:100px">
        <?php foreach($monthly as $m): $h=round(($m['rev']/$maxR)*90); ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px">
          <div style="width:100%;background:rgba(0,229,160,.2);border-radius:4px 4px 0 0;height:<?= $h ?>px"></div>
          <div style="font-size:.65rem;color:var(--text)"><?= $m['mon'] ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <a href="products.php?action=add" class="btn btn-primary btn-sm" style="justify-content:center"><i class="fa-solid fa-plus"></i> Add New Product</a>
        <a href="categories.php" class="btn btn-outline btn-sm" style="justify-content:center"><i class="fa-solid fa-tags"></i> Manage Categories</a>
        <a href="reports.php" class="btn btn-outline btn-sm" style="justify-content:center"><i class="fa-solid fa-download"></i> Export Sales Report</a>
        <a href="settings.php" class="btn btn-outline btn-sm" style="justify-content:center"><i class="fa-solid fa-gear"></i> Site Settings</a>
      </div>
    </div>
  </div>
</div>

<?php include 'admin_footer.php'; ?>
