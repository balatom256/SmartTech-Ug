<?php
require_once '../includes/config.php';
$pageTitle = 'Site Settings';
include 'admin_layout.php';

// Settings table
$conn->query("CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT
) ENGINE=InnoDB");

// Save settings
if($_SERVER['REQUEST_METHOD']==='POST'){
    $section = $_POST['section'] ?? '';

    if($section === 'general'){
        saveSetting($conn,'site_name',       $_POST['site_name'] ?? 'SmartTech-UG');
        saveSetting($conn,'site_tagline',    $_POST['site_tagline'] ?? '');
        saveSetting($conn,'contact_phone',   $_POST['contact_phone'] ?? '');
        saveSetting($conn,'contact_email',   $_POST['contact_email'] ?? '');
        saveSetting($conn,'contact_address', $_POST['contact_address'] ?? '');
        saveSetting($conn,'whatsapp_number', preg_replace('/[^0-9]/','',$_POST['whatsapp_number'] ?? ''));
        saveSetting($conn,'whatsapp_message',$_POST['whatsapp_message'] ?? 'Hello! I found you on SmartTech-UG.');
        saveSetting($conn,'show_whatsapp',   isset($_POST['show_whatsapp'])?'1':'0');
    }

    if($section === 'hero'){
        saveSetting($conn,'hero_title_1',  $_POST['hero_title_1'] ?? '');
        saveSetting($conn,'hero_title_2',  $_POST['hero_title_2'] ?? '');
        saveSetting($conn,'hero_title_3',  $_POST['hero_title_3'] ?? '');
        saveSetting($conn,'hero_subtitle', $_POST['hero_subtitle'] ?? '');
        saveSetting($conn,'hero_badge',    $_POST['hero_badge'] ?? '');
        saveSetting($conn,'stat_1_num',    $_POST['stat_1_num'] ?? '5K+');
        saveSetting($conn,'stat_1_label',  $_POST['stat_1_label'] ?? 'Products');
        saveSetting($conn,'stat_2_num',    $_POST['stat_2_num'] ?? '20K+');
        saveSetting($conn,'stat_2_label',  $_POST['stat_2_label'] ?? 'Happy Customers');
        saveSetting($conn,'stat_3_num',    $_POST['stat_3_num'] ?? '100%');
        saveSetting($conn,'stat_3_label',  $_POST['stat_3_label'] ?? 'Authentic');
    }

    if($section === 'logo'){
        $UPLOAD_DIR = '../assets/';
        if(!empty($_FILES['logo_file']['name'])){
            $ext = strtolower(pathinfo($_FILES['logo_file']['name'],PATHINFO_EXTENSION));
            if(in_array($ext,['png','jpg','jpeg','svg','webp'])){
                $fname = 'logo.'.$ext;
                if(move_uploaded_file($_FILES['logo_file']['tmp_name'],$UPLOAD_DIR.$fname)){
                    saveSetting($conn,'logo_file',$fname);
                }
            }
        }
        if(!empty($_FILES['favicon_file']['name'])){
            $ext = strtolower(pathinfo($_FILES['favicon_file']['name'],PATHINFO_EXTENSION));
            if(in_array($ext,['png','ico','jpg'])){
                $fname = 'favicon.'.$ext;
                move_uploaded_file($_FILES['favicon_file']['tmp_name'],$UPLOAD_DIR.$fname);
                saveSetting($conn,'favicon_file',$fname);
            }
        }
        saveSetting($conn,'logo_text',     $_POST['logo_text'] ?? 'Smart');
        saveSetting($conn,'logo_text_span',$_POST['logo_text_span'] ?? 'Tech');
        saveSetting($conn,'logo_suffix',   $_POST['logo_suffix'] ?? '-UG');
        saveSetting($conn,'use_logo_image',isset($_POST['use_logo_image'])?'1':'0');
    }

    if($section === 'maintenance'){
        saveSetting($conn,'maintenance_mode',    isset($_POST['maintenance_mode'])?'1':'0');
        saveSetting($conn,'maintenance_title',   $_POST['maintenance_title'] ?? "We'll be back soon!");
        saveSetting($conn,'maintenance_message', $_POST['maintenance_message'] ?? 'We are under maintenance.');
    }

    if($section === 'social'){
        foreach(['facebook','instagram','twitter','tiktok','youtube'] as $net){
            saveSetting($conn,'social_'.$net, $_POST['social_'.$net] ?? '');
        }
    }

    if($section === 'password'){
        $cur  = $_POST['current_password'];
        $new  = $_POST['new_password'];
        $new2 = $_POST['new_password2'];
        $uid  = (int)$_SESSION['user_id'];
        $user = $conn->query("SELECT password FROM users WHERE id=$uid")->fetch_assoc();
        if(!password_verify($cur,$user['password'])){
            $_SESSION['admin_msg']='Current password is wrong.'; $_SESSION['admin_msg_type']='error';
        } elseif($new !== $new2){
            $_SESSION['admin_msg']='New passwords do not match.'; $_SESSION['admin_msg_type']='error';
        } elseif(strlen($new) < 6){
            $_SESSION['admin_msg']='Password must be at least 6 characters.'; $_SESSION['admin_msg_type']='error';
        } else {
            $hash = password_hash($new,PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hash' WHERE id=$uid");
            $_SESSION['admin_msg']='Password changed successfully.'; $_SESSION['admin_msg_type']='success';
        }
        header('Location: settings.php#password'); exit;
    }

    if($section !== 'password'){
        $_SESSION['admin_msg']='Settings saved!'; $_SESSION['admin_msg_type']='success';
        header('Location: settings.php'); exit;
    }
}

// Load all settings
$s = [];
$res = $conn->query("SELECT `key`,`value` FROM settings");
while($row = $res->fetch_assoc()) $s[$row['key']] = $row['value'];
$g = fn($k,$d='') => $s[$k] ?? $d;
?>
<style>
.settings-tabs{display:flex;gap:6px;margin-bottom:2rem;flex-wrap:wrap;border-bottom:1px solid var(--border);padding-bottom:1rem}
.stab{padding:.5rem 1.1rem;border-radius:8px;font-size:.85rem;font-weight:500;cursor:pointer;color:var(--text);border:1px solid transparent;transition:all .2s;background:none}
.stab:hover{color:var(--white);background:rgba(255,255,255,.05)}
.stab.active{background:rgba(0,229,160,.1);color:var(--accent);border-color:rgba(0,229,160,.25)}
.settings-panel{display:none}.settings-panel.active{display:block}
</style>

<div class="settings-tabs">
  <button class="stab active" onclick="showTab('general',this)"><i class="fa-solid fa-store"></i> General</button>
  <button class="stab" onclick="showTab('logo',this)"><i class="fa-solid fa-image"></i> Logo & Branding</button>
  <button class="stab" onclick="showTab('hero',this)"><i class="fa-solid fa-house"></i> Homepage Text</button>
  <button class="stab" onclick="showTab('social',this)"><i class="fa-solid fa-share-nodes"></i> Social Media</button>
  <button class="stab" onclick="showTab('maintenance',this)"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</button>
  <button class="stab" onclick="showTab('password',this)" id="pwTab"><i class="fa-solid fa-lock"></i> Change Password</button>
</div>

<!-- GENERAL -->
<div class="settings-panel active card" id="tab-general">
  <div class="card-title"><i class="fa-solid fa-store"></i> General Settings</div>
  <form method="POST"><input type="hidden" name="section" value="general">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Business Name</label><input class="form-control" name="site_name" value="<?= htmlspecialchars($g('site_name','SmartTech-UG')) ?>"/></div>
      <div class="form-group"><label class="form-label">Tagline</label><input class="form-control" name="site_tagline" value="<?= htmlspecialchars($g('site_tagline',"Uganda's #1 Tech Marketplace")) ?>"/></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Contact Phone</label><input class="form-control" name="contact_phone" value="<?= htmlspecialchars($g('contact_phone','+256 700 000 000')) ?>" placeholder="+256 7XX XXX XXX"/></div>
      <div class="form-group"><label class="form-label">Contact Email</label><input class="form-control" type="email" name="contact_email" value="<?= htmlspecialchars($g('contact_email','hello@smarttech-ug.com')) ?>"/></div>
    </div>
    <div class="form-group"><label class="form-label">Business Address</label><input class="form-control" name="contact_address" value="<?= htmlspecialchars($g('contact_address','Kampala, Uganda')) ?>"/></div>
    <hr style="border-color:var(--border);margin:1.5rem 0"/>
    <div class="card-title" style="margin-bottom:1rem"><i class="fa-brands fa-whatsapp" style="color:#25D366"></i> WhatsApp Chat Button</div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">WhatsApp Number (digits only)</label><input class="form-control" name="whatsapp_number" value="<?= htmlspecialchars($g('whatsapp_number')) ?>" placeholder="256700000000"/></div>
      <div class="form-group"><label class="form-label">Pre-filled Message</label><input class="form-control" name="whatsapp_message" value="<?= htmlspecialchars($g('whatsapp_message','Hello! I found you on SmartTech-UG.')) ?>"/></div>
    </div>
    <div class="form-group">
      <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="show_whatsapp" value="1" <?= $g('show_whatsapp','1')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label><span class="toggle-label">Show WhatsApp floating button on website</span></div>
    </div>
    <button class="btn btn-primary btn-lg">Save General Settings</button>
  </form>
</div>

<!-- LOGO -->
<div class="settings-panel card" id="tab-logo">
  <div class="card-title"><i class="fa-solid fa-image"></i> Logo & Branding</div>
  <form method="POST" enctype="multipart/form-data"><input type="hidden" name="section" value="logo">
    <div class="form-group">
      <div class="toggle-wrap" style="margin-bottom:1rem"><label class="toggle"><input type="checkbox" name="use_logo_image" value="1" <?= $g('use_logo_image')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label><span class="toggle-label">Use uploaded image logo (instead of text)</span></div>
    </div>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:1.2rem;margin-bottom:1.5rem">
      <div style="font-size:.8rem;color:var(--text);margin-bottom:.8rem;font-weight:600">CURRENT LOGO PREVIEW</div>
      <?php $logoFile = $g('logo_file'); ?>
      <?php if($g('use_logo_image')==='1' && $logoFile && file_exists('../assets/'.$logoFile)): ?>
        <img src="../assets/<?= htmlspecialchars($logoFile) ?>" height="40" alt="Logo"/>
      <?php else: ?>
        <span style="font-family:var(--font-head);font-size:1.4rem;font-weight:800"><?= htmlspecialchars($g('logo_text','Smart')) ?><span style="color:var(--accent)"><?= htmlspecialchars($g('logo_text_span','Tech')) ?></span><?= htmlspecialchars($g('logo_suffix','-UG')) ?></span>
      <?php endif; ?>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Logo Image (PNG/SVG recommended)</label>
        <label class="upload-zone" for="logoFile" style="padding:1rem">
          <i class="fa-solid fa-image"></i><p><span class="accent">Upload Logo</span></p>
          <p style="font-size:.72rem">PNG with transparent background, ~200×50px</p>
          <input type="file" id="logoFile" name="logo_file" accept="image/*"/>
        </label>
      </div>
      <div class="form-group"><label class="form-label">Favicon (browser tab icon)</label>
        <label class="upload-zone" for="faviconFile" style="padding:1rem">
          <i class="fa-solid fa-icons"></i><p><span class="accent">Upload Favicon</span></p>
          <p style="font-size:.72rem">PNG or ICO, 32×32px</p>
          <input type="file" id="faviconFile" name="favicon_file" accept="image/*,.ico"/>
        </label>
      </div>
    </div>
    <div style="border-top:1px solid var(--border);padding-top:1.2rem;margin-top:.5rem">
      <div class="form-label" style="margin-bottom:.8rem">Text Logo (if not using image)</div>
      <div class="form-row-3">
        <div class="form-group"><label class="form-label">First Part</label><input class="form-control" name="logo_text" value="<?= htmlspecialchars($g('logo_text','Smart')) ?>"/></div>
        <div class="form-group"><label class="form-label">Highlighted Part (green)</label><input class="form-control" name="logo_text_span" value="<?= htmlspecialchars($g('logo_text_span','Tech')) ?>"/></div>
        <div class="form-group"><label class="form-label">Suffix</label><input class="form-control" name="logo_suffix" value="<?= htmlspecialchars($g('logo_suffix','-UG')) ?>"/></div>
      </div>
    </div>
    <button class="btn btn-primary btn-lg">Save Branding</button>
  </form>
</div>

<!-- HERO -->
<div class="settings-panel card" id="tab-hero">
  <div class="card-title"><i class="fa-solid fa-house"></i> Homepage Hero Text</div>
  <form method="POST"><input type="hidden" name="section" value="hero">
    <div class="form-group"><label class="form-label">Badge Text (top of hero)</label><input class="form-control" name="hero_badge" value="<?= htmlspecialchars($g('hero_badge',"Uganda's #1 Tech Marketplace")) ?>"/></div>
    <div class="form-group"><label class="form-label">Headline Line 1</label><input class="form-control" name="hero_title_1" value="<?= htmlspecialchars($g('hero_title_1','Shop Smarter,')) ?>"/></div>
    <div class="form-group"><label class="form-label">Headline Line 2 (shown in green)</label><input class="form-control" name="hero_title_2" value="<?= htmlspecialchars($g('hero_title_2','Live Smarter')) ?>"/></div>
    <div class="form-group"><label class="form-label">Headline Line 3</label><input class="form-control" name="hero_title_3" value="<?= htmlspecialchars($g('hero_title_3','with Tech')) ?>"/></div>
    <div class="form-group"><label class="form-label">Subtitle / Description</label><textarea class="form-control" name="hero_subtitle" rows="3"><?= htmlspecialchars($g('hero_subtitle','Discover the latest smartphones, laptops, audio gear and accessories. Fast delivery across Uganda.')) ?></textarea></div>
    <hr style="border-color:var(--border);margin:1rem 0"/>
    <div class="form-label mb-1">Hero Stats (3 counters)</div>
    <div class="form-row-3">
      <div class="form-group"><label class="form-label">Stat 1 Number</label><input class="form-control" name="stat_1_num" value="<?= htmlspecialchars($g('stat_1_num','5K+')) ?>"/></div>
      <div class="form-group"><label class="form-label">Stat 1 Label</label><input class="form-control" name="stat_1_label" value="<?= htmlspecialchars($g('stat_1_label','Products')) ?>"/></div>
      <div class="form-group"></div>
      <div class="form-group"><label class="form-label">Stat 2 Number</label><input class="form-control" name="stat_2_num" value="<?= htmlspecialchars($g('stat_2_num','20K+')) ?>"/></div>
      <div class="form-group"><label class="form-label">Stat 2 Label</label><input class="form-control" name="stat_2_label" value="<?= htmlspecialchars($g('stat_2_label','Happy Customers')) ?>"/></div>
      <div class="form-group"></div>
      <div class="form-group"><label class="form-label">Stat 3 Number</label><input class="form-control" name="stat_3_num" value="<?= htmlspecialchars($g('stat_3_num','100%')) ?>"/></div>
      <div class="form-group"><label class="form-label">Stat 3 Label</label><input class="form-control" name="stat_3_label" value="<?= htmlspecialchars($g('stat_3_label','Authentic')) ?>"/></div>
    </div>
    <button class="btn btn-primary btn-lg">Save Homepage Text</button>
  </form>
</div>

<!-- SOCIAL -->
<div class="settings-panel card" id="tab-social">
  <div class="card-title"><i class="fa-solid fa-share-nodes"></i> Social Media Links</div>
  <form method="POST"><input type="hidden" name="section" value="social">
    <?php foreach([['facebook','fa-facebook-f','Facebook'],['instagram','fa-instagram','Instagram'],['twitter','fa-twitter','Twitter / X'],['tiktok','fa-tiktok','TikTok'],['youtube','fa-youtube','YouTube']] as [$net,$icon,$label]): ?>
    <div class="form-group">
      <label class="form-label"><i class="fa-brands <?= $icon ?>" style="color:var(--accent);margin-right:6px"></i><?= $label ?></label>
      <input class="form-control" name="social_<?= $net ?>" value="<?= htmlspecialchars($g('social_'.$net)) ?>" placeholder="https://<?= $net ?>.com/yourpage"/>
    </div>
    <?php endforeach; ?>
    <button class="btn btn-primary btn-lg">Save Social Links</button>
  </form>
</div>

<!-- MAINTENANCE -->
<div class="settings-panel card" id="tab-maintenance">
  <div class="card-title"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance Mode</div>
  <?php if($g('maintenance_mode')==='1'): ?>
  <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i><strong>Maintenance mode is currently ON.</strong> Visitors see the maintenance page instead of the website. Only admins can access.</div>
  <?php endif; ?>
  <form method="POST"><input type="hidden" name="section" value="maintenance">
    <div class="form-group">
      <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="maintenance_mode" value="1" <?= $g('maintenance_mode')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label><span class="toggle-label" style="<?= $g('maintenance_mode')==='1'?'color:var(--accent2);font-weight:700':'' ?>">Enable Maintenance Mode</span></div>
    </div>
    <div class="form-group"><label class="form-label">Maintenance Page Title</label><input class="form-control" name="maintenance_title" value="<?= htmlspecialchars($g('maintenance_title',"We'll be back soon! 🛠️")) ?>"/></div>
    <div class="form-group"><label class="form-label">Maintenance Message</label><textarea class="form-control" name="maintenance_message" rows="3"><?= htmlspecialchars($g('maintenance_message','SmartTech-UG is under scheduled maintenance. We will be back shortly.')) ?></textarea></div>
    <div class="alert alert-warning" style="margin-top:1rem"><i class="fa-solid fa-info-circle"></i> When maintenance is ON, only admins can browse the site. Turning it off will make the site public again immediately.</div>
    <button class="btn btn-primary btn-lg">Save Maintenance Settings</button>
  </form>
</div>

<!-- CHANGE PASSWORD -->
<div class="settings-panel card" id="tab-password">
  <div class="card-title"><i class="fa-solid fa-lock"></i> Change Admin Password</div>
  <form method="POST" style="max-width:420px"><input type="hidden" name="section" value="password">
    <div class="form-group"><label class="form-label">Current Password</label><input class="form-control" type="password" name="current_password" required/></div>
    <div class="form-group"><label class="form-label">New Password</label><input class="form-control" type="password" name="new_password" required minlength="6"/></div>
    <div class="form-group"><label class="form-label">Confirm New Password</label><input class="form-control" type="password" name="new_password2" required minlength="6"/></div>
    <button class="btn btn-primary btn-lg">Change Password</button>
  </form>
</div>

<script>
function showTab(name, btn){
  document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.stab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  btn.classList.add('active');
}
// Auto-open password tab if URL has #password
if(location.hash === '#password') document.getElementById('pwTab')?.click();
</script>

<?php include 'admin_footer.php'; ?>
