<?php
require_once 'authentication.php';

if (strpos($Get->file, '/') !== false) {
	http_response_code(403);
	exit;
}

$path = sprintf('../file/%s/%s', substr($Get->file, 0, 4), $Get->file);
if (! unlink($path)) http_response_code(500);
