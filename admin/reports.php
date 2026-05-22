<?php
require_once '../includes/config.php';
$pageTitle = 'Sales Report';
include 'admin_layout.php';

$dateFrom = sanitize($conn,$_GET['from'] ?? date('Y-m-01'));
$dateTo   = sanitize($conn,$_GET['to']   ?? date('Y-m-d'));
$statusF  = sanitize($conn,$_GET['status'] ?? 'delivered');

$where = "o.created_at BETWEEN '$dateFrom 00:00:00' AND '$dateTo 23:59:59'";
if($statusF && $statusF !== 'all') $where .= " AND o.status='$statusF'";

$orders = $conn->query("SELECT o.*,u.name as uname,u.email,u.phone FROM orders o JOIN users u ON o.user_id=u.id WHERE $where ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$totalRev   = array_sum(array_column($orders,'total'));
$totalCount = count($orders);
$avgOrder   = $totalCount ? $totalRev / $totalCount : 0;

// CSV Export
if(isset($_GET['export'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="smarttech_sales_'.$dateFrom.'_to_'.$dateTo.'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['Order #','Customer','Email','Phone','Date','Total (UGX)','Payment','Status','Delivery Address']);
    foreach($orders as $o){
        fputcsv($out,[str_pad($o['id'],4,'0',STR_PAD_LEFT),$o['uname'],$o['email'],$o['phone'] ?? '',date('d/m/Y H:i',strtotime($o['created_at'])),$o['total'],$o['payment_method'],$o['status'],$o['delivery_address'] ?? '']);
    }
    fclose($out); exit;
}

// Daily breakdown
$daily = $conn->query("SELECT DATE(o.created_at) as day, COUNT(*) as cnt, SUM(o.total) as rev FROM orders o WHERE $where GROUP BY DATE(o.created_at) ORDER BY day DESC LIMIT 30")->fetch_all(MYSQLI_ASSOC);

// Payment method breakdown
$payBreak = $conn->query("SELECT payment_method, COUNT(*) as cnt, SUM(total) as rev FROM orders o WHERE $where GROUP BY payment_method ORDER BY rev DESC")->fetch_all(MYSQLI_ASSOC);
$sc = ['pending'=>'badge-gray','processing'=>'badge-blue','shipped'=>'badge-orange','delivered'=>'badge-green','cancelled'=>'badge-orange'];
?>

<!-- Filter Bar -->
<div class="card mb-2">
  <form method="GET" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
    <div class="form-group" style="margin:0">
      <label class="form-label">From Date</label>
      <input type="date" name="from" value="<?= $dateFrom ?>" class="form-control" style="border-radius:8px"/>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label">To Date</label>
      <input type="date" name="to" value="<?= $dateTo ?>" class="form-control" style="border-radius:8px"/>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label">Status</label>
      <select name="status" class="form-control" style="border-radius:8px;width:auto">
        <option value="all" <?= $statusF==='all'?'selected':'' ?>>All Statuses</option>
        <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusF===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
    <a href="reports.php?<?= http_build_query(array_merge($_GET,['export'=>1])) ?>" class="btn btn-outline"><i class="fa-solid fa-download"></i> Export CSV</a>
  </form>
</div>

<!-- Summary Stats -->
<div class="stats-row">
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon"><i class="fa-solid fa-chart-line"></i></div></div><div class="stat-num" style="font-size:1.2rem"><?= formatPrice($totalRev) ?></div><div class="stat-label">Total Revenue</div></div>
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon orange"><i class="fa-solid fa-receipt"></i></div></div><div class="stat-num"><?= $totalCount ?></div><div class="stat-label">Total Orders</div></div>
  <div class="stat-card"><div class="stat-card-top"><div class="stat-icon blue"><i class="fa-solid fa-calculator"></i></div></div><div class="stat-num" style="font-size:1.2rem"><?= formatPrice($avgOrder) ?></div><div class="stat-label">Avg. Order Value</div></div>
</div>

<div class="grid-2 mb-2" style="align-items:start">
  <!-- Daily breakdown -->
  <div class="card">
    <div class="card-title"><i class="fa-solid fa-calendar-days"></i> Daily Breakdown</div>
    <?php if(empty($daily)): ?>
    <p class="text-muted" style="font-size:.85rem">No orders in this period.</p>
    <?php else: ?>
    <div class="table-wrap" style="max-height:320px;overflow-y:auto">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach($daily as $d): ?>
          <tr>
            <td><?= date('D, d M Y',strtotime($d['day'])) ?></td>
            <td><span class="badge badge-gray"><?= $d['cnt'] ?></span></td>
            <td><strong class="text-accent"><?= formatPrice($d['rev']) ?></strong></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Payment methods -->
  <div class="card">
    <div class="card-title"><i class="fa-solid fa-credit-card"></i> Payment Methods</div>
    <?php if(empty($payBreak)): ?>
    <p class="text-muted" style="font-size:.85rem">No data.</p>
    <?php else: $maxRev=max(array_column($payBreak,'rev'))?:1; foreach($payBreak as $pm): $w=round(($pm['rev']/$maxRev)*100); ?>
    <div style="margin-bottom:1rem">
      <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem">
        <span><?= htmlspecialchars($pm['payment_method']) ?></span>
        <span class="text-accent fw-bold"><?= formatPrice($pm['rev']) ?></span>
      </div>
      <div style="height:6px;background:rgba(255,255,255,.07);border-radius:3px">
        <div style="height:100%;width:<?= $w ?>%;background:var(--accent);border-radius:3px;transition:width .5s"></div>
      </div>
      <div class="text-muted" style="font-size:.72rem;margin-top:2px"><?= $pm['cnt'] ?> order<?= $pm['cnt']!=1?'s':'' ?></div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- Full orders table -->
<div class="card">
  <div class="card-title"><i class="fa-solid fa-list"></i> Orders in Period</div>
  <?php if(empty($orders)): ?>
  <p class="text-muted">No orders found for this period and status.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach($orders as $o): ?>
        <tr>
          <td><strong class="text-accent">#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></strong></td>
          <td><?= htmlspecialchars($o['uname']) ?></td>
          <td class="text-muted" style="font-size:.8rem"><?= date('d M Y H:i',strtotime($o['created_at'])) ?></td>
          <td><strong><?= formatPrice($o['total']) ?></strong></td>
          <td class="text-muted" style="font-size:.8rem"><?= htmlspecialchars($o['payment_method']) ?></td>
          <td><span class="badge <?= $sc[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php include 'admin_footer.php'; ?>
