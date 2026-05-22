<?php
require_once 'includes/config.php';
$pageTitle = "SmartTech-UG | Uganda's #1 Tech Marketplace";

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch featured products
$featured = $conn->query("SELECT p.*, c.name as cat_name, c.slug as cat_slug 
                          FROM products p JOIN categories c ON p.category_id=c.id 
                          WHERE p.featured=1 AND p.stock>0 
                          ORDER BY p.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Fetch deals (products with old_price)
$deals = $conn->query("SELECT p.*, c.name as cat_name 
                       FROM products p JOIN categories c ON p.category_id=c.id
                       WHERE p.old_price IS NOT NULL AND p.stock>0 
                       ORDER BY (p.old_price - p.price) DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<style>
/* HERO */
.hero{min-height:100vh;padding:120px 5% 80px;display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-200px;right:-200px;width:600px;height:600px;background:radial-gradient(circle,rgba(0,229,160,.12) 0%,transparent 70%);pointer-events:none}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(0,229,160,.1);border:1px solid rgba(0,229,160,.25);color:var(--accent);padding:6px 16px;border-radius:50px;font-size:.82rem;font-weight:500;margin-bottom:1.5rem}
.hero-badge .dot{width:7px;height:7px;background:var(--accent);border-radius:50%;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(1.4)}}
.hero h1{font-family:var(--font-head);font-size:clamp(2.8rem,5vw,4.5rem);font-weight:800;line-height:1.05;letter-spacing:-2px;margin-bottom:1.5rem}
.hero h1 em{color:var(--accent);font-style:normal}
.hero>div>p{color:var(--text);font-size:1.05rem;max-width:480px;margin-bottom:2.5rem;line-height:1.75}
.hero-cta{display:flex;gap:1rem;flex-wrap:wrap}
.hero-stats{display:flex;gap:2.5rem;margin-top:3rem}
.stat-num{font-family:var(--font-head);font-size:1.8rem;font-weight:800}
.stat-num span{color:var(--accent)}
.stat-label{color:var(--text);font-size:.82rem}

/* PHONE MOCK */
.hero-visual{position:relative;display:flex;align-items:center;justify-content:center}
.phone-mockup{width:280px;height:520px;background:var(--card);border-radius:40px;border:2px solid rgba(255,255,255,.1);padding:20px;box-shadow:0 40px 80px rgba(0,0,0,.5);animation:float 4s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-18px)}}
.phone-screen{background:#0d0d0d;border-radius:28px;height:100%;overflow:hidden;padding:16px}
.phone-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.phone-logo{font-family:var(--font-head);font-size:.8rem;font-weight:800;color:var(--accent)}
.phone-search{background:rgba(255,255,255,.06);border-radius:10px;padding:7px 12px;font-size:.7rem;color:var(--text);margin-bottom:12px}
.phone-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.phone-item{background:rgba(255,255,255,.04);border-radius:12px;padding:10px;border:1px solid var(--border)}
.phone-emoji{height:48px;background:rgba(0,229,160,.08);border-radius:8px;margin-bottom:7px;display:flex;align-items:center;justify-content:center;font-size:1.4rem}
.phone-pname{font-size:.6rem;font-weight:600;color:var(--white);margin-bottom:2px}
.phone-pprice{font-size:.65rem;color:var(--accent);font-weight:700}

/* FLOATING */
.floating-badge{position:absolute;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:10px 16px;display:flex;align-items:center;gap:8px;font-size:.8rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,.3)}
.fb1{top:50px;left:-80px;animation:floatB 3.5s .5s ease-in-out infinite}
.fb2{bottom:70px;right:-70px;animation:floatB 4s 1s ease-in-out infinite}
@keyframes floatB{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.fb-icon{width:32px;height:32px;border-radius:10px;background:rgba(0,229,160,.15);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0}

/* MARQUEE */
.marquee-wrap{border-top:1px solid var(--border);border-bottom:1px solid var(--border);background:rgba(255,255,255,.02);overflow:hidden;padding:14px 0}
.marquee-track{display:flex;gap:3rem;animation:marquee 20s linear infinite;width:max-content}
@keyframes marquee{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.marquee-item{display:flex;align-items:center;gap:10px;color:var(--text);font-size:.85rem;font-weight:500;white-space:nowrap}
.marquee-item i{color:var(--accent)}

/* CATEGORIES */
.categories-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-top:2.5rem}
.cat-card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:1.8rem 1.2rem;text-align:center;cursor:pointer;transition:border-color .25s,transform .25s,background .25s;display:block;color:inherit}
.cat-card:hover{border-color:rgba(0,229,160,.4);transform:translateY(-4px);background:rgba(0,229,160,.04)}
.cat-icon{width:54px;height:54px;background:rgba(0,229,160,.1);border-radius:14px;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:1.3rem}
.cat-name{font-family:var(--font-head);font-size:.9rem;font-weight:700;margin-bottom:3px}
.cat-count{color:var(--text);font-size:.75rem}

/* PRODUCTS */
.products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px;margin-top:2.5rem}
.product-card{background:var(--card);border:1px solid var(--border);border-radius:18px;overflow:hidden;transition:border-color .25s,transform .25s}
.product-card:hover{border-color:rgba(0,229,160,.35);transform:translateY(-4px)}
.product-img{height:170px;background:rgba(255,255,255,.03);display:flex;align-items:center;justify-content:center;font-size:3.8rem;position:relative;border-bottom:1px solid var(--border)}
.p-badge{position:absolute;top:10px;left:10px;background:var(--accent);color:var(--black);font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:50px}
.p-badge.new-badge{background:var(--accent2);color:var(--white)}
.wish-btn{position:absolute;top:10px;right:10px;width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:var(--text);font-size:.8rem;cursor:pointer;transition:all .2s;border:none}
.wish-btn:hover{background:rgba(255,107,53,.2);color:var(--accent2)}
.product-body{padding:1.1rem}
.p-brand{color:var(--accent);font-size:.72rem;font-weight:600;letter-spacing:.05em;margin-bottom:3px}
.p-name{font-family:var(--font-head);font-size:.92rem;font-weight:700;margin-bottom:7px;line-height:1.3}
.p-price{font-family:var(--font-head);font-size:1.05rem;font-weight:800}
.p-old{font-size:.78rem;color:var(--text);text-decoration:line-through;font-weight:400;margin-right:4px}
.p-footer{display:flex;align-items:center;justify-content:space-between;margin-top:10px}
.add-btn{background:rgba(0,229,160,.15);color:var(--accent);border:1px solid rgba(0,229,160,.3);width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.85rem;transition:all .2s}
.add-btn:hover{background:var(--accent);color:var(--black);transform:scale(1.1)}
.out-of-stock{color:var(--accent2);font-size:.78rem;font-weight:500}

/* DEALS */
.deals-grid{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-top:2.5rem}
.deal-card{background:var(--card);border:1px solid rgba(0,229,160,.2);border-radius:18px;padding:1.5rem;transition:border-color .2s,transform .2s;cursor:pointer}
.deal-card:hover{border-color:rgba(0,229,160,.5);transform:translateY(-3px)}
.discount-badge{background:var(--accent2);color:var(--white);font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:50px;display:inline-block;margin-bottom:.8rem}

@media(max-width:900px){
  .hero{grid-template-columns:1fr;text-align:center}
  .hero>div>p{margin:0 auto 2.5rem}
  .hero-cta{justify-content:center}
  .hero-stats{justify-content:center}
  .hero-visual{display:none}
  .deals-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:600px){
  .deals-grid{grid-template-columns:1fr}
  .hero h1{font-size:2.4rem}
}
</style>

<!-- HERO -->
<section class="hero">
  <div>
    <div class="hero-badge"><span class="dot"></span> Uganda's #1 Tech Marketplace</div>
    <h1>Shop Smarter,<br><em>Live Smarter</em><br>with Tech</h1>
    <p>Discover the latest smartphones, laptops, audio gear, and accessories. Fast delivery across Uganda. Authentic products. Unbeatable prices.</p>
    <div class="hero-cta">
      <a href="products.php" class="btn-primary"><i class="fa-solid fa-bolt"></i> Shop Now</a>
      <a href="#categories" class="btn-outline"><i class="fa-solid fa-grid-2"></i> Browse Categories</a>
    </div>
    <div class="hero-stats">
      <div>
        <div class="stat-num"><?= $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'] ?><span>+</span></div>
        <div class="stat-label">Products</div>
      </div>
      <div>
        <div class="stat-num">20K<span>+</span></div>
        <div class="stat-label">Happy Customers</div>
      </div>
      <div>
        <div class="stat-num">100<span>%</span></div>
        <div class="stat-label">Authentic</div>
      </div>
    </div>
  </div>

  <div class="hero-visual">
    <div class="phone-mockup">
      <div class="phone-screen">
        <div class="phone-header">
          <span class="phone-logo">SmartTech</span>
          <i class="fa-solid fa-cart-shopping" style="color:var(--accent);font-size:.85rem"></i>
        </div>
        <div class="phone-search">🔍 Search products...</div>
        <div class="phone-grid">
          <?php foreach(array_slice($featured,0,4) as $fp): ?>
          <div class="phone-item">
            <div class="phone-emoji"><?= $fp['emoji'] ?></div>
            <div class="phone-pname"><?= substr($fp['name'],0,14) ?>…</div>
            <div class="phone-pprice"><?= formatPrice($fp['price']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="floating-badge fb1">
      <div class="fb-icon"><i class="fa-solid fa-truck"></i></div>
      <div><div style="font-weight:600;font-size:.8rem">Free Delivery</div><div style="color:var(--text);font-size:.72rem">Orders over UGX 500K</div></div>
    </div>
    <div class="floating-badge fb2">
      <div class="fb-icon"><i class="fa-solid fa-shield-check"></i></div>
      <div><div style="font-weight:600;font-size:.8rem">2-Year Warranty</div><div style="color:var(--text);font-size:.72rem">All electronics</div></div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-wrap">
  <div class="marquee-track">
    <?php $brands = ['Samsung Official Reseller','Apple Authorized Dealer','Sony Audio Partner','HP & Dell Partner','Free Kampala Delivery','MTN & Airtel MoMo'];
    foreach(array_merge($brands,$brands) as $b): ?>
    <div class="marquee-item"><i class="fa-solid fa-star"></i> <?= $b ?></div>
    <?php endforeach; ?>
  </div>
</div>

<!-- CATEGORIES -->
<section id="categories" style="padding:80px 5%">
  <div class="section-label">Browse</div>
  <h2 class="section-title">Shop by Category</h2>
  <div class="categories-grid">
    <?php foreach($categories as $cat):
      $cnt = $conn->query("SELECT COUNT(*) c FROM products WHERE category_id={$cat['id']} AND stock>0")->fetch_assoc()['c'];
    ?>
    <a href="products.php?category=<?= $cat['slug'] ?>" class="cat-card">
      <div class="cat-icon"><i class="<?= $cat['icon'] ?>"></i></div>
      <div class="cat-name"><?= htmlspecialchars($cat['name']) ?></div>
      <div class="cat-count"><?= $cnt ?>+ products</div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section style="padding:0 5% 80px">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;margin-bottom:.5rem">
    <div>
      <div class="section-label">Collection</div>
      <h2 class="section-title">Featured Products</h2>
    </div>
    <a href="products.php" class="btn-outline btn-sm">View All <i class="fa-solid fa-arrow-right"></i></a>
  </div>

  <?php if(isset($_SESSION['msg'])): ?>
  <div class="alert alert-<?= $_SESSION['msg_type'] ?>"><?= $_SESSION['msg'] ?></div>
  <?php unset($_SESSION['msg'],$_SESSION['msg_type']); endif; ?>

  <div class="products-grid">
    <?php foreach($featured as $p):
      $inWish = false;
      if(isLoggedIn()){
        $uid=(int)$_SESSION['user_id']; $pid=(int)$p['id'];
        $inWish = $conn->query("SELECT id FROM wishlist WHERE user_id=$uid AND product_id=$pid")->num_rows > 0;
      }
    ?>
    <div class="product-card">
      <div class="product-img">
        <?php if($p['badge']): ?><span class="p-badge <?= strpos($p['badge'],'New')!==false?'new-badge':'' ?>"><?= $p['badge'] ?></span><?php endif; ?>
        <?= $p['emoji'] ?>
        <form method="POST" action="wishlist.php" style="position:absolute;top:10px;right:10px">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <input type="hidden" name="action" value="<?= $inWish?'remove':'add' ?>">
          <input type="hidden" name="redirect" value="index.php">
          <button class="wish-btn" title="<?= $inWish?'Remove from':'Add to' ?> wishlist" style="color:<?= $inWish?'var(--accent2)':'' ?>">
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
            <input type="hidden" name="redirect" value="index.php">
            <button class="add-btn" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
          </form>
          <?php else: ?>
          <span class="out-of-stock">Out of stock</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- HOT DEALS -->
<?php if($deals): ?>
<section style="padding:0 5% 80px;background:rgba(0,229,160,.03);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div style="padding-top:80px">
  <div class="section-label">Limited Time</div>
  <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem">
    <h2 class="section-title">Hot Deals Today</h2>
    <div style="display:flex;gap:1rem">
      <?php foreach(['Hours'=>'08','Mins'=>'34','Secs'=>'22'] as $lbl=>$val): ?>
      <div style="background:var(--card);border-radius:12px;padding:.9rem 1.4rem;text-align:center;border:1px solid var(--border)">
        <span class="stat-num" id="t<?= strtolower($lbl[0]) ?>"><?= $val ?></span>
        <div style="font-size:.72rem;color:var(--text);text-transform:uppercase;letter-spacing:.1em"><?= $lbl ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="deals-grid">
    <?php foreach($deals as $d):
      $disc = round((($d['old_price'] - $d['price']) / $d['old_price']) * 100);
    ?>
    <a href="products.php?id=<?= $d['id'] ?>" class="deal-card" style="text-decoration:none;color:inherit">
      <div class="discount-badge"><?= $disc ?>% OFF</div>
      <div style="font-size:2.5rem;margin-bottom:.8rem"><?= $d['emoji'] ?></div>
      <div style="font-family:var(--font-head);font-size:.92rem;font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($d['name']) ?></div>
      <div style="color:var(--text);font-size:.8rem;text-decoration:line-through;margin-bottom:3px"><?= formatPrice($d['old_price']) ?></div>
      <div style="font-family:var(--font-head);font-weight:800;color:var(--accent);font-size:1.05rem"><?= formatPrice($d['price']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
  </div>
</section>
<?php endif; ?>

<!-- WHY US -->
<section style="padding:80px 5%">
  <div class="section-label">Why Us</div>
  <h2 class="section-title">Built for Ugandan Tech Lovers</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-top:2.5rem">
    <?php foreach([
      ['fa-shield-check','100% Authentic','Every product sourced from official distributors. Full warranty included.'],
      ['fa-truck-fast','Same-Day Delivery','Order by 1PM, receive in Kampala same day. Nationwide in 48hrs.'],
      ['fa-mobile-signal','Mobile Money','Pay with MTN MoMo, Airtel Money, VISA or Mastercard seamlessly.'],
      ['fa-headset','24/7 Support','WhatsApp, call, or chat. Our team is always ready to help you.'],
    ] as [$icon,$title,$desc]): ?>
    <div class="card" style="display:flex;flex-direction:column;gap:1rem">
      <div style="width:48px;height:48px;border-radius:14px;background:rgba(0,229,160,.1);border:1px solid rgba(0,229,160,.2);display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:1.2rem">
        <i class="fa-solid <?= $icon ?>"></i>
      </div>
      <div>
        <div style="font-family:var(--font-head);font-weight:700;margin-bottom:.3rem"><?= $title ?></div>
        <div style="color:var(--text);font-size:.88rem;line-height:1.65"><?= $desc ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
// Countdown timer
const d = new Date(Date.now() + (8*3600+34*60+22)*1000);
function tick(){
  const diff = Math.max(0,d-Date.now());
  const h=Math.floor(diff/3600000),m=Math.floor((diff%3600000)/60000),s=Math.floor((diff%60000)/1000);
  const th=document.getElementById('th'),tm=document.getElementById('tm'),ts=document.getElementById('ts');
  if(th)th.textContent=String(h).padStart(2,'0');
  if(tm)tm.textContent=String(m).padStart(2,'0');
  if(ts)ts.textContent=String(s).padStart(2,'0');
}
tick(); setInterval(tick,1000);
</script>

<?php include 'includes/footer.php'; ?>
