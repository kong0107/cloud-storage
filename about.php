<?php
require_once './lib/start.php';
$Get->convert();

$page_info = [
	'title' => '關於本站',
	'description' => '',
];

require 'html-header.php';
// 本檔之格式同於 `privacy.php` 和 `terms.php` 。
?>

<div class="markdown">
	<?php readfile('var/markdown/about.md'); ?>
</div>

<?php require 'html-footer.php'; ?>
