<?php
// ── SmartTech-UG · includes/config.php ──

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smarttech_ug');
define('SITE_NAME', 'SmartTech-UG');
define('SITE_URL', 'http://localhost/smarttech-ug');
define('CURRENCY', 'UGX ');

if(session_status() === PHP_SESSION_NONE) session_start();

// ── DB Connection ──
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if($conn->connect_error){
    die('<div style="font-family:sans-serif;padding:2rem;background:#0a0a0a;color:#ff6b35;min-height:100vh">
        <h2>⚠️ Database Connection Failed</h2>
        <p style="color:#b0b0a8">'.$conn->connect_error.'</p>
        <p style="color:#b0b0a8;margin-top:1rem">Make sure XAMPP MySQL is running and you imported <strong>smarttech_ug.sql</strong> in phpMyAdmin.</p>
    </div>');
}
$conn->set_charset('utf8mb4');

// ── Settings helper ──
function getSetting($conn, $key, $default = ''){
    $k = $conn->real_escape_string($key);
    $tableExists = $conn->query("SHOW TABLES LIKE 'settings'")->num_rows > 0;
    if(!$tableExists) return $default;
    $r = $conn->query("SELECT value FROM settings WHERE `key`='$k'")->fetch_assoc();
    return $r ? $r['value'] : $default;
}

function saveSetting($conn, $key, $value){
    $k = $conn->real_escape_string($key);
    $v = $conn->real_escape_string($value);
    $conn->query("INSERT INTO settings (`key`,`value`) VALUES('$k','$v') ON DUPLICATE KEY UPDATE `value`='$v'");
}

// ── Maintenance Mode ──
$isAdminArea = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$isLoginPage = basename($_SERVER['PHP_SELF']) === 'login.php';
$isMaintPage = basename($_SERVER['PHP_SELF']) === 'maintenance.php';
$isAdminUser = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$maintenanceOn = getSetting($conn, 'maintenance_mode', '0') === '1';

if($maintenanceOn && !$isAdminArea && !$isAdminUser && !$isMaintPage && !$isLoginPage){
    header('Location: ' . SITE_URL . '/maintenance.php');
    exit;
}

// ── Helpers ──
function formatPrice($amount){
    return CURRENCY . number_format((float)$amount, 0, '.', ',');
}
function isLoggedIn(){ return isset($_SESSION['user_id']); }
function isAdmin(){ return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function requireLogin(){
    if(!isLoggedIn()){
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php'); exit;
    }
}
function requireAdmin(){
    if(!isAdmin()){ header('Location: ' . SITE_URL . '/index.php'); exit; }
}
function sanitize($conn, $val){
    return $conn->real_escape_string(trim($val));
}
function getCartCount($conn){
    if(!isLoggedIn()) return 0;
    $uid = (int)$_SESSION['user_id'];
    $r = $conn->query("SELECT SUM(quantity) as t FROM cart WHERE user_id=$uid");
    return (int)($r->fetch_assoc()['t'] ?? 0);
}
function getCartItems($conn){
    if(!isLoggedIn()) return [];
    $uid = (int)$_SESSION['user_id'];
    $r = $conn->query("SELECT c.*, p.name, p.price, p.emoji, p.image FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=$uid");
    return $r->fetch_all(MYSQLI_ASSOC);
}
function getCartTotal($conn){
    return array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], getCartItems($conn)));
}

// ── Dynamic logo helper ──
// Returns HTML string for the logo based on admin settings
function renderLogo($conn, $linkUrl = null){
    $usImg   = getSetting($conn, 'use_logo_image', '0') === '1';
    $logoFile= getSetting($conn, 'logo_file', '');
    $t1      = htmlspecialchars(getSetting($conn, 'logo_text',       'Smart'));
    $t2      = htmlspecialchars(getSetting($conn, 'logo_text_span',  'Tech'));
    $t3      = htmlspecialchars(getSetting($conn, 'logo_suffix',     '-UG'));
    $href    = $linkUrl ?? (SITE_URL . '/index.php');

    $assetBase = SITE_URL . '/assets/';
    if($usImg && $logoFile && file_exists($_SERVER['DOCUMENT_ROOT'] . '/smarttech-ug/assets/' . $logoFile)){
        $inner = '<img src="'.$assetBase.htmlspecialchars($logoFile).'" height="40" alt="'.SITE_NAME.'" style="display:block"/>';
    } else {
        $inner = $t1.'<span style="color:var(--accent)">'.$t2.'</span>'.$t3;
    }
    return '<a href="'.$href.'" class="logo" style="font-family:var(--font-head);font-size:1.5rem;font-weight:800;color:var(--white);text-decoration:none">'.$inner.'</a>';
}
?>
