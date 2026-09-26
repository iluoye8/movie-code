<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
require __DIR__ . '/../inc/common.php';
admin_required();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    db()->prepare('DELETE FROM movies WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
    redirect('index.php');
}
$list = db()->query('SELECT m.id,m.title,m.release_date,c.name cname FROM movies m LEFT JOIN category c ON c.id=m.category_id ORDER BY m.id DESC')->fetchAll();
render('header.html', ['title' => '电影管理', 'base' => '../', 'isAdmin' => true]);
?>
<div class="box"><a class="btn" href="movie_edit.php">+ 新增电影</a></div>
<table><tr><th>ID</th><th>片名</th><th>分类</th><th>上映</th><th>操作</th></tr>
<?php foreach ($list as $m): ?>
<tr><td><?= $m['id'] ?></td><td><?= e($m['title']) ?></td><td><?= e($m['cname']) ?></td><td><?= e($m['release_date']) ?></td>
<td><a href="movie_edit.php?id=<?= $m['id'] ?>">编辑</a>
<form method="post" style="display:inline" onsubmit="return confirm('确定删除？')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $m['id'] ?>"><button>删除</button></form></td></tr>
<?php endforeach; ?></table>
<?php render('footer.html'); ?>
