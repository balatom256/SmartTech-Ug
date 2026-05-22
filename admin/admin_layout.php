<?php
ob_start();
// admin/admin_layout.php  — include at top of every admin page
// Usage:  $pageTitle = 'Dashboard'; include 'admin_layout.php';
// Requires config.php to already be included.
requireAdmin();

// Sidebar active detection
$currentAdmin = basename($_SERVER['PHP_SELF']);

// Quick stats for sidebar
$pendingOrders = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$lowStock      = $conn->query("SELECT COUNT(*) c FROM products WHERE stock <= 5 AND stock > 0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — SmartTech-UG Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --black:#0a0a0a;--white:#f5f5f0;--accent:#00e5a0;--accent2:#ff6b35;
      --mid:#141414;--card:#1a1a1a;--text:#9a9a92;--border:rgba(255,255,255,0.07);
      --sidebar:220px;--font-head:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;
    }
    html{scroll-behavior:smooth}
    body{background:var(--black);color:var(--white);font-family:var(--font-body);font-size:15px;display:flex;min-height:100vh}
    a{text-decoration:none;color:inherit}
    img{max-width:100%}

    /* ── SIDEBAR ── */
    .sidebar{
      width:var(--sidebar);flex-shrink:0;background:var(--mid);
      border-right:1px solid var(--border);
      display:flex;flex-direction:column;
      position:fixed;top:0;left:0;bottom:0;
      overflow-y:auto;z-index:100;
    }
    .sidebar-logo{
      padding:1.5rem 1.4rem;border-bottom:1px solid var(--border);
      font-family:var(--font-head);font-size:1.2rem;font-weight:800;
    }
    .sidebar-logo span{color:var(--accent)}
    .sidebar-logo small{display:block;font-size:.65rem;font-weight:400;color:var(--text);letter-spacing:.08em;text-transform:uppercase;margin-top:2px}
    .sidebar-section{padding:.6rem 1rem .2rem;font-size:.65rem;font-weight:600;color:var(--text);letter-spacing:.1em;text-transform:uppercase;margin-top:.5rem}
    .sidebar-link{
      display:flex;align-items:center;gap:.75rem;
      padding:.7rem 1.4rem;color:var(--text);font-size:.88rem;
      transition:all .2s;position:relative;border-radius:0;
    }
    .sidebar-link i{width:16px;text-align:center;font-size:.9rem}
    .sidebar-link:hover{color:var(--white);background:rgba(255,255,255,.04)}
    .sidebar-link.active{color:var(--accent);background:rgba(0,229,160,.08)}
    .sidebar-link.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--accent);border-radius:0 2px 2px 0}
    .sidebar-badge{background:var(--accent2);color:var(--white);font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:50px;margin-left:auto}
    .sidebar-badge.green{background:var(--accent);color:var(--black)}
    .sidebar-footer{margin-top:auto;padding:1rem 1.4rem;border-top:1px solid var(--border)}
    .sidebar-user{display:flex;align-items:center;gap:.75rem}
    .user-avatar{width:34px;height:34px;border-radius:50%;background:rgba(0,229,160,.15);border:1px solid rgba(0,229,160,.3);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:var(--accent);flex-shrink:0}
    .user-name{font-size:.82rem;font-weight:600;color:var(--white)}
    .user-role{font-size:.7rem;color:var(--text)}

    /* ── MAIN ── */
    .main-content{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;min-height:100vh}

    /* ── TOP BAR ── */
    .topbar{
      background:var(--mid);border-bottom:1px solid var(--border);
      padding:.9rem 2rem;display:flex;align-items:center;
      justify-content:space-between;position:sticky;top:0;z-index:50;
    }
    .topbar-title{font-family:var(--font-head);font-size:1.1rem;font-weight:700}
    .topbar-actions{display:flex;gap:.8rem;align-items:center}
    .topbar-btn{
      background:transparent;border:1px solid var(--border);color:var(--text);
      padding:.45rem 1rem;border-radius:8px;font-family:var(--font-body);
      font-size:.82rem;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:6px;
    }
    .topbar-btn:hover{border-color:rgba(0,229,160,.4);color:var(--white)}
    .topbar-btn.primary{background:var(--accent);color:var(--black);border-color:var(--accent);font-weight:600}
    .topbar-btn.primary:hover{opacity:.88}

    /* ── PAGE BODY ── */
    .page-body{padding:2rem;flex:1}

    /* ── STAT CARDS ── */
    .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:2rem}
    .stat-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.4rem;transition:border-color .2s}
    .stat-card:hover{border-color:rgba(0,229,160,.25)}
    .stat-card-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem}
    .stat-icon{width:38px;height:38px;border-radius:10px;background:rgba(0,229,160,.1);display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:.95rem}
    .stat-icon.orange{background:rgba(255,107,53,.1);color:var(--accent2)}
    .stat-icon.blue{background:rgba(59,130,246,.1);color:#60a5fa}
    .stat-icon.purple{background:rgba(139,92,246,.1);color:#a78bfa}
    .stat-trend{font-size:.72rem;color:var(--accent);font-weight:600}
    .stat-trend.down{color:var(--accent2)}
    .stat-num{font-family:var(--font-head);font-size:1.7rem;font-weight:800;margin-bottom:.2rem}
    .stat-label{font-size:.78rem;color:var(--text)}

    /* ── CARDS ── */
    .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem}
    .card-title{font-family:var(--font-head);font-weight:700;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem}
    .card-title i{color:var(--accent)}

    /* ── TABLE ── */
    .table-wrap{overflow-x:auto}
    .data-table{width:100%;border-collapse:collapse;font-size:.85rem}
    .data-table th{color:var(--text);font-weight:600;padding:.65rem 1rem;text-align:left;border-bottom:1px solid var(--border);font-size:.72rem;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
    .data-table td{padding:.8rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle}
    .data-table tr:last-child td{border-bottom:none}
    .data-table tbody tr:hover td{background:rgba(255,255,255,.02)}

    /* ── FORMS ── */
    .form-group{margin-bottom:1.1rem}
    .form-label{display:block;font-size:.8rem;font-weight:500;color:var(--text);margin-bottom:.35rem;letter-spacing:.02em}
    .form-control{width:100%;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--white);padding:.65rem 1rem;border-radius:10px;font-family:var(--font-body);font-size:.9rem;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:rgba(0,229,160,.5);background:rgba(0,229,160,.03)}
    .form-control::placeholder{color:var(--text)}
    select.form-control option{background:#1a1a1a}
    textarea.form-control{resize:vertical;min-height:80px}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    .form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem}

    /* ── BADGES ── */
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:50px;font-size:.7rem;font-weight:700;white-space:nowrap}
    .badge-green{background:rgba(0,229,160,.12);color:var(--accent);border:1px solid rgba(0,229,160,.2)}
    .badge-orange{background:rgba(255,107,53,.12);color:var(--accent2);border:1px solid rgba(255,107,53,.2)}
    .badge-gray{background:rgba(255,255,255,.06);color:var(--text);border:1px solid var(--border)}
    .badge-blue{background:rgba(59,130,246,.12);color:#60a5fa;border:1px solid rgba(59,130,246,.2)}

    /* ── ALERTS ── */
    .alert{padding:.8rem 1.1rem;border-radius:10px;margin-bottom:1.2rem;font-size:.88rem;border:1px solid transparent;display:flex;align-items:center;gap:.6rem}
    .alert-success{background:rgba(0,229,160,.1);border-color:rgba(0,229,160,.25);color:var(--accent)}
    .alert-error{background:rgba(255,107,53,.1);border-color:rgba(255,107,53,.25);color:var(--accent2)}
    .alert-warning{background:rgba(250,204,21,.1);border-color:rgba(250,204,21,.25);color:#fde047}

    /* ── BUTTONS ── */
    .btn{display:inline-flex;align-items:center;gap:6px;padding:.55rem 1.1rem;border-radius:8px;font-family:var(--font-body);font-size:.82rem;cursor:pointer;border:1px solid transparent;transition:all .2s;font-weight:500}
    .btn-primary{background:var(--accent);color:var(--black);border-color:var(--accent);font-weight:700}
    .btn-primary:hover{opacity:.88;transform:translateY(-1px)}
    .btn-outline{background:transparent;color:var(--text);border-color:var(--border)}
    .btn-outline:hover{border-color:rgba(255,255,255,.25);color:var(--white)}
    .btn-danger{background:rgba(255,107,53,.12);color:var(--accent2);border-color:rgba(255,107,53,.25)}
    .btn-danger:hover{background:var(--accent2);color:var(--white)}
    .btn-sm{padding:.38rem .8rem;font-size:.78rem}
    .btn-lg{padding:.8rem 1.8rem;font-size:.95rem}

    /* ── IMAGE PREVIEW ── */
    .img-preview{width:60px;height:60px;border-radius:10px;object-fit:cover;background:rgba(255,255,255,.04);border:1px solid var(--border)}
    .img-preview-lg{width:100%;height:200px;border-radius:12px;object-fit:contain;background:rgba(255,255,255,.03);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:4rem;margin-bottom:1rem}
    .upload-zone{border:2px dashed var(--border);border-radius:12px;padding:2rem;text-align:center;transition:border-color .2s;cursor:pointer}
    .upload-zone:hover{border-color:rgba(0,229,160,.4);background:rgba(0,229,160,.03)}
    .upload-zone input[type=file]{display:none}
    .upload-zone i{font-size:2rem;color:var(--text);margin-bottom:.8rem;display:block}
    .upload-zone p{color:var(--text);font-size:.85rem}
    .upload-zone .accent{color:var(--accent)}

    /* ── TOGGLE SWITCH ── */
    .toggle-wrap{display:flex;align-items:center;gap:.7rem}
    .toggle{position:relative;width:40px;height:22px}
    .toggle input{opacity:0;width:0;height:0}
    .toggle-slider{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,.12);border-radius:50px;cursor:pointer;transition:.3s}
    .toggle-slider:before{content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:var(--text);border-radius:50%;transition:.3s}
    input:checked+.toggle-slider{background:var(--accent)}
    input:checked+.toggle-slider:before{transform:translateX(18px);background:var(--black)}
    .toggle-label{font-size:.85rem;color:var(--text)}

    /* ── PAGINATION ── */
    .pagination{display:flex;gap:6px;margin-top:1.2rem;flex-wrap:wrap}
    .page-btn{background:var(--card);border:1px solid var(--border);color:var(--text);padding:.4rem .8rem;border-radius:8px;font-size:.8rem;cursor:pointer;transition:all .2s}
    .page-btn:hover,.page-btn.active{background:var(--accent);color:var(--black);border-color:var(--accent);font-weight:700}

    /* ── MISC ── */
    .text-accent{color:var(--accent)}
    .text-muted{color:var(--text)}
    .text-orange{color:var(--accent2)}
    .fw-bold{font-weight:700}
    .font-head{font-family:var(--font-head)}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
    .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem}
    .mt-1{margin-top:.5rem}.mt-2{margin-top:1rem}.mt-3{margin-top:1.5rem}
    .mb-1{margin-bottom:.5rem}.mb-2{margin-bottom:1rem}.mb-3{margin-bottom:1.5rem}
    .flex{display:flex}.flex-center{align-items:center}.gap-1{gap:.5rem}.gap-2{gap:1rem}
    .justify-between{justify-content:space-between}

    @media(max-width:768px){
      .sidebar{transform:translateX(-100%);transition:transform .3s}
      .sidebar.open{transform:translateX(0)}
      .main-content{margin-left:0}
      .form-row,.form-row-3{grid-template-columns:1fr}
      .grid-2,.grid-3{grid-template-columns:1fr}
    }
  </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    Smart<span>Tech</span>-UG
    <small>Admin Panel</small>
  </div>

  <div class="sidebar-section">Main</div>
  <a href="index.php"       class="sidebar-link <?= $currentAdmin==='index.php'?'active':'' ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
  <a href="orders.php"      class="sidebar-link <?= $currentAdmin==='orders.php'?'active':'' ?>">
    <i class="fa-solid fa-bag-shopping"></i> Orders
    <?php if($pendingOrders>0): ?><span class="sidebar-badge"><?= $pendingOrders ?></span><?php endif; ?>
  </a>

  <div class="sidebar-section">Catalog</div>
  <a href="products.php"    class="sidebar-link <?= $currentAdmin==='products.php'?'active':'' ?>">
    <i class="fa-solid fa-box"></i> Products
    <?php if($lowStock>0): ?><span class="sidebar-badge green"><?= $lowStock ?> low</span><?php endif; ?>
  </a>
  <a href="categories.php"  class="sidebar-link <?= $currentAdmin==='categories.php'?'active':'' ?>"><i class="fa-solid fa-tags"></i> Categories</a>

  <div class="sidebar-section">People</div>
  <a href="users.php"       class="sidebar-link <?= $currentAdmin==='users.php'?'active':'' ?>"><i class="fa-solid fa-users"></i> Customers</a>

  <div class="sidebar-section">Reports</div>
  <a href="reports.php"     class="sidebar-link <?= $currentAdmin==='reports.php'?'active':'' ?>"><i class="fa-solid fa-chart-line"></i> Sales Report</a>

  <div class="sidebar-section">System</div>
  <a href="settings.php"    class="sidebar-link <?= $currentAdmin==='settings.php'?'active':'' ?>"><i class="fa-solid fa-gear"></i> Site Settings</a>
  <a href="../index.php"    class="sidebar-link" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Website</a>
  <a href="../logout.php"   class="sidebar-link text-orange"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['name'],0,1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars(explode(' ',$_SESSION['name'])[0]) ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
  </div>
</aside>

<!-- ── MAIN ── -->
<div class="main-content">
  <div class="topbar">
    <div class="flex flex-center gap-2">
      <button class="topbar-btn" onclick="document.getElementById('sidebar').classList.toggle('open')" style="display:none" id="menuBtn"><i class="fa-solid fa-bars"></i></button>
      <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Admin') ?></span>
    </div>
    <div class="topbar-actions">
      <a href="products.php?action=add" class="topbar-btn primary"><i class="fa-solid fa-plus"></i> Add Product</a>
      <a href="../index.php" class="topbar-btn" target="_blank"><i class="fa-solid fa-eye"></i> View Site</a>
    </div>
  </div>
  <div class="page-body">
<?php
// Helper: show alert from session
if(isset($_SESSION['admin_msg'])){
    $type = $_SESSION['admin_msg_type'] ?? 'success';
    echo '<div class="alert alert-'.$type.'"><i class="fa-solid fa-'.($type==='success'?'circle-check':'circle-exclamation').'"></i>'.htmlspecialchars($_SESSION['admin_msg']).'</div>';
    unset($_SESSION['admin_msg'],$_SESSION['admin_msg_type']);
}
?>
