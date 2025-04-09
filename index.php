<?php
require_once './lib/user_authn.php';

$path = substr($_SERVER['REQUEST_URI'], strlen(CONFIG['site.base']) + 6);
// 若是目錄，前端要自動加上斜線（後端轉址會要寫死 RewriteBase ，比較麻煩）

require 'html-header.php';
?>

Request Path: <?= $path ?>

<?php require 'html-footer.php'; ?>
