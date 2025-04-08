<?php
/**
 * 找出未被引用的圖檔。
 * 1. 列出所有圖檔
 * 2. 查詢資料表 ImageObject
 * 3. 逐筆確認 Product.description 。
 */
require_once 'authentication.php';

// 列出所有圖檔
$files = [];
foreach (scandir('../file/') as $dir) {
	if (str_starts_with($dir, '.')) continue;
	$files = array_merge($files, scandir("../file/$dir"));
}
$files = array_diff($files, ['.', '..']);

// 列出資料表 ImageObject 裡頭的，然後從 $files 裡挑掉。
$sql = 'SELECT DISTINCT(SUBSTRING(contentUrl, 11)) FROM ImageObject WHERE contentUrl NOT LIKE \'%://%\'';
$files = array_diff($files, $db->get_col($sql));

/**
 * 逐筆確認 Product.description
 * 因為資料量大，故改成一筆一筆看。
 */
$result = $db->query('SELECT description FROM Product WHERE description LIKE \'% src="file/%\'');
while($row = $result->fetch_row()) {
	preg_match_all('/ src="file\/\\d{4}\/([\\d_]+\\.\\w+)"/', $row[0], $matches);
	$files = array_diff($files, $matches[1]);
}

require 'html-header.php';
?>

<h1 class="fs-3">未被使用的圖片</h1>
<?php if (! count($files)): ?>
	<p class="text-muted">沒有偵測到未被使用的圖片。</p>
<?php endif; ?>
<?php foreach ($files as $basename): ?>
	<details>
	    <summary><?= $basename ?></summary>
	    <button class="btn btn-warning">刪除</button>
	    <br>
	    <img loading="lazy" src="file/<?= substr($basename, 0, 4) ?>/<?= $basename ?>">
	</details>
<?php endforeach; ?>

<script>
	$$('details > button.btn-warning').forEach(btn => {
	    listen(btn, 'click', () => {
	        const path = $('img', btn.parentNode).src;
	        const basename = path.split('/').pop();
	        fetchStrict('admin/delete.php?file=' + basename)
	        .then(() => {
	            console.info('成功刪除了 ' + basename);
	            btn.parentNode.remove();
	        }, alerter('刪除失敗'));
	    })
	});
</script>

<?php require 'html-footer.php'; ?>
