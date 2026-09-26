<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
require __DIR__ . '/../inc/common.php';
admin_required();
$id = (int)($_GET['id'] ?? 0);
$m = ['title' => '', 'category_id' => 0, 'cover' => '', 'description' => '', 'actors' => '', 'release_date' => '', 'video_url' => ''];
if ($id) {
    $st = db()->prepare('SELECT * FROM movies WHERE id = ?');
    $st->execute([$id]);
    $m = $st->fetch() ?: die('电影不存在');
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $m = [
        'title' => trim($_POST['title'] ?? ''), 'category_id' => (int)($_POST['category_id'] ?? 0),
        'cover' => clean_url($_POST['cover'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
        'actors' => trim($_POST['actors'] ?? ''), 'release_date' => trim($_POST['release_date'] ?? ''),
        'video_url' => clean_url($_POST['video_url'] ?? ''),
    ];
    if ($m['title'] === '' || mb_strlen($m['title']) > 100) $err = '片名必填且不超过100字';
    elseif ($m['cover'] === false || $m['video_url'] === false) $err = '封面/视频地址必须以 http(s):// 或 / 开头';
    elseif ($m['release_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $m['release_date'])) $err = '上映日期格式错误';
    else {
        $m['release_date'] = $m['release_date'] ?: null;
        $v = array_values($m);
        if ($id) {
            $v[] = $id;
            db()->prepare('UPDATE movies SET title=?,category_id=?,cover=?,description=?,actors=?,release_date=?,video_url=? WHERE id=?')->execute($v);
        } else {
            db()->prepare('INSERT INTO movies (title,category_id,cover,description,actors,release_date,video_url) VALUES (?,?,?,?,?,?,?)')->execute($v);
        }
        redirect('index.php');
    }
}
$cats = db()->query('SELECT * FROM category ORDER BY id')->fetchAll();
render('header.html', ['title' => $id ? '编辑电影' : '新增电影', 'base' => '../', 'isAdmin' => true]);
?>
<div class="box form"><h2><?= $id ? '编辑' : '新增' ?>电影</h2>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label>片名</label><input type="text" name="title" value="<?= e($m['title']) ?>" required>
<label>分类</label><select name="category_id"><option value="0">未分类</option>
<?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $m['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
<label>封面图地址</label><input type="text" name="cover" value="<?= e($m['cover']) ?>">
<label>上映时间</label><input type="date" name="release_date" value="<?= e($m['release_date']) ?>">
<label>演员（逗号分隔）</label><input type="text" name="actors" value="<?= e($m['actors']) ?>">
<label>视频地址（mp4/webm 直链或播放器页面地址）</label><input type="text" name="video_url" value="<?= e($m['video_url']) ?>">
<label>简介</label><textarea name="description" rows="5"><?= e($m['description']) ?></textarea><br>
<button>保存</button> <a class="btn gray" href="index.php">返回</a></form></div>
<?php render('footer.html'); ?>
