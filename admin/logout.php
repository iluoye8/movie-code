<?php
/*
作者：iluoye8
网站：http://m.ksks63.com/
*/
?>
<?php
session_start();
session_destroy();
header('Location: login.php');
