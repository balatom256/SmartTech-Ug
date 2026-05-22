<?php
require_once '../includes/config.php';
$pageTitle = 'Categories';
include 'admin_layout.php';

// Save
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_cat'])){
    $id   = (int)($_POST['id'] ?? 0);
    $name = sanitize($conn,$_POST['name']);
    $icon = sanitize($conn,$_POST['icon'] ?: 'fa-solid fa-box');
    $slug = sanitize($conn,strtolower(preg_replace('/[^a-z0-9]+/','-',$_POST['slug'] ?: $_POST['name'])));
    if(!$name){ $_SESSION['admin_msg']='Name is required.'; $_SESSION['admin_msg_type']='error'; header('Location: categories.php'); exit; }
    if($id) $conn->query("UPDATE categories SET name='$name',icon='$icon',slug='$slug' WHERE id=$id");
    else    $conn->query("INSERT INTO categories (name,icon,slug) VALUES('$name','$icon','$slug')");
    $_SESSION['admin_msg']=$id?'Category updated.':'Category added.'; $_SESSION['admin_msg_type']='success';
    header('Location: categories.php'); exit;
}
// Delete
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $cnt = $conn->query("SELECT COUNT(*) c FROM products WHERE category_id=$id")->fetch_assoc()['c'];
    if($cnt>0){ $_SESSION['admin_msg']="Cannot delete — $cnt products use this category."; $_SESSION['admin_msg_type']='error'; }
    else { $conn->query("DELETE FROM categories WHERE id=$id"); $_SESSION['admin_msg']='Category deleted.'; $_SESSION['admin_msg_type']='success'; }
    header('Location: categories.php'); exit;
}

$editCat    = isset($_GET['edit']) ? $conn->query("SELECT * FROM categories WHERE id=".(int)$_GET['edit'])->fetch_assoc() : null;
$categories = $conn->query("SELECT c.*,(SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) as product_count FROM categories c ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);

$icons = ['fa-solid fa-mobile-screen','fa-solid fa-laptop','fa-solid fa-headphones','fa-solid fa-tv','fa-solid fa-clock','fa-solid fa-camera','fa-solid fa-plug','fa-solid fa-gamepad','fa-solid fa-tablet-screen-button','fa-solid fa-print','fa-solid fa-keyboard','fa-solid fa-computer-mouse','fa-solid fa-hard-drive','fa-solid fa-wifi','fa-solid fa-battery-full'];
?>

<div class="grid-2" style="align-items:start">
  <!-- LIST -->
  <div class="card">
    <div class="card-title flex justify-between flex-center">
      <span><i class="fa-solid fa-tags"></i> All Categories (<?= count($categories) ?>)</span>
      <a href="categories.php?add=1" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add New</a>
    </div>
    <table class="data-table">
      <thead><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($categories as $cat): ?>
        <tr>
          <td><i class="<?= htmlspecialchars($cat['icon']) ?>" style="color:var(--accent);font-size:1.1rem"></i></td>
          <td class="fw-bold"><?= htmlspecialchars($cat['name']) ?></td>
          <td><code style="color:var(--text);font-size:.78rem"><?= htmlspecialchars($cat['slug']) ?></code></td>
          <td><span class="badge badge-gray"><?= $cat['product_count'] ?></span></td>
          <td>
            <div style="display:flex;gap:5px">
              <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
              <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Delete '<?= htmlspecialchars($cat['name']) ?>'?"><i class="fa-solid fa-trash"></i></a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- FORM -->
  <?php if($editCat || isset($_GET['add'])): ?>
  <div class="card" style="position:sticky;top:10px">
    <div class="card-title"><i class="fa-solid fa-<?= $editCat?'pen':'plus' ?>"></i> <?= $editCat?'Edit Category':'Add Category' ?></div>
    <form method="POST">
      <?php if($editCat): ?><input type="hidden" name="id" value="<?= $editCat['id'] ?>"><?php endif; ?>
      <input type="hidden" name="save_cat" value="1">
      <div class="form-group"><label class="form-label">Category Name *</label><input class="form-control" name="name" required value="<?= htmlspecialchars($editCat['name'] ?? '') ?>" placeholder="e.g. Smartphones" oninput="autoSlug(this)"/></div>
      <div class="form-group"><label class="form-label">URL Slug *</label><input class="form-control" name="slug" id="slugField" value="<?= htmlspecialchars($editCat['slug'] ?? '') ?>" placeholder="e.g. smartphones"/></div>
      <div class="form-group">
        <label class="form-label">Icon (Font Awesome class)</label>
        <input class="form-control" name="icon" id="iconField" value="<?= htmlspecialchars($editCat['icon'] ?? 'fa-solid fa-box') ?>" placeholder="fa-solid fa-box"/>
        <div style="margin-top:.8rem;display:flex;gap:8px;flex-wrap:wrap">
          <?php foreach($icons as $ic): ?>
          <button type="button" class="btn btn-outline btn-sm" title="<?= $ic ?>" onclick="document.getElementById('iconField').value='<?= $ic ?>'"><i class="<?= $ic ?>"></i></button>
          <?php endforeach; ?>
        </div>
        <div style="margin-top:.8rem;color:var(--text);font-size:.8rem">Preview: <i id="iconPreview" class="<?= $editCat['icon'] ?? 'fa-solid fa-box' ?>" style="color:var(--accent);font-size:1.2rem;margin-left:6px"></i></div>
      </div>
      <div style="display:flex;gap:8px;margin-top:.5rem">
        <button class="btn btn-primary" style="flex:1;justify-content:center" type="submit"><?= $editCat?'Update':'Add Category' ?></button>
        <a href="categories.php" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>

<script>
function autoSlug(el){
  document.getElementById('slugField').value = el.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
}
document.getElementById('iconField')?.addEventListener('input', function(){
  document.getElementById('iconPreview').className = this.value + ' fa-solid';
  setTimeout(() => document.getElementById('iconPreview').className = this.value, 50);
});
</script>

<?php include 'admin_footer.php'; ?>
