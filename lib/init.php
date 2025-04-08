<?php
header_remove('X-Powered-By');

require_once __DIR__ . '/functions.php';

set_error_handler('error_handler');
set_exception_handler('exception_handler');
register_shutdown_function('shutdown_function');

if (ini_get('user_ini.filename') !== '.user.ini') {
	foreach (parse_ini_file(__DIR__ . '/../.user.ini') as $key => $value)
		safe_ini_set($key, $value);
}

define('DIR_VAR', realpath(__DIR__ . '/../var'));
if (! file_exists(DIR_VAR . '/config.ini'))
	copy(DIR_VAR . '/config.ini.sample', DIR_VAR . '/config.ini');

define('CONFIG', parse_ini_file(DIR_VAR . '/config.ini'));
define('URL_BASE', 'https://' . $_SERVER['HTTP_HOST'] . CONFIG['site.base']);

foreach (array('logs', 'uploads') as $dirname) {
	$dirpath = CONFIG["dir.$dirname"];
	if (! str_starts_with($dirpath, '/') && substr($dirpath, 1, 1) !== ':') {
		$dirpath = __DIR__ . "/../$dirpath";
	}
	if (! is_dir($dirpath)) {
		$success = mkdir($dirpath, 0644, true);
		if (! $success) {
			error_log("Failed to make directory $dirpath");
			finish(500);
		}
	}
	define('DIR_' . strtoupper($dirname), realpath($dirpath));
}

ini_set('error_log', DIR_LOGS . DIRECTORY_SEPARATOR . date('ymd') . '.log');
