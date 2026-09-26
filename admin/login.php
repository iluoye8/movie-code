<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
require __DIR__ . '/../inc/common.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = db()->prepare('SELECT * FROM admin WHERE username = ?');
    $st->execute([trim($_POST['username'] ?? '')]);
    $a = $st->fetch();
    if ($a && password_verify($_POST['password'] ?? '', $a['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $a['id'];
        redirect('index.php');
    }
    $err = '账号或密码错误';
}
render('header.html', ['title' => '管理员登录', 'base' => '../', 'isAdmin' => false]);
?>
<div class="box form" style="max-width:380px;margin:40px auto">
<h2>管理员登录</h2>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label>账号</label><input type="text" name="username" required>
<label>密码</label><input type="password" name="password" required><br><br>
<button>登录</button></form></div>
<?php render('footer.html'); ?>
