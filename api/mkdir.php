<?php
require '../lib/user_authn.php';
assert_login();

if (empty($_POST['path']) || str_contains($_POST['path'], '../')) finish(403);
$target = CONFIG['dir.storage'] . $_POST['path'];

if (mkdir($target, 0644)) {
	header('Location: ' . URL_BASE . 'browse/' . $_POST['path'], true, 201);
	exit(0);
}

site_log("Failed to mkdir $target");
finish(403);
