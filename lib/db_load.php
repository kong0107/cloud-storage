<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/class/PDOi.php';

try {
	$db = new PDOi(
		CONFIG['dsn.driver'],
		array(
			'host' => CONFIG['dsn.host'],
			'dbname' => CONFIG['dsn.dbname'],
			'charset' => CONFIG['dsn.charset']
		),
		CONFIG['pdo.username'],
		CONFIG['pdo.password']
	);
	$db->exec(sprintf("SET time_zone = '%s';", date('P')));
}
catch (PDOException $ex) {
	exception_handler($ex, 'Database Error');
}
