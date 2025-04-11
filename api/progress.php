<?php
require '../lib/user_authn.php';
assert_login();

if (empty($_GET['hash']) || str_contains($_GET['hash'], '/')) finish(403);
$fullpath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $_GET['hash'];

exit_json(array(
	'size' => file_exists($fullpath) ? filesize($fullpath) : 0
));
