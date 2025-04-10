<?php
require_once __DIR__ . '/lib/user_authn.php';
assert_login();

$path = substr($_SERVER['REQUEST_URI'], strlen(CONFIG['site.base']) + 3);
$fullpath = CONFIG['dir.storage'] . $path;
if (! file_exists($fullpath)) finish(404);

$last_modified = gmdate(DATE_RFC7231, filemtime($fullpath));
header("Last-Modified: $last_modified");
if ($last_modified === $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? 0) finish(304);

header('Cache-Control: max-age=0, must-revalidate, private');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($fullpath));
header('Content-Type: ' . mime_content_type($fullpath));

while (ob_get_level()) ob_end_clean();
readfile($fullpath);
