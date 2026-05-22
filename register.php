<?php
require_once 'includes/config.php';
if(isLoggedIn()){ header('Location: index.php'); exit; }
$pageTitle = 'Create Account – SmartTech-UG';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name  = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    $pass  = $_POST['password'];
    $pass2 = $_POST['password2'];
    if(!$name || !$email || !$pass) $error='All fields required.';
    elseif($pass !== $pass2) $error='Passwords do not match.';
    elseif(strlen($pass) < 6) $error='Password must be at least 6 characters.';
    elseif($conn->query("SELECT id FROM users WHERE email='$email'")->num_rows) $error='Email already registered. <a href="login.php" style="color:var(--accent)">Sign in?</a>';
    else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (name,email,password,phone) VALUES('$name','$email','$hash','$phone')");
        $_SESSION['msg'] = 'Account created! Please sign in.';
        header('Location: login.php'); exit;
    }
}
include 'includes/header.php';
?>
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:100px 5% 60px">
  <div style="width:100%;max-width:440px">
    <div style="text-align:center;margin-bottom:2.5rem">
      <a href="index.php" class="logo" style="display:inline-block;margin-bottom:1rem;font-size:1.8rem">Smart<span>Tech</span>-UG</a>
      <h2 style="font-family:var(--font-head);font-weight:800;font-size:1.8rem;margin-bottom:.5rem">Create Account</h2>
      <p style="color:var(--text);font-size:.9rem">Join thousands of Ugandan tech shoppers</p>
    </div>
    <div class="card">
      <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-group"><label>Full Name *</label><input class="form-control" name="name" required placeholder="e.g. Aisha Kato" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"/></div>
        <div class="form-group"><label>Email Address *</label><input class="form-control" type="email" name="email" required placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/></div>
        <div class="form-group"><label>Phone Number</label><input class="form-control" name="phone" placeholder="+256 7XX XXX XXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/></div>
        <div class="form-group"><label>Password *</label><input class="form-control" type="password" name="password" required placeholder="Min. 6 characters"/></div>
        <div class="form-group"><label>Confirm Password *</label><input class="form-control" type="password" name="password2" required placeholder="Repeat password"/></div>
        <button class="btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">Create Account <i class="fa-solid fa-user-plus"></i></button>
      </form>
      <div style="text-align:center;margin-top:1.5rem;color:var(--text);font-size:.88rem">
        Already have an account? <a href="login.php" style="color:var(--accent);font-weight:600">Sign in</a>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
