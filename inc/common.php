<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
session_start();
require __DIR__ . '/../config.php';
if (!INSTALLED) {
    $up = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false ? '../' : '';
    header('Location: ' . $up . 'install.php');
    exit;
}

function db() {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header('Location: ' . $url); exit; }
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) die('非法请求（CSRF校验失败）');
}
function admin_required() {
    if (empty($_SESSION['admin_id'])) redirect('login.php');
}
// 仅允许 http(s):// 或站内绝对路径；空值合法，非法返回 false
function clean_url($u) {
    $u = trim($u);
    return ($u === '' || preg_match('#^(https?://|/)[^\s<>"\']+$#i', $u)) ? $u : false;
}
function render($file, array $vars = []) {
    extract($vars);
    include __DIR__ . '/../templates/' . $file;
}
