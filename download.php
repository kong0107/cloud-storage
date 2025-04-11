<?php
require_once __DIR__ . '/lib/user_authn.php';
assert_login();

$path = substr($_SERVER['REQUEST_URI'], strlen(CONFIG['site.base']) + 3);
$fullpath = CONFIG['dir.storage'] . urldecode($path);
if (! file_exists($fullpath)) finish(404);

/// 靜態的標頭
header('Cache-Control: max-age=0, must-revalidate, private');
header('Accept-Ranges: bytes');
header('Content-Encoding: none');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');


/// 關掉所有緩衝和壓縮
if (function_exists('apache_setenv') && ! apache_setenv('no-gzip', '1'))
	site_log('Warning: `apache_setenv()` failed.');
if (ini_get('zlib.output_compression') && ! ini_set('zlib.output_compression', false))
	site_log('Warning: failed to turn off `zlib.output_compression`');
while (ob_get_level()) ob_end_clean();
ob_implicit_flush();


/// 確認檔案日期、類型、尺寸
$last_modified = gmdate(DATE_RFC7231, filemtime($fullpath));
header("Last-Modified: $last_modified");
if ($last_modified === ($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? 0)) finish(304);
$mime = mime_content_type($fullpath);
$size = filesize($fullpath);


/// 若不適用範圍請求，這裡就可以輸出
if (empty($_SERVER['HTTP_RANGE'])
	|| (isset($_SERVER['HTTP_IF_RANGE']) && $_SERVER['HTTP_IF_RANGE'] !== $last_modified)
) {
	header("Content-Type: $mime");
	header("Content-Length: $size");
	if ($_SERVER['REQUEST_METHOD'] === 'HEAD') finish(200);
	readfile($fullpath);
	exit(0);
}


/// 檢查範圍請求
site_log('Range: ' . $_SERVER['HTTP_RANGE']);
try {
	if (! str_starts_with($_SERVER['HTTP_RANGE'], 'bytes='))
		throw new UnexpectedValueException('`Range` 須以 "bytes=" 開頭');
	$parts = array();
	$parts_orig = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
	foreach ($parts_orig as $i => $part_text) {
		$pair = explode('-', trim($part_text));
		if (count($pair) !== 2)
			throw new UnexpectedValueException('每段範圍必有減號');
		if ($pair[0] === '' && count($parts_orig) > 1)
			throw new UnexpectedValueException('多段傳輸不能省去開頭');
		if ($pair[1] === '' && $i !== count($parts_orig) - 1)
			throw new UnexpectedValueException('僅有末段可以省去結尾');
		if ($pair[0] === '' && $pair[1] === '')
			throw new UnexpectedValueException('頭尾你都不指定，是在搞笑吧');

		$parts[] = ($pair[0] === '')
			? array($size - intval($pair[1]), $size - 1)
			: array(intval($pair[0]), $pair[1] ? intval($pair[1]) : ($size - 1))
		;
	}
}
catch (UnexpectedValueException $ex) {
	finish(400, 'UnexpectedValueException', $ex->getMessage());
}

foreach ($parts as $part) {
	if ($part[0] > $part[1] || $part[1] >= $size) {
		header("Content-Range: bytes */$size");
		finish(416, 'RangeException');
	}
}


/// 檢查完畢，終於可以開始讀檔了
if (count($parts) === 1) {
	list($start, $end) = $parts[0];
	header("Content-Type: $mime");
	header('Content-Length: ' . ($end - $start + 1));
	header("Content-Range: bytes $start-$end/$size");

	http_response_code(206);
	if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit(0);

	$fp = fopen($fullpath, 'rb');
	if (! $fp || fseek($fp, $start)) finish(500);
	for ($pos = $start; $pos <= $end; $pos = ftell($fp)) {
		echo fread($fp, min(8192, $end - $pos + 1));
	}
	exit(fclose($fp) ? 0 : 1);
}


/// 多範圍的情形
$boundary = base64url_encode(random_bytes(51));
header("Content-Type: multipart/byteranges; boundary=$boundary");

$length = array_reduce($parts, function ($carry, $pair) {
	list ($start, $end) = $pair;
	$part_length = ($end - $start + 1)
		+ strlen("\r\n--$boundary\r\nContent-Type: $mime\r\nContent-Range: bytes $start-$end/$size\r\n\r\n");
	return $carray + $part_length;
}, strlen($boundary) + 4);
header("Content-Length: $length");

http_response_code(206);
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit(0);

$fp = fopen($fullpath, 'rb');
if (! $fp) finish(500);
foreach ($parts as $pair) {
	list ($start, $end) = $pair;
	if (fseek($fp, $start)) {
		site_log('Error: fseek() failed');
		exit(1);
	}
	echo "--$boundary\r\nContent-Type: $mime\r\nContent-Range: bytes $start-$end/$size\r\n\r\n";
	for ($pos = $start; $pos <= $end; $pos = ftell($fp)) {
		echo fread($fp, min(8192, $end - $pos + 1));
	}
	echo "\r\n";
}
echo "--$boundary--\r\n";
exit(fclose($fp) ? 0 : 1);
