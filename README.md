<!--
作者：iluoye8
网站：http://m.ksks63.com/
-->
<!DOCTYPE html>
<html lang="zh-CN"><head><meta charset="utf-8"><title>网站安装说明文档</title>
<style>body{max-width:760px;margin:30px auto;padding:0 15px;font:15px/1.8 "Microsoft YaHei",sans-serif}code{background:#eee;padding:1px 5px}h2{border-left:4px solid #e50914;padding-left:8px}</style></head><body>
<h1>网站安装说明文档</h1>
<p>作者：iluoye8 ｜ 网站：<a href="http://m.ksks63.com/">http://m.ksks63.com/</a></p>
<h2>一、环境要求</h2>
<ul><li>PHP 7.4 及以上（推荐 8.x），需开启 <code>pdo_mysql</code>、<code>mbstring</code></li><li>MySQL 5.7+ / MariaDB 10.3+</li><li>Apache / Nginx / IIS 均可，网站根目录需有写入权限（安装时生成 config.php）</li></ul>
<h2>二、上传文件</h2>
<p>将全部文件（config.php、install.php、index.php、movie.php、play.php、admin/、inc/、templates/、assets/）上传到网站根目录或子目录。</p>
<h2>三、访问安装页面</h2>
<p>浏览器访问 <code>http://你的域名/install.php</code>。</p>
<h2>四、安装步骤</h2>
<ol><li>查看环境检测，全部显示 ✔ 后继续；</li><li>填写数据库地址、库名、账号、密码（库不存在会尝试自动创建）；</li><li>填写网站名称与管理员账号、密码（至少6位）；</li><li>点击“开始安装”，自动建立 movies、category、admin 三张表并写入 config.php；</li><li>提示安装完成，访问 <code>/admin/login.php</code> 登录后台，先添加分类再添加电影。</li></ol>
<h2>五、安装后安全操作</h2>
<ul><li><b>立即删除 install.php</b>（已安装后该页面也会拒绝访问）；</li><li>如需重新安装：删除 <code>install.lock</code> 并将 config.php 中 <code>INSTALLED</code> 改为 <code>false</code>；</li><li>建议将 config.php 权限设为只读，并使用 HTTPS；管理员使用强密码。</li></ul>
<h2>六、常见报错排查</h2>
<ul>
<li><b>SQLSTATE[HY000] [1045] Access denied</b>：数据库账号或密码错误；</li>
<li><b>[2002] Connection refused / 找不到主机</b>：数据库地址错误或 MySQL 未启动，可尝试 <code>127.0.0.1</code> 代替 <code>localhost</code>；</li>
<li><b>[1044]/[1049] 无权限或库不存在</b>：账号无建库权限时，请先在面板手动创建库再安装；</li>
<li><b>config.php 写入失败</b>：给网站根目录及 config.php 写权限；</li>
<li><b>环境检测 pdo_mysql ✘</b>：在 php.ini 中启用 <code>extension=pdo_mysql</code> 并重启服务；</li>
<li><b>提示“网站已安装”</b>：正常保护机制，见第五条重新安装方法；</li>
<li><b>后台登录后跳回登录页</b>：检查 PHP session 保存目录是否可写；</li>
<li><b>封面/视频地址保存失败</b>：地址需以 http://、https:// 或 / 开头。</li>
</ul>
<p style="color:#888;text-align:center">程序作者：iluoye8 · <a href="http://m.ksks63.com/">http://m.ksks63.com/</a></p>
</body></html>
