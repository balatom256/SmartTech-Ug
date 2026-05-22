<?php
// includes/footer.php  — reads all contact/social from DB settings
$phone    = getSetting($conn, 'contact_phone',   '+256 700 000 000');
$email    = getSetting($conn, 'contact_email',   'hello@smarttech-ug.com');
$address  = getSetting($conn, 'contact_address', 'Kampala, Uganda');
$tagline  = getSetting($conn, 'site_tagline',    "Uganda's leading online marketplace for authentic tech products.");
$waNum    = getSetting($conn, 'whatsapp_number', '');
$waMsg    = getSetting($conn, 'whatsapp_message','Hello! I found you on SmartTech-UG.');
$showWa   = getSetting($conn, 'show_whatsapp',   '1') === '1';

$socials  = [
    'facebook'  => ['fa-facebook-f',  getSetting($conn,'social_facebook','#')],
    'instagram' => ['fa-instagram',   getSetting($conn,'social_instagram','#')],
    'twitter'   => ['fa-twitter',     getSetting($conn,'social_twitter','#')],
    'tiktok'    => ['fa-tiktok',      getSetting($conn,'social_tiktok','#')],
    'youtube'   => ['fa-youtube',     getSetting($conn,'social_youtube','')],
];

$categories = $conn->query("SELECT name,slug FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<footer style="background:var(--mid);border-top:1px solid var(--border);padding:60px 5% 30px;margin-top:4rem">
  <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:3rem;margin-bottom:3rem">
    <div>
      <?= renderLogo($conn) ?>
      <p style="color:var(--text);font-size:.88rem;line-height:1.75;max-width:280px;margin:1rem 0 1.5rem"><?= htmlspecialchars($tagline) ?></p>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php foreach($socials as $net => [$icon, $url]):
          if(!$url || $url === '#') continue;
        ?>
        <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener"
           style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text);font-size:.85rem;transition:all .2s"
           onmouseover="this.style.background='rgba(0,229,160,.15)';this.style.color='var(--accent)'"
           onmouseout="this.style.background='rgba(255,255,255,.06)';this.style.color='var(--text)'">
          <i class="fa-brands <?= $icon ?>"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <h4 style="font-family:var(--font-head);font-size:.9rem;font-weight:700;margin-bottom:1.2rem">Shop</h4>
      <ul style="list-style:none">
        <?php foreach($categories as $cat): ?>
        <li style="margin-bottom:.65rem">
          <a href="<?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?>"
             style="color:var(--text);font-size:.87rem;transition:color .2s"
             onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text)'">
            <?= htmlspecialchars($cat['name']) ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h4 style="font-family:var(--font-head);font-size:.9rem;font-weight:700;margin-bottom:1.2rem">Account</h4>
      <ul style="list-style:none">
        <?php foreach([['Sign In','login.php'],['Register','register.php'],['My Orders','orders.php'],['Wishlist','wishlist.php'],['Cart','cart.php']] as [$n,$p]): ?>
        <li style="margin-bottom:.65rem">
          <a href="<?= SITE_URL ?>/<?= $p ?>" style="color:var(--text);font-size:.87rem;transition:color .2s"
             onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text)'"><?= $n ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h4 style="font-family:var(--font-head);font-size:.9rem;font-weight:700;margin-bottom:1.2rem">Contact</h4>
      <ul style="list-style:none">
        <?php if($phone): ?>
        <li style="margin-bottom:.75rem;color:var(--text);font-size:.87rem">
          <i class="fa-solid fa-phone" style="color:var(--accent);margin-right:8px;width:14px"></i>
          <a href="tel:<?= preg_replace('/[^+0-9]/','',$phone) ?>" style="color:var(--text)"><?= htmlspecialchars($phone) ?></a>
        </li>
        <?php endif; ?>
        <?php if($email): ?>
        <li style="margin-bottom:.75rem;color:var(--text);font-size:.87rem">
          <i class="fa-solid fa-envelope" style="color:var(--accent);margin-right:8px;width:14px"></i>
          <a href="mailto:<?= htmlspecialchars($email) ?>" style="color:var(--text)"><?= htmlspecialchars($email) ?></a>
        </li>
        <?php endif; ?>
        <?php if($address): ?>
        <li style="margin-bottom:.75rem;color:var(--text);font-size:.87rem">
          <i class="fa-solid fa-location-dot" style="color:var(--accent);margin-right:8px;width:14px"></i>
          <?= htmlspecialchars($address) ?>
        </li>
        <?php endif; ?>
        <?php if($waNum): ?>
        <li style="margin-bottom:.75rem">
          <a href="https://wa.me/<?= htmlspecialchars($waNum) ?>?text=<?= urlencode($waMsg) ?>" target="_blank"
             style="color:var(--text);font-size:.87rem;display:flex;align-items:center;gap:8px">
            <i class="fa-brands fa-whatsapp" style="color:#25D366;font-size:1rem"></i> WhatsApp Chat
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;padding-top:2rem;border-top:1px solid var(--border);flex-wrap:wrap;gap:1rem">
    <p style="color:var(--text);font-size:.8rem">
      © <?= date('Y') ?> <?= htmlspecialchars(getSetting($conn,'site_name',SITE_NAME)) ?>. Built with ❤️ in Uganda.
    </p>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <?php foreach(['MTN MoMo','Airtel Money','VISA','Mastercard'] as $pay): ?>
      <span style="background:rgba(255,255,255,.06);border-radius:6px;padding:4px 10px;font-size:.72rem;color:var(--text);border:1px solid var(--border)"><?= $pay ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</footer>

<?php if($showWa && $waNum): ?>
<div class="wa-float">
  <div class="wa-tooltip">Chat with us on WhatsApp</div>
  <a href="https://wa.me/<?= htmlspecialchars($waNum) ?>?text=<?= urlencode($waMsg) ?>" target="_blank" class="wa-btn" title="Chat on WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
  </a>
</div>
<?php endif; ?>

</body>
</html>
