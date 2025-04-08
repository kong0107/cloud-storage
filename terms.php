<?php
require_once './lib/start.php';
$Get->convert();

$page_info = [
	'title' => '服務條款',
	'description' => CONFIG['site.name'] . '的服務條款',
];

require 'html-header.php';
// 可參考： https://ecssl.pchome.com.tw/sys/cflowex/index/staticPage/CLAUSE
?>

<div class="markdown">
	<?php readfile('var/markdown/terms.md'); ?>
</div>

<?php require 'html-footer.php'; ?>
