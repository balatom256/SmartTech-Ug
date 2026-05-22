<?php
// includes/header.php
$cartCount   = getCartCount($conn);
$currentPage = basename($_SERVER['PHP_SELF']);
$siteName    = getSetting($conn, 'site_name', SITE_NAME);
$faviconFile = getSetting($conn, 'favicon_file', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle ?? $siteName) ?></title>
  <?php if($faviconFile && file_exists($_SERVER['DOCUMENT_ROOT'].'/smarttech-ug/assets/'.$faviconFile)): ?>
  <link rel="icon" href="<?= SITE_URL ?>/assets/<?= htmlspecialchars($faviconFile) ?>"/>
  <?php endif; ?>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--black:#0a0a0a;--white:#f5f5f0;--accent:#00e5a0;--accent2:#ff6b35;--mid:#1a1a1a;--card:#141414;--text:#b0b0a8;--border:rgba(255,255,255,0.08);--font-head:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;}
    html{scroll-behavior:smooth}
    body{background:var(--black);color:var(--white);font-family:var(--font-body);font-size:16px;line-height:1.6;overflow-x:hidden}
    a{text-decoration:none;color:inherit} img{max-width:100%;height:auto}
    .alert{padding:.85rem 1.2rem;border-radius:10px;margin-bottom:1.2rem;font-size:.9rem;border:1px solid transparent;display:flex;align-items:center;gap:.6rem}
    .alert-success{background:rgba(0,229,160,.1);border-color:rgba(0,229,160,.25);color:var(--accent)}
    .alert-error{background:rgba(255,107,53,.1);border-color:rgba(255,107,53,.25);color:var(--accent2)}
    nav{position:fixed;top:0;left:0;right:0;z-index:999;background:rgba(10,10,10,.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);padding:0 5%;height:68px;display:flex;align-items:center;justify-content:space-between}
    .logo{font-family:var(--font-head);font-size:1.5rem;font-weight:800;color:var(--white)}
    .logo span{color:var(--accent)}
    .nav-links{display:flex;gap:2rem;list-style:none}
    .nav-links a{color:var(--text);font-size:.9rem;font-weight:500;transition:color .2s}
    .nav-links a:hover,.nav-links a.active{color:var(--accent)}
    .nav-actions{display:flex;gap:1rem;align-items:center}
    .nav-icon{color:var(--text);font-size:1.1rem;cursor:pointer;transition:color .2s;position:relative}
    .nav-icon:hover{color:var(--white)}
    .cart-badge{position:absolute;top:-7px;right:-7px;background:var(--accent);color:var(--black);font-size:.62rem;font-weight:700;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center}
    .btn-nav{background:var(--accent);color:var(--black);border:none;padding:.55rem 1.3rem;border-radius:50px;font-weight:600;font-family:var(--font-body);font-size:.88rem;cursor:pointer;transition:opacity .2s}
    .btn-nav:hover{opacity:.88}
    .btn-nav-outline{background:transparent;color:var(--white);border:1px solid var(--border);padding:.5rem 1.2rem;border-radius:50px;font-weight:500;font-family:var(--font-body);font-size:.88rem;transition:border-color .2s}
    .btn-nav-outline:hover{border-color:rgba(255,255,255,.3)}
    .hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;background:none;border:none;padding:4px}
    .hamburger span{display:block;width:24px;height:2px;background:var(--white);border-radius:2px}
    .mobile-menu{display:none;position:fixed;top:68px;left:0;right:0;bottom:0;background:rgba(10,10,10,.97);z-index:998;flex-direction:column;padding:2rem 5%;overflow-y:auto}
    .mobile-menu.open{display:flex}
    .mobile-menu a{color:var(--white);font-size:1.2rem;font-weight:600;padding:1rem 0;border-bottom:1px solid var(--border)}
    .mobile-menu a:hover{color:var(--accent)}
    .btn-primary{background:var(--accent);color:var(--black);border:none;padding:.85rem 2rem;border-radius:50px;font-weight:700;font-family:var(--font-body);font-size:.95rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:transform .2s,box-shadow .2s}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,229,160,.25)}
    .btn-outline{background:transparent;color:var(--white);border:1px solid var(--border);padding:.85rem 2rem;border-radius:50px;font-weight:500;font-family:var(--font-body);font-size:.95rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:border-color .2s,background .2s}
    .btn-outline:hover{border-color:rgba(255,255,255,.3);background:rgba(255,255,255,.04)}
    .btn-sm{padding:.5rem 1.2rem!important;font-size:.82rem!important}
    .btn-danger{background:rgba(255,107,53,.15);color:var(--accent2);border:1px solid rgba(255,107,53,.3)}
    .btn-danger:hover{background:var(--accent2);color:var(--white)}
    .form-group{margin-bottom:1.2rem}
    .form-group label{display:block;font-size:.85rem;font-weight:500;color:var(--text);margin-bottom:.4rem}
    .form-control{width:100%;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--white);padding:.75rem 1rem;border-radius:10px;font-family:var(--font-body);font-size:.95rem;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:rgba(0,229,160,.5)}
    .form-control::placeholder{color:var(--text)}
    select.form-control option{background:var(--mid)}
    .card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:1.8rem}
    .page-wrap{padding:100px 5% 60px;min-height:80vh}
    .section-label{display:inline-flex;align-items:center;gap:8px;color:var(--accent);font-size:.8rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;margin-bottom:1rem}
    .section-label::before{content:'';width:24px;height:2px;background:var(--accent)}
    .section-title{font-family:var(--font-head);font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;line-height:1.1;letter-spacing:-1.5px;margin-bottom:1rem}
    .data-table{width:100%;border-collapse:collapse;font-size:.88rem}
    .data-table th{color:var(--text);font-weight:600;padding:.7rem 1rem;text-align:left;border-bottom:1px solid var(--border);font-size:.78rem;letter-spacing:.05em;text-transform:uppercase}
    .data-table td{padding:.85rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle}
    .data-table tr:last-child td{border-bottom:none}
    .data-table tbody tr:hover td{background:rgba(255,255,255,.02)}
    .badge{display:inline-block;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:700}
    .badge-accent{background:rgba(0,229,160,.15);color:var(--accent)}
    .badge-orange{background:rgba(255,107,53,.15);color:var(--accent2)}
    .badge-gray{background:rgba(255,255,255,.08);color:var(--text)}
    /* WhatsApp float */
    .wa-float{position:fixed;bottom:28px;right:28px;z-index:990}
    .wa-btn{width:56px;height:56px;border-radius:50%;background:#25D366;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.7rem;box-shadow:0 4px 20px rgba(37,211,102,.5);transition:transform .2s,box-shadow .2s;cursor:pointer}
    .wa-btn:hover{transform:scale(1.1);box-shadow:0 8px 28px rgba(37,211,102,.6)}
    .wa-tooltip{position:absolute;bottom:66px;right:0;background:var(--card);border:1px solid var(--border);color:var(--white);padding:.45rem .9rem;border-radius:50px;font-size:.78rem;white-space:nowrap;opacity:0;transition:opacity .2s;pointer-events:none}
    .wa-float:hover .wa-tooltip{opacity:1}
    @media(max-width:900px){.nav-links,.btn-nav,.nav-icon{display:none}.hamburger{display:flex}}
  </style>
</head>
<body>

<nav>
  <?= renderLogo($conn) ?>
  <ul class="nav-links">
    <li><a href="<?= SITE_URL ?>/index.php"    class="<?= $currentPage==='index.php'   ?'active':'' ?>">Home</a></li>
    <li><a href="<?= SITE_URL ?>/products.php" class="<?= $currentPage==='products.php'?'active':'' ?>">Products</a></li>
    <li><a href="<?= SITE_URL ?>/products.php?sort=price_asc">Deals</a></li>
    <?php if(isAdmin()): ?>
    <li><a href="<?= SITE_URL ?>/admin/index.php" style="color:var(--accent)"><i class="fa-solid fa-gauge-high"></i> Admin</a></li>
    <?php endif; ?>
  </ul>
  <div class="nav-actions">
    <a href="<?= SITE_URL ?>/products.php" class="nav-icon"><i class="fa-regular fa-magnifying-glass"></i></a>
    <?php if(isLoggedIn()): ?>
    <a href="<?= SITE_URL ?>/wishlist.php" class="nav-icon"><i class="fa-regular fa-heart"></i></a>
    <a href="<?= SITE_URL ?>/cart.php" class="nav-icon">
      <i class="fa-regular fa-cart-shopping"></i>
      <?php if($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
    </a>
    <a href="<?= SITE_URL ?>/orders.php" class="btn-nav-outline"><?= htmlspecialchars(explode(' ',$_SESSION['name'])[0]) ?></a>
    <a href="<?= SITE_URL ?>/logout.php" class="btn-nav">Logout</a>
    <?php else: ?>
    <a href="<?= SITE_URL ?>/login.php"    class="btn-nav-outline">Sign In</a>
    <a href="<?= SITE_URL ?>/register.php" class="btn-nav">Register</a>
    <?php endif; ?>
    <button class="hamburger" onclick="document.getElementById('mobileMenu').classList.toggle('open')">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <a href="<?= SITE_URL ?>/index.php">Home</a>
  <a href="<?= SITE_URL ?>/products.php">Products</a>
  <a href="<?= SITE_URL ?>/cart.php">Cart (<?= $cartCount ?>)</a>
  <?php if(isLoggedIn()): ?>
  <a href="<?= SITE_URL ?>/orders.php">My Orders</a>
  <a href="<?= SITE_URL ?>/wishlist.php">Wishlist</a>
  <?php if(isAdmin()): ?><a href="<?= SITE_URL ?>/admin/index.php" style="color:var(--accent)">Admin Panel</a><?php endif; ?>
  <a href="<?= SITE_URL ?>/logout.php">Logout</a>
  <?php else: ?>
  <a href="<?= SITE_URL ?>/login.php">Sign In</a>
  <a href="<?= SITE_URL ?>/register.php">Register</a>
  <?php endif; ?>
</div>
