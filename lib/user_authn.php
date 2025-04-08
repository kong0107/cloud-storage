<?php
require_once __DIR__ . '/init.php';

if (isset($_COOKIE['at_hash'])) {
	$current_user = json_file_get(__DIR__ . '/../var/tokens.json', $_COOKIE['at_hash']);
	if (empty($current_user)) {
		site_log('找不到權杖');
		set_cookie('at_hash', '', -1, CONFIG['site.base']);
	}
}

if (isset($current_user) && $current_user->exp < $_SERVER['REQUEST_TIME']) {
	site_log("$current_user->email 的權杖已逾期");
	// set_cookie('at_hash', '', -1, CONFIG['site.base']);
	if ($current_user->refresh_token) {
		$time = microtime(true);
		$res = curl_fetch('https://oauth2.googleapis.com/token', array(
			'method' => 'POST',
			'body' => array(
				'grant_type' => 'refresh_token',
				'client_id' => CONFIG['google.id'],
				'client_secret' => CONFIG['google.secret'],
				'refresh_token' => $current_user->refresh_token
			)
		));
		site_log('重整權杖花了 %d 毫秒', 1000 * (microtime(true) - $time));
		if ($res['errno']) {
			site_log($res);
			unset($current_user);
		}
		else {
			site_log('重整權杖成功');
			$res['body'] = json_decode($res['body']);
			json_file_write('./var/last_refresh_token.json', $res);
			$current_user->access_token = $res['body']->access_token;
			$current_user->exp = $_SERVER['REQUEST_TIME'] + $res['body']->expires_in;
			json_file_set(__DIR__ . '/../var/tokens.json', $_COOKIE['at_hash'], $current_user);
		}
	}
	else {
		set_cookie('at_hash', '', -1, CONFIG['site.base']);
		unset($current_user);
	}
}
