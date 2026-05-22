<?php
ob_start();
require_once '../includes/config.php';
$pageTitle = 'Products';

$UPLOAD_DIR = '../assets/uploads/';
if(!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);

// ── DELETE ──
if(isset($_GET['delete'])){
    $pid = (int)$_GET['delete'];
    $img = $conn->query("SELECT image FROM products WHERE id=$pid")->fetch_assoc()['image'] ?? '';
    if($img && file_exists($UPLOAD_DIR.$img)) unlink($UPLOAD_DIR.$img);
    $conn->query("DELETE FROM products WHERE id=$pid");
    $_SESSION['admin_msg']='Product deleted.';
    $_SESSION['admin_msg_type']='success';
    header('Location: products.php'); exit;
}

// ── ADD / EDIT SAVE ──
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_product'])){
    $pid   = (int)($_POST['id'] ?? 0);
    $name  = sanitize($conn, $_POST['name']);
    $brand = sanitize($conn, $_POST['brand']);
    $cat   = (int)$_POST['category_id'];
    $desc  = sanitize($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $oldP  = $_POST['old_price'] !== '' ? (float)$_POST['old_price'] : null;
    $stock = (int)$_POST['stock'];
    $badge = sanitize($conn, $_POST['badge']);
    $feat  = isset($_POST['featured']) ? 1 : 0;
    $imgField = sanitize($conn, $_POST['current_image'] ?? '');

    // ── Handle image upload ──
    if(!empty($_FILES['image']['name'])){
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if(!in_array($ext, $allowed)){
            $_SESSION['admin_msg'] = 'Only JPG, PNG, WEBP images allowed.';
            $_SESSION['admin_msg_type'] = 'error';
            header('Location: products.php'.($pid?"?edit=$pid":'')); exit;
        }
        if($_FILES['image']['size'] > 5 * 1024 * 1024){
            $_SESSION['admin_msg'] = 'Image must be smaller than 5MB.';
            $_SESSION['admin_msg_type'] = 'error';
            header('Location: products.php'.($pid?"?edit=$pid":'')); exit;
        }
        $newName = 'product_'.time().'_'.rand(100,999).'.'.$ext;
        if(move_uploaded_file($_FILES['image']['tmp_name'], $UPLOAD_DIR.$newName)){
            if($imgField && file_exists($UPLOAD_DIR.$imgField)) unlink($UPLOAD_DIR.$imgField);
            $imgField = $newName;
        } else {
            $_SESSION['admin_msg'] = 'Image upload failed. Check folder permissions.';
            $_SESSION['admin_msg_type'] = 'error';
            header('Location: products.php'.($pid?"?edit=$pid":'')); exit;
        }
    }

    $imgField = sanitize($conn, $imgField);
    $oldVal   = $oldP !== null ? $oldP : 'NULL';

    if(!$name || !$price || !$cat){
        $_SESSION['admin_msg'] = 'Product name, category and price are required.';
        $_SESSION['admin_msg_type'] = 'error';
        header('Location: products.php'.($pid?"?edit=$pid":'')); exit;
    }

    if($pid){
        $conn->query("UPDATE products SET name='$name',brand='$brand',category_id=$cat,description='$desc',price=$price,old_price=$oldVal,stock=$stock,badge='$badge',featured=$feat,image='$imgField',emoji='' WHERE id=$pid");
        $_SESSION['admin_msg'] = 'Product updated successfully.';
    } else {
        $conn->query("INSERT INTO products (name,brand,category_id,description,price,old_price,stock,badge,featured,image,emoji) VALUES('$name','$brand',$cat,'$desc',$price,$oldVal,$stock,'$badge',$feat,'$imgField','')");
        $_SESSION['admin_msg'] = 'Product added successfully.';
    }
    $_SESSION['admin_msg_type'] = 'success';
    header('Location: products.php'); exit;
}

// ── LOAD DATA ──
$editProduct = null;
if(isset($_GET['edit'])){
    $editProduct = $conn->query("SELECT * FROM products WHERE id=".(int)$_GET['edit'])->fetch_assoc();
}
$showForm = (isset($_GET['action']) && $_GET['action']==='add') || $editProduct;

$search = sanitize($conn, $_GET['s'] ?? '');
$catF   = (int)($_GET['cat'] ?? 0);
$where  = "1=1";
if($search) $where .= " AND (p.name LIKE '%$search%' OR p.brand LIKE '%$search%')";
if($catF)   $where .= " AND p.category_id=$catF";

$products   = $conn->query("SELECT p.*,c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE $where ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

include 'admin_layout.php';
?>

<style>
.prod-img-box{width:60px;height:60px;border-radius:10px;object-fit:cover;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.prod-img-box img{width:100%;height:100%;object-fit:cover;border-radius:10px}
.no-img{width:60px;height:60px;border-radius:10px;background:rgba(255,255,255,.04);border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;color:var(--text);font-size:.65rem;text-align:center;line-height:1.2}
.upload-zone{border:2px dashed var(--border);border-radius:12px;padding:2rem;text-align:center;transition:border-color .2s,background .2s;cursor:pointer;display:block;margin-top:.4rem}
.upload-zone:hover{border-color:rgba(0,229,160,.5);background:rgba(0,229,160,.03)}
.upload-zone input{display:none}
.upload-zone i{font-size:2rem;color:var(--text);display:block;margin-bottom:.6rem}
.upload-zone p{color:var(--text);font-size:.82rem;margin-bottom:.2rem}
.upload-zone .accent{color:var(--accent);font-weight:600}
.img-preview-box{width:100%;height:200px;border-radius:12px;object-fit:contain;background:rgba(255,255,255,.03);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;margin-bottom:.8rem;overflow:hidden}
.img-preview-box img{max-height:190px;max-width:100%;object-fit:contain}
.img-preview-box .placeholder{color:var(--text);font-size:.85rem;text-align:center;padding:1rem}
.toggle-wrap{display:flex;align-items:center;gap:.7rem}
.toggle{position:relative;width:40px;height:22px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:rgba(255,255,255,.12);border-radius:50px;cursor:pointer;transition:.3s}
.toggle-slider:before{content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:var(--text);border-radius:50%;transition:.3s}
input:checked+.toggle-slider{background:var(--accent)}
input:checked+.toggle-slider:before{transform:translateX(18px);background:var(--black)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
@media(max-width:700px){.form-row{grid-template-columns:1fr}}
</style>

<div style="display:grid;grid-template-columns:<?= $showForm?'1fr 370px':'1fr' ?>;gap:1.5rem;align-items:start">

  <!-- ── PRODUCT LIST ── -->
  <div>
    <div class="card">
      <div class="card-title flex justify-between flex-center" style="flex-wrap:wrap;gap:.5rem">
        <span><i class="fa-solid fa-box"></i> All Products (<?= count($products) ?>)</span>
        <a href="products.php?action=add" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add New Product</a>
      </div>

      <!-- Search / Filter -->
      <form method="GET" style="display:flex;gap:8px;margin-bottom:1.2rem;flex-wrap:wrap">
        <input type="text" name="s" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search product name or brand…"
               class="form-control" style="flex:1;min-width:160px;border-radius:8px;padding:.5rem .9rem"/>
        <select name="cat" class="form-control" style="width:auto;min-width:140px;border-radius:8px;padding:.5rem .8rem">
          <option value="">All Categories</option>
          <?php foreach($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $catF==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        <?php if($search||$catF): ?>
        <a href="products.php" class="btn btn-outline btn-sm">Clear</a>
        <?php endif; ?>
      </form>

      <!-- Table -->
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Photo</th>
              <th>Product</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Featured</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($products)): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--text);padding:2rem">
              No products found. <a href="products.php?action=add" style="color:var(--accent)">Add your first product →</a>
            </td></tr>
          <?php else: foreach($products as $p): ?>
            <tr>
              <td>
                <?php if($p['image'] && file_exists('../assets/uploads/'.$p['image'])): ?>
                  <div class="prod-img-box">
                    <img src="../assets/uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"/>
                  </div>
                <?php else: ?>
                  <div class="no-img">No<br>Photo</div>
                <?php endif; ?>
              </td>
              <td>
                <div class="fw-bold" style="font-size:.85rem"><?= htmlspecialchars($p['name']) ?></div>
                <div class="text-muted" style="font-size:.74rem;margin-top:2px"><?= htmlspecialchars($p['brand']) ?></div>
                <?php if($p['badge']): ?>
                <span class="badge badge-orange" style="margin-top:4px"><?= htmlspecialchars($p['badge']) ?></span>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-gray"><?= htmlspecialchars($p['cat_name']) ?></span></td>
              <td>
                <div class="fw-bold text-accent"><?= formatPrice($p['price']) ?></div>
                <?php if($p['old_price']): ?>
                <div class="text-muted" style="font-size:.72rem;text-decoration:line-through"><?= formatPrice($p['old_price']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-weight:700;color:<?= $p['stock']==0?'var(--accent2)':($p['stock']<=5?'#fde047':'var(--text)') ?>">
                  <?= $p['stock']==0 ? 'OUT OF STOCK' : $p['stock'] ?>
                </span>
              </td>
              <td style="text-align:center">
                <?= $p['featured'] ? '<i class="fa-solid fa-star" style="color:var(--accent)"></i>' : '<span class="text-muted">—</span>' ?>
              </td>
              <td>
                <div style="display:flex;gap:6px">
                  <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="Edit">
                    <i class="fa-solid fa-pen"></i> Edit
                  </a>
                  <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
                     data-confirm="Delete '<?= htmlspecialchars($p['name']) ?>'? This cannot be undone." title="Delete">
                    <i class="fa-solid fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── ADD / EDIT FORM ── -->
  <?php if($showForm): ?>
  <div class="card" style="position:sticky;top:10px;max-height:calc(100vh - 20px);overflow-y:auto">
    <div class="card-title">
      <i class="fa-solid fa-<?= $editProduct?'pen':'plus' ?>"></i>
      <?= $editProduct ? 'Edit Product' : 'Add New Product' ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <?php if($editProduct): ?>
      <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
      <?php endif; ?>
      <input type="hidden" name="current_image" value="<?= htmlspecialchars($editProduct['image'] ?? '') ?>">
      <input type="hidden" name="save_product" value="1">

      <!-- PHOTO UPLOAD -->
      <div class="form-group">
        <label class="form-label">Product Photo</label>

        <!-- Preview box -->
        <div class="img-preview-box" id="imgPreviewBox">
          <?php if($editProduct && $editProduct['image'] && file_exists('../assets/uploads/'.$editProduct['image'])): ?>
            <img src="../assets/uploads/<?= htmlspecialchars($editProduct['image']) ?>" id="imgPreviewEl"/>
          <?php else: ?>
            <div class="placeholder">
              <i class="fa-solid fa-image" style="font-size:2.5rem;color:var(--border);display:block;margin-bottom:.5rem"></i>
              No photo uploaded yet
            </div>
          <?php endif; ?>
        </div>

        <!-- Upload zone -->
        <label class="upload-zone" for="imgFile">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <p><span class="accent">Click here to upload a photo</span></p>
          <p>or drag and drop your image here</p>
          <p style="margin-top:.4rem;font-size:.74rem">JPG, PNG, WEBP — max 5MB</p>
          <input type="file" id="imgFile" name="image" accept="image/jpeg,image/png,image/webp,image/gif"/>
        </label>
      </div>

      <!-- PRODUCT DETAILS -->
      <div class="form-group">
        <label class="form-label">Product Name *</label>
        <input class="form-control" name="name" required
               value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>"
               placeholder="e.g. Samsung Galaxy S25 Ultra"/>
      </div>

      <div class="form-group">
        <label class="form-label">Brand</label>
        <input class="form-control" name="brand"
               value="<?= htmlspecialchars($editProduct['brand'] ?? '') ?>"
               placeholder="e.g. Samsung, Apple, Sony"/>
      </div>

      <div class="form-group">
        <label class="form-label">Category *</label>
        <select class="form-control" name="category_id" required>
          <option value="">Select a category…</option>
          <?php foreach($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"
            <?= ($editProduct['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3"
                  placeholder="Describe this product — specs, features, what's in the box…"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
      </div>

      <!-- PRICE -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Selling Price (UGX) *</label>
          <input class="form-control" type="number" name="price" required min="0"
                 value="<?= $editProduct['price'] ?? '' ?>"
                 placeholder="e.g. 1500000"/>
        </div>
        <div class="form-group">
          <label class="form-label">Original Price (for discount)</label>
          <input class="form-control" type="number" name="old_price" min="0"
                 value="<?= $editProduct['old_price'] ?? '' ?>"
                 placeholder="Leave blank if no discount"/>
        </div>
      </div>

      <!-- STOCK & BADGE -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Stock Quantity *</label>
          <input class="form-control" type="number" name="stock" required min="0"
                 value="<?= $editProduct['stock'] ?? 0 ?>"
                 placeholder="How many do you have?"/>
        </div>
        <div class="form-group">
          <label class="form-label">Badge Label</label>
          <input class="form-control" name="badge"
                 value="<?= htmlspecialchars($editProduct['badge'] ?? '') ?>"
                 placeholder="e.g. New, Hot, 20% Off"/>
        </div>
      </div>

      <!-- FEATURED -->
      <div class="form-group">
        <div class="toggle-wrap">
          <label class="toggle">
            <input type="checkbox" name="featured" value="1"
                   <?= ($editProduct['featured'] ?? 0) ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
          <span class="toggle-label">Show this product on the homepage</span>
        </div>
      </div>

      <!-- SUBMIT -->
      <div style="display:flex;gap:8px;margin-top:.5rem">
        <button class="btn btn-primary" style="flex:1;justify-content:center;padding:.8rem" type="submit">
          <i class="fa-solid fa-<?= $editProduct?'floppy-disk':'plus' ?>"></i>
          <?= $editProduct ? 'Update Product' : 'Add Product' ?>
        </button>
        <a href="products.php" class="btn btn-outline" style="padding:.8rem 1.2rem">Cancel</a>
      </div>
    </form>
  </div>
  <?php endif; ?>

</div>

<script>
// Live image preview when user selects a file
document.getElementById('imgFile').addEventListener('change', function(){
  if(this.files && this.files[0]){
    const reader = new FileReader();
    reader.onload = function(e){
      document.getElementById('imgPreviewBox').innerHTML =
        '<img src="'+e.target.result+'" style="max-height:190px;max-width:100%;object-fit:contain"/>';
    };
    reader.readAsDataURL(this.files[0]);
  }
});

// Drag and drop support
const zone = document.querySelector('.upload-zone');
if(zone){
  zone.addEventListener('dragover', e => {
    e.preventDefault();
    zone.style.borderColor = 'var(--accent)';
    zone.style.background  = 'rgba(0,229,160,.05)';
  });
  zone.addEventListener('dragleave', () => {
    zone.style.borderColor = '';
    zone.style.background  = '';
  });
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.borderColor = '';
    zone.style.background  = '';
    const file = e.dataTransfer.files[0];
    if(file){
      const dt = new DataTransfer();
      dt.items.add(file);
      document.getElementById('imgFile').files = dt.files;
      document.getElementById('imgFile').dispatchEvent(new Event('change'));
    }
  });
}
</script>

<?php include 'admin_footer.php'; ?>
