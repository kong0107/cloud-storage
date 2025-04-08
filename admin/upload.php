<?php
require_once 'authentication.php';

$Post->convert(['counter' => 'int']);

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) onerror();
// site_log($file);

$type = isset($file['type'])
	? explode('/', $file['type'])[1]
	: 'jpeg'
;

$dir = '../file/' . date('ym');
if (! is_dir($dir)) mkdir($dir, 0664);

$path = sprintf('file/%s/%s_%02d.%s',
	date('ym'), date('ymd_His'), $Post->counter, $type
);

if (move_uploaded_file($file['tmp_name'], '../' . $path))
	echo json_encode(['default' => $path]);
else onerror();


function onerror() {
	site_log('file upload failed');
	site_log($_POST);
	site_log($_FILES);
	site_log(apache_request_headers());
	http_response_code(500);
	echo json_encode(['error' => 'file upload failed']);
	exit;
}
