<?php
require_once 'includes/config.php';
$pageTitle = 'Products – SmartTech-UG';

// ── Filters ──
$search   = sanitize($conn, $_GET['search'] ?? '');
$catSlug  = sanitize($conn, $_GET['category'] ?? '');
$sort     = sanitize($conn, $_GET['sort'] ?? 'newest');

$where = "p.stock > 0";
if($search)  $where .= " AND (p.name LIKE '%$search%' OR p.brand LIKE '%$search%')";
if($catSlug) $where .= " AND c.slug='$catSlug'";

$orderBy = match($sort){
  'price_asc'  => 'p.price ASC',
  'price_desc' => 'p.price DESC',
  'popular'    => 'p.featured DESC',
  default      => 'p.created_at DESC',
};

$products = $conn->query("SELECT p.*, c.name as cat_name, c.slug as cat_slug 
                          FROM products p JOIN categories c ON p.category_id=c.id 
                          WHERE $where ORDER BY $orderBy")->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get active category name for heading
$activeCatName = '';
if($catSlug){
  $row = $conn->query("SELECT name FROM categories WHERE slug='$catSlug'")->fetch_assoc();
  $activeCatName = $row['name'] ?? '';
}

include 'includes/header.php';
?>
<style>
.products-wrap{display:grid;grid-template-columns:240px 1fr;gap:2rem;padding:90px 5% 60px;min-height:80vh}
.sidebar{padding-top:10px}
.sidebar h3{font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:1.2rem;color:var(--white)}
.filter-group{margin-bottom:2rem}
.filter-group h4{font-size:.78rem;font-weight:600;color:var(--text);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.8rem}
.filter-link{display:block;padding:.45rem 0;color:var(--text);font-size:.88rem;transition:color .2s;text-decoration:none;border-bottom:none}
.filter-link:hover,.filter-link.active{color:var(--accent)}
.filter-link.active{font-weight:600}
.sort-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.8rem}
.sort-bar p{color:var(--text);font-size:.88rem}
.products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px}
.product-card{background:var(--card);border:1px solid var(--border);border-radius:18px;overflow:hidden;transition:border-color .25s,transform .25s}
.product-card:hover{border-color:rgba(0,229,160,.35);transform:translateY(-4px)}
.product-img{height:160px;background:rgba(255,255,255,.03);display:flex;align-items:center;justify-content:center;font-size:3.5rem;position:relative;border-bottom:1px solid var(--border)}
.p-badge{position:absolute;top:8px;left:8px;background:var(--accent);color:var(--black);font-size:.66rem;font-weight:700;padding:2px 9px;border-radius:50px}
.p-badge.nb{background:var(--accent2);color:var(--white)}
.wish-btn{position:absolute;top:8px;right:8px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:var(--text);font-size:.78rem;cursor:pointer;border:none;transition:all .2s}
.wish-btn:hover{background:rgba(255,107,53,.2);color:var(--accent2)}
.product-body{padding:1rem}
.p-brand{color:var(--accent);font-size:.7rem;font-weight:600;letter-spacing:.05em;margin-bottom:3px}
.p-name{font-family:var(--font-head);font-size:.88rem;font-weight:700;margin-bottom:8px;line-height:1.3}
.p-footer{display:flex;align-items:center;justify-content:space-between}
.p-price{font-family:var(--font-head);font-size:1rem;font-weight:800}
.p-old{font-size:.75rem;color:var(--text);text-decoration:line-through;font-weight:400;display:block}
.add-btn{background:rgba(0,229,160,.15);color:var(--accent);border:1px solid rgba(0,229,160,.3);width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.82rem;transition:all .2s}
.add-btn:hover{background:var(--accent);color:var(--black)}
.no-results{grid-column:1/-1;text-align:center;padding:4rem;color:var(--text)}
.no-results i{font-size:3rem;margin-bottom:1rem;color:var(--accent);opacity:.4;display:block}
@media(max-width:800px){.products-wrap{grid-template-columns:1fr}.sidebar{display:none}}
</style>

<div class="products-wrap">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <h3>Filter Products</h3>

    <div class="filter-group">
      <h4>Category</h4>
      <a href="products.php" class="filter-link <?= !$catSlug?'active':'' ?>">All Categories</a>
      <?php foreach($categories as $cat): ?>
      <a href="products.php?category=<?= $cat['slug'] ?><?= $search?"&search=$search":'' ?>" 
         class="filter-link <?= $catSlug===$cat['slug']?'active':'' ?>">
        <i class="<?= $cat['icon'] ?>" style="font-size:.85rem;margin-right:6px"></i><?= htmlspecialchars($cat['name']) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="filter-group">
      <h4>Sort By</h4>
      <?php foreach(['newest'=>'Newest First','price_asc'=>'Price: Low to High','price_desc'=>'Price: High to Low','popular'=>'Most Popular'] as $val=>$lbl): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['sort'=>$val])) ?>" 
         class="filter-link <?= $sort===$val?'active':'' ?>"><?= $lbl ?></a>
      <?php endforeach; ?>
    </div>
  </aside>

  <!-- MAIN -->
  <main>
    <!-- Search bar -->
    <form method="GET" style="display:flex;gap:10px;margin-bottom:1.5rem">
      <?php if($catSlug): ?><input type="hidden" name="category" value="<?= $catSlug ?>"/><?php endif; ?>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="🔍 Search products or brands…" class="form-control" style="border-radius:50px;padding:.7rem 1.3rem"/>
      <button class="btn-primary btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      <?php if($search): ?><a href="products.php<?= $catSlug?"?category=$catSlug":'' ?>" class="btn-outline btn-sm">Clear</a><?php endif; ?>
    </form>

    <div class="sort-bar">
      <p>
        <?php if($activeCatName) echo "<strong style='color:var(--white)'>$activeCatName</strong> &nbsp;·&nbsp; "; ?>
        <strong style="color:var(--white)"><?= count($products) ?></strong> product<?= count($products)!=1?'s':'' ?> found
        <?php if($search) echo " for <em style='color:var(--accent)'>\"$search\"</em>"; ?>
      </p>
    </div>

    <?php if(isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?>"><?= $_SESSION['msg'] ?></div>
    <?php unset($_SESSION['msg'],$_SESSION['msg_type']); endif; ?>

    <div class="products-grid">
    <?php if(empty($products)): ?>
      <div class="no-results">
        <i class="fa-solid fa-box-open"></i>
        <p>No products found. <a href="products.php" style="color:var(--accent)">Clear filters</a></p>
      </div>
    <?php else: foreach($products as $p):
      $inWish = false;
      if(isLoggedIn()){
        $uid=(int)$_SESSION['user_id']; $pid=(int)$p['id'];
        $inWish = $conn->query("SELECT id FROM wishlist WHERE user_id=$uid AND product_id=$pid")->num_rows > 0;
      }
    ?>
      <div class="product-card">
        <div class="product-img">
          <?php if($p['badge']): ?><span class="p-badge <?= strpos($p['badge'],'New')!==false?'nb':'' ?>"><?= $p['badge'] ?></span><?php endif; ?>
          <?php if($p['image'] && file_exists('assets/uploads/'.$p['image'])): ?>
          <img src="assets/uploads/<?= htmlspecialchars($p['image']) ?>" style="max-height:130px;max-width:90%;object-fit:contain"/>
          <?php else: ?>
          <div style="color:var(--text);font-size:.8rem;text-align:center">No image</div>
          <?php endif; ?>
          <form method="POST" action="wishlist.php" style="position:absolute;top:8px;right:8px">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="action" value="<?= $inWish?'remove':'add' ?>">
            <input type="hidden" name="redirect" value="products.php?category=<?= $catSlug ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">
            <button class="wish-btn" style="color:<?= $inWish?'var(--accent2)':'' ?>">
              <i class="fa-<?= $inWish?'solid':'regular' ?> fa-heart"></i>
            </button>
          </form>
        </div>
        <div class="product-body">
          <div class="p-brand"><?= htmlspecialchars($p['brand']) ?></div>
          <div class="p-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="p-footer">
            <div class="p-price">
              <?php if($p['old_price']): ?><span class="p-old"><?= formatPrice($p['old_price']) ?></span><?php endif; ?>
              <?= formatPrice($p['price']) ?>
            </div>
            <?php if($p['stock'] > 0): ?>
            <form method="POST" action="cart.php">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="redirect" value="products.php?category=<?= $catSlug ?>&search=<?= urlencode($search) ?>">
              <button class="add-btn" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </form>
            <?php else: ?>
            <span style="color:var(--accent2);font-size:.72rem">Out of stock</span>
            <?php endif; ?>
          </div>
          <?php if($p['stock'] <= 5 && $p['stock'] > 0): ?>
          <div style="color:var(--accent2);font-size:.72rem;margin-top:6px"><i class="fa-solid fa-fire-flame-curved"></i> Only <?= $p['stock'] ?> left!</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
    </div>
  </main>
</div>

<?php include 'includes/footer.php'; ?>
