<?php
require_once 'includes/config.php';
if(isLoggedIn()){ header('Location: index.php'); exit; }
$pageTitle = 'Sign In – SmartTech-UG';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = sanitize($conn, $_POST['email']);
    $pass  = $_POST['password'];
    $user  = $conn->query("SELECT * FROM users WHERE email='$email'")->fetch_assoc();
    if($user && password_verify($pass, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['phone']   = $user['phone'];
        $redir = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        header("Location: $redir"); exit;
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}
include 'includes/header.php';
?>
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:100px 5% 60px">
  <div style="width:100%;max-width:420px">
    <div style="text-align:center;margin-bottom:2.5rem">
      <a href="index.php" class="logo" style="display:inline-block;margin-bottom:1rem;font-size:1.8rem">Smart<span>Tech</span>-UG</a>
      <h2 style="font-family:var(--font-head);font-weight:800;font-size:1.8rem;margin-bottom:.5rem">Welcome back</h2>
      <p style="color:var(--text);font-size:.9rem">Sign in to your account</p>
    </div>
    <div class="card">
      <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
      <?php if(isset($_SESSION['msg'])): ?><div class="alert alert-success"><?= $_SESSION['msg'] ?></div><?php unset($_SESSION['msg']); endif; ?>
      <form method="POST">
        <div class="form-group"><label>Email Address *</label><input class="form-control" type="email" name="email" required placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/></div>
        <div class="form-group"><label>Password *</label><input class="form-control" type="password" name="password" required placeholder="Your password"/></div>
        <button class="btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">Sign In <i class="fa-solid fa-arrow-right"></i></button>
      </form>
      <div style="text-align:center;margin-top:1.5rem;color:var(--text);font-size:.88rem">
        Don't have an account? <a href="register.php" style="color:var(--accent);font-weight:600">Register here</a>
      </div>
      <div style="text-align:center;margin-top:1rem;font-size:.8rem;color:var(--text)">
        <strong>Demo Admin:</strong> admin@smarttech-ug.com / password<br>
        <strong>Demo Customer:</strong> aisha@example.com / password
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
