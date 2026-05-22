<?php
require_once '../includes/config.php';
$pageTitle = 'Orders';
include 'admin_layout.php';

// Update status
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_status'])){
    $oid = (int)$_POST['order_id'];
    $st  = sanitize($conn,$_POST['status']);
    if(in_array($st,['pending','processing','shipped','delivered','cancelled'])){
        $conn->query("UPDATE orders SET status='$st' WHERE id=$oid");
        $_SESSION['admin_msg']='Order updated.'; $_SESSION['admin_msg_type']='success';
    }
    header('Location: orders.php'); exit;
}

// Filters
$statusF = sanitize($conn,$_GET['status'] ?? '');
$search  = sanitize($conn,$_GET['s'] ?? '');
$where = "1=1";
if($statusF) $where .= " AND o.status='$statusF'";
if($search)  $where .= " AND (u.name LIKE '%$search%' OR o.id LIKE '%$search%')";

$orders = $conn->query("SELECT o.*,u.name as uname,u.email,u.phone FROM orders o JOIN users u ON o.user_id=u.id WHERE $where ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$sc = ['pending'=>'badge-gray','processing'=>'badge-blue','shipped'=>'badge-orange','delivered'=>'badge-green','cancelled'=>'badge-orange'];
?>

<div class="card">
  <div class="card-title flex justify-between flex-center" style="flex-wrap:wrap;gap:.8rem">
    <span><i class="fa-solid fa-bag-shopping"></i> All Orders (<?= count($orders) ?>)</span>
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
      <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Search customer or order #…" class="form-control" style="border-radius:8px;padding:.45rem .9rem;min-width:180px"/>
      <select name="status" class="form-control" style="width:auto;border-radius:8px;padding:.45rem .8rem" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusF===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
      <?php if($statusF||$search): ?><a href="orders.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
    </form>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Contact</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Update</th></tr></thead>
      <tbody>
      <?php foreach($orders as $o):
        $items = $conn->query("SELECT oi.*,p.name as pname,p.emoji FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id={$o['id']}")->fetch_all(MYSQLI_ASSOC);
      ?>
        <tr>
          <td>
            <strong class="text-accent">#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></strong>
            <div style="font-size:.72rem;color:var(--text);margin-top:2px"><?= count($items) ?> item<?= count($items)!=1?'s':'' ?></div>
            <details style="font-size:.72rem;color:var(--text);margin-top:4px">
              <summary style="cursor:pointer;color:var(--accent)">View items</summary>
              <?php foreach($items as $it): ?><div style="margin-top:3px"><?= $it['emoji'] ?> <?= htmlspecialchars($it['pname']) ?> × <?= $it['quantity'] ?> — <?= formatPrice($it['price']*$it['quantity']) ?></div><?php endforeach; ?>
              <?php if($o['delivery_address']): ?><div style="margin-top:4px;color:var(--text)">📍 <?= htmlspecialchars($o['delivery_address']) ?></div><?php endif; ?>
              <?php if($o['notes']): ?><div style="margin-top:4px">📝 <?= htmlspecialchars($o['notes']) ?></div><?php endif; ?>
            </details>
          </td>
          <td><div class="fw-bold" style="font-size:.85rem"><?= htmlspecialchars($o['uname']) ?></div></td>
          <td class="text-muted" style="font-size:.8rem"><?= htmlspecialchars($o['phone'] ?? '') ?><br><?= htmlspecialchars($o['email']) ?></td>
          <td class="text-muted" style="font-size:.8rem"><?= date('d M Y',strtotime($o['created_at'])) ?><br><?= date('H:i',strtotime($o['created_at'])) ?></td>
          <td><strong class="text-accent"><?= formatPrice($o['total']) ?></strong></td>
          <td class="text-muted" style="font-size:.8rem"><?= htmlspecialchars($o['payment_method']) ?></td>
          <td><span class="badge <?= $sc[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
          <td>
            <form method="POST" style="display:flex;gap:5px">
              <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
              <select name="status" class="form-control" style="padding:.28rem .5rem;font-size:.75rem;border-radius:6px;width:115px">
                <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button name="update_status" class="btn btn-primary btn-sm">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'admin_footer.php'; ?>
