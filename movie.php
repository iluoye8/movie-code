<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
require __DIR__ . '/inc/common.php';
$st = db()->prepare('SELECT m.*, c.name AS cname FROM movies m LEFT JOIN category c ON c.id = m.category_id WHERE m.id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$m = $st->fetch();
if (!$m) { http_response_code(404); die('电影不存在'); }
render('header.html', ['title' => $m['title'], 'base' => '']);
?>
<div class="box detail">
<img src="<?= e($m['cover']) ?>" alt="<?= e($m['title']) ?>">
<div class="info">
<h1><?= e($m['title']) ?></h1>
<p>分类：<?= e($m['cname'] ?: '未分类') ?></p>
<p>上映时间：<?= e($m['release_date']) ?></p>
<p>演员：<?= e($m['actors']) ?></p>
<p><?= nl2br(e($m['description'])) ?></p>
<?php if ($m['video_url']): ?><a class="btn" href="play.php?id=<?= $m['id'] ?>">▶ 立即播放</a><?php endif; ?>
<a class="btn gray" href="index.php">返回首页</a>
</div></div>
<?php render('footer.html'); ?>
