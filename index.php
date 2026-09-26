<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
require __DIR__ . '/inc/common.php';
$per = 12;
$q = trim($_GET['q'] ?? '');
$cid = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$where = ['1=1']; $args = [];
if ($q !== '') { $where[] = '(title LIKE ? OR actors LIKE ?)'; $args[] = "%$q%"; $args[] = "%$q%"; }
if ($cid) { $where[] = 'category_id = ?'; $args[] = $cid; }
$w = implode(' AND ', $where);
$st = db()->prepare("SELECT COUNT(*) FROM movies WHERE $w");
$st->execute($args);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$off = ($page - 1) * $per;
$st = db()->prepare("SELECT id,title,cover FROM movies WHERE $w ORDER BY id DESC LIMIT $per OFFSET $off");
$st->execute($args);
$movies = $st->fetchAll();
$cats = db()->query('SELECT * FROM category ORDER BY id')->fetchAll();
$qs = function ($p) use ($q, $cid) { return '?' . http_build_query(['q' => $q, 'cat' => $cid, 'page' => $p]); };
render('header.html', ['title' => '首页', 'base' => '']);
?>
<div class="cats"><a href="index.php" class="<?= $cid ? '' : 'on' ?>">全部</a>
<?php foreach ($cats as $c): ?><a href="?cat=<?= $c['id'] ?>" class="<?= $cid == $c['id'] ? 'on' : '' ?>"><?= e($c['name']) ?></a><?php endforeach; ?></div>
<?php if (!$movies): ?><div class="box">没有找到相关电影。</div><?php endif; ?>
<div class="grid">
<?php foreach ($movies as $m): ?>
<a class="card" href="movie.php?id=<?= $m['id'] ?>"><img src="<?= e($m['cover']) ?>" alt="<?= e($m['title']) ?>"><h3><?= e($m['title']) ?></h3></a>
<?php endforeach; ?>
</div>
<div class="pager">
<?php for ($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++): ?>
<?= $i == $page ? "<span>$i</span>" : '<a href="' . e($qs($i)) . "\">$i</a>" ?>
<?php endfor; ?>
<a href="<?= e($qs($pages)) ?>">末页</a> 共 <?= $total ?> 部
</div>
<?php render('footer.html'); ?>
