<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
require __DIR__ . '/inc/common.php';
$st = db()->prepare('SELECT * FROM movies WHERE id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$m = $st->fetch();
if (!$m) { http_response_code(404); die('电影不存在'); }
$url = $m['video_url'];
$isFile = preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $url);
render('header.html', ['title' => '播放：' . $m['title'], 'base' => '']);
?>
<div class="box">
<h2><?= e($m['title']) ?></h2>
<?php if (!$url): ?><p>暂无播放地址。</p>
<?php elseif ($isFile): ?><video src="<?= e($url) ?>" controls autoplay></video>
<?php else: ?><iframe class="player" src="<?= e($url) ?>" allowfullscreen></iframe><?php endif; ?>
<p>上映时间：<?= e($m['release_date']) ?> ｜ 演员：<?= e($m['actors']) ?></p>
<p><?= nl2br(e($m['description'])) ?></p>
<a class="btn gray" href="movie.php?id=<?= $m['id'] ?>">返回详情</a>
</div>
<?php render('footer.html'); ?>
