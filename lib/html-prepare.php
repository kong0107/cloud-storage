<?php
require_once __DIR__ . '/user_authn.php';

$meta_name_content = array(
	'viewport' => 'width=device-width, initial-scale=1.0',
	'referrer' => 'same-origin',
	'author' => 'rich.dog.studio@gmail.com'
);

$link_href = array(
	'assets/main.css'
);

$script_src_afterbegin = array(
	'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
	'https://cdn.jsdelivr.net/npm/kong-util@0.8.14/dist/all.js',
	'assets/afterbegin.js'
);

$script_src_beforeend = array(
	'assets/beforeend.js'
);

function append_mtime($path) {
	if (! str_starts_with($path, 'https://')) {
		$real = realpath(__DIR__ . "/../$path");
		if ($real) $path .= '?' . (filemtime($real) - 937849636); //base64url_encode(hex2bin(md5_file($real)));
		else site_log("Failed to access $real");
	}
	return $path;
}
