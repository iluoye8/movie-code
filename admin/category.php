<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
require __DIR__ . '/../inc/common.php';
admin_required();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['del'])) {
        $id = (int)$_POST['del'];
        db()->prepare('UPDATE movies SET category_id = 0 WHERE category_id = ?')->execute([$id]);
        db()->prepare('DELETE FROM category WHERE id = ?')->execute([$id]);
    } else {
        $name = trim($_POST['name'] ?? '');
        if ($name === '' || mb_strlen($name) > 30) $err = '分类名必填且不超过30字';
        else {
            try { db()->prepare('INSERT INTO category (name) VALUES (?)')->execute([$name]); }
            catch (PDOException $ex) { $err = '分类已存在'; }
        }
    }
    if (!$err) redirect('category.php');
}
$cats = db()->query('SELECT * FROM category ORDER BY id')->fetchAll();
render('header.html', ['title' => '分类管理', 'base' => '../', 'isAdmin' => true]);
?>
<div class="box form"><h2>新增分类</h2>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><input type="text" name="name" placeholder="分类名称" required style="width:60%"> <button>添加</button></form></div>
<table><tr><th>ID</th><th>名称</th><th>操作</th></tr>
<?php foreach ($cats as $c): ?>
<tr><td><?= $c['id'] ?></td><td><?= e($c['name']) ?></td><td>
<form method="post" onsubmit="return confirm('删除后该分类下电影变为未分类，确定？')"><?= csrf_field() ?><button name="del" value="<?= $c['id'] ?>">删除</button></form></td></tr>
<?php endforeach; ?></table>
<?php render('footer.html'); ?>
