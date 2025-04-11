<?php
require '../lib/user_authn.php';
assert_login();
if (ini_parse_quantity(ini_get('upload_max_filesize')) < 1048576)
	finish(500, '伺服器支援的「可上傳尺寸」小於 1MB');

/**
 * Required fields:
 * * $_POST: path, size, offset
 * * $_FILES: chunk
 */

if (empty($_POST['path']) || str_contains($_POST['path'], '..')) finish(403);
if (empty($_FILES) || empty($_FILES['chunk'])) finish(403);

$chunk = $_FILES['chunk'];
if ($chunk['error']) finish(500, array('OK', 'INI_SIZE', 'FORM_SIZE', 'PARTIAL', 'NO_FILE', 'unknown', 'NO_TMP_DIR', 'CANT_WRITE', 'EXTENSION')[$file['error']]);

$hashfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $chunk['name'];
if (is_file($hashfile)) {
	if (empty($_POST['offset'])
		|| intval($_POST['offset']) !== filesize($hashfile)
	) finish(403, 'offset not match');
}
else if (! empty($_POST['offset'])) finish(403, 'part not exists');

file_put_contents($hashfile, file_get_contents($chunk['tmp_name']), FILE_APPEND | LOCK_EX);
$progress = filesize($hashfile);

if ($progress === intval($_POST['size'])) {
	$target = CONFIG['dir.storage'] . $_POST['path'];
	if (is_file($target)) {
		if (md5_file($target) === md5_file($hashfile)) {
			unlink($hashfile);
			finish(304, '已有相同內容與名稱的檔案');
		}
		if ($ext = pathinfo($target, PATHINFO_EXTENSION)) {
			$target = substr($target, 0, - strlen($ext) - 1)
				. '+' . substr($chunk['name'], 0, 7)
				. ".$ext"
			;
		}
		else $target .= '+' . substr($chunk['name'], 0, 7);
	}

	$success = rename($hashfile, $target);
	if (! $success) finish(500, '檔案搬移失敗');

	site_log("$current_user->email 上傳了 " . substr($target, strlen(CONFIG['dir.storage'])));
}

exit_json(array('progress' => $progress));
