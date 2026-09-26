<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
session_start();
$cfgFile = __DIR__ . '/config.php';
$lock = __DIR__ . '/install.lock';

// 已安装则禁止重复访问
$installed = is_file($lock);
if (!$installed && is_file($cfgFile)) { require $cfgFile; $installed = defined('INSTALLED') && INSTALLED; }
if ($installed) {
    http_response_code(403);
    die('<!--
作者：iluoye8
网站：http://m.ksks63.com/
--><meta charset="utf-8"><p style="font:16px sans-serif;text-align:center;margin-top:80px">网站已安装，禁止重复安装。请删除 install.php。<br><small>程序作者：iluoye8 · <a href="http://m.ksks63.com/">http://m.ksks63.com/</a></small></p>');
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$env = [
    'PHP 版本 >= 7.4（当前 ' . PHP_VERSION . '）' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO 扩展' => extension_loaded('pdo'),
    'pdo_mysql 扩展' => extension_loaded('pdo_mysql'),
    'mbstring 扩展' => extension_loaded('mbstring'),
    '根目录可写（生成 config.php）' => is_writable(__DIR__),
];
$envOk = !in_array(false, $env, true);

$f = ['host' => '127.0.0.1', 'name' => 'mini_movie', 'user' => 'root', 'pass' => '', 'site' => '迷你电影网', 'admin' => 'admin'];
$err = ''; $done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $envOk) {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) die('非法请求');
    foreach (['host', 'name', 'user', 'site', 'admin'] as $k) $f[$k] = trim($_POST[$k] ?? '');
    $f['pass'] = $_POST['pass'] ?? '';
    $apass = $_POST['apass'] ?? '';
    if ($f['host'] === '' || !preg_match('/^[A-Za-z0-9_]{1,64}$/', $f['name']) || $f['user'] === '') $err = '数据库信息不完整，库名仅允许字母、数字、下划线';
    elseif ($f['site'] === '' || mb_strlen($f['site']) > 50) $err = '请填写网站名称（不超过50字）';
    elseif (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $f['admin'])) $err = '管理员账号需为3-20位字母、数字或下划线';
    elseif (strlen($apass) < 6) $err = '管理员密码至少6位';
    elseif ($apass !== ($_POST['apass2'] ?? '')) $err = '两次输入的密码不一致';
    else {
        try {
            $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            try {
                $pdo = new PDO("mysql:host={$f['host']};charset=utf8mb4", $f['user'], $f['pass'], $opt);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$f['name']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$f['name']}`");
            } catch (PDOException $e) { // 无建库权限时，尝试直接连接已存在的库
                $pdo = new PDO("mysql:host={$f['host']};dbname={$f['name']};charset=utf8mb4", $f['user'], $f['pass'], $opt);
            }
            $sql = [
                "CREATE TABLE IF NOT EXISTS category (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(30) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                "CREATE TABLE IF NOT EXISTS movies (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(100) NOT NULL,
                    category_id INT UNSIGNED NOT NULL DEFAULT 0,
                    cover VARCHAR(255) NOT NULL DEFAULT '',
                    description TEXT,
                    actors VARCHAR(255) NOT NULL DEFAULT '',
                    release_date DATE DEFAULT NULL,
                    video_url VARCHAR(500) NOT NULL DEFAULT '',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_cat (category_id),
                    KEY idx_title (title)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                "CREATE TABLE IF NOT EXISTS admin (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(20) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            ];
            foreach ($sql as $s) $pdo->exec($s);
            $pdo->prepare('INSERT INTO admin (username,password) VALUES (?,?) ON DUPLICATE KEY UPDATE password=VALUES(password)')
                ->execute([$f['admin'], password_hash($apass, PASSWORD_DEFAULT)]);
            $ins = $pdo->prepare('INSERT IGNORE INTO category (name) VALUES (?)');
            foreach (['动作', '喜剧', '科幻', '爱情'] as $c) $ins->execute([$c]);

            $cfg = "<?php\n/*\n作者：iluoye8\n网站：http://m.ksks63.com/\n*/\n?>\n<?php\n"
                . "define('INSTALLED', true);\n"
                . "define('DB_HOST', " . var_export($f['host'], true) . ");\n"
                . "define('DB_NAME', " . var_export($f['name'], true) . ");\n"
                . "define('DB_USER', " . var_export($f['user'], true) . ");\n"
                . "define('DB_PASS', " . var_export($f['pass'], true) . ");\n"
                . "define('SITE_NAME', " . var_export($f['site'], true) . ");\n";
            if (file_put_contents($cfgFile, $cfg) === false) throw new Exception('config.php 写入失败，请检查目录权限');
            @file_put_contents($lock, date('c'));
            $done = true;
        } catch (Exception $e) {
            $err = '安装失败：' . $e->getMessage();
        }
    }
}
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
?>
<!--
作者：iluoye8
网站：http://m.ksks63.com/
-->
<!DOCTYPE html>
<html lang="zh-CN"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>网站安装向导</title>
<style>
body{margin:0;background:#f4f5f7;font:14px/1.7 "Microsoft YaHei",Arial,sans-serif;color:#222}
.w{max-width:520px;margin:30px auto;background:#fff;padding:25px;border-radius:6px;box-shadow:0 2px 8px #0002}
h1{margin:0 0 5px;color:#e50914;font-size:22px}h3{border-left:4px solid #e50914;padding-left:8px}
label{display:block;margin-top:10px;font-weight:bold}input{width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;box-sizing:border-box}
button{margin-top:18px;background:#e50914;color:#fff;border:0;padding:10px 24px;border-radius:3px;cursor:pointer;font-size:15px}
.ok{color:#080}.no{color:#c00}.err{background:#fde8e8;color:#b00;padding:8px 12px;border-radius:3px}.good{background:#e6f7e6;color:#060;padding:12px;border-radius:3px}
.ft{text-align:center;color:#888;font-size:13px;margin-top:20px}.ft a{color:#888}
</style></head><body>
<div class="w">
<h1>电影网站安装向导</h1>
<div style="color:#888">程序作者：iluoye8 · 网站：<a href="http://m.ksks63.com/" target="_blank" rel="noopener">http://m.ksks63.com/</a></div>
<?php if ($done): ?>
<div class="good"><h3>安装完成！</h3>
<p>数据表已创建，管理员账号已设置，config.php 已生成。</p>
<p><b>⚠ 为了安全，请立即删除服务器上的 install.php 文件！</b>（已安装后本页将禁止再次访问）</p>
<p><a href="index.php">访问前台首页</a> ｜ <a href="admin/login.php">进入后台登录</a></p></div>
<?php else: ?>
<h3>1. 环境检测</h3>
<?php foreach ($env as $k => $v): ?><div class="<?= $v ? 'ok' : 'no' ?>"><?= $v ? '✔' : '✘' ?> <?= h($k) ?></div><?php endforeach; ?>
<?php if (!$envOk): ?><p class="err">环境不满足要求，请先修复后刷新页面。</p><?php else: ?>
<?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
<h3>2. 数据库配置</h3>
<label>数据库地址</label><input name="host" value="<?= h($f['host']) ?>" required>
<label>数据库名（不存在将尝试自动创建）</label><input name="name" value="<?= h($f['name']) ?>" required>
<label>数据库账号</label><input name="user" value="<?= h($f['user']) ?>" required>
<label>数据库密码</label><input type="password" name="pass">
<h3>3. 站点与管理员</h3>
<label>网站名称</label><input name="site" value="<?= h($f['site']) ?>" required>
<label>管理员账号</label><input name="admin" value="<?= h($f['admin']) ?>" required>
<label>管理员密码（至少6位）</label><input type="password" name="apass" required>
<label>确认密码</label><input type="password" name="apass2" required>
<button>开始安装</button>
</form>
<?php endif; endif; ?>
<div class="ft">&copy; <?= date('Y') ?> 程序作者：iluoye8 · <a href="http://m.ksks63.com/" target="_blank" rel="noopener">http://m.ksks63.com/</a></div>
</div></body></html>
