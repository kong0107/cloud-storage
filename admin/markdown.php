<?php
require_once 'authentication.php';

$Get->convert(['name' => 'string']);
$Post->convert(['content' => 'string']);

$files = [
	'about' => '關於我們',
	'terms' => '服務條款',
	'privacy' => '隱私權政策'
];

if ($content = trim($Post->content)) {
	if (! array_key_exists($Get->name, $files))
	    exit('no such file');
	if (! file_put_contents("../var/markdown/{$Get->name}.md", $content . "\r\n"))
	    exit('write failure');
	$message = date('m/d H:i:s ') . '儲存';
}

require 'html-header.php';
?>

<script id="marked" src="https://cdn.jsdelivr.net/npm/marked@v4.3.0/marked.min.js"></script>

<h1 class="fs-3">文檔管理</h1>

<?php if (isset($message)): ?>
	<span class="badge text-bg-success"><?= $message ?></span>
<?php endif; ?>

<?php if ($Get->name): ?>
	<form method="post" class="row">
	    <div class="col-md-6">
	        <header>
	            檔案內容
	            （使用<a href="https://markdown.tw/#header" target="_blank">Markdown 語法</a>）
	        </header>
	        <textarea name="content" class="form-control"
	        ><?= file_get_contents("../var/markdown/{$Get->name}.md") ?></textarea>
	    </div>
	    <div class="col-md-6">
	        <header>顯示結果</header>
	        <div id="parsed"></div>
	    </div>
	    <button type="submit" disabled class="btn btn-primary w-50 mx-auto mt-3">儲存</button>
	</form>

	<script>
	    listen($('textarea'), 'input', () => {
	        const content = $('textarea').value;
	        $('#parsed').innerHTML = marked.parse(content);
	        $('textarea').rows = Math.ceil(content.split('\n').length * 1.5);
	    });
	    $('textarea').dispatchEvent(new Event('input'));

	    listen($('textarea'), 'input', () => {
	        $('button[disabled]').removeAttribute('disabled');
	    }, {once: true});
	</script>
<?php else: ?>
	<div class="row">
	    <?php foreach ($files as $name => $title): ?>
	        <section class="mb-4 col-lg-4 px-3 border-start border-success">
	            <div class="markdown mb-3" style="height: 50vh; overflow-y: auto;"
	            ><?= file_get_contents("../var/markdown/$name.md") ?></div>
	            <a class="btn btn-info" href="admin/markdown.php?name=<?= $name ?>">編輯</a>
	        </section>
	    <?php endforeach; ?>
	</div>
<?php endif; ?>

<?php require 'html-footer.php'; ?>
