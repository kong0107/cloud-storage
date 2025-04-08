<?php
/**
 * 本檔案處理登入與登出的操作，但不包含登入狀態的驗證。
 *
 * @see https://developers.google.com/identity/protocols/oauth2/web-server
 * @see https://developers.google.com/identity/openid-connect/openid-connect
 */
require_once './lib/init.php';

/// 登出
if (isset($_GET['logout'])) {
	set_cookie('at_hash');
	if (isset($current_user)) site_log("$current_user->email 主動登出了。");
	if (isset($_COOKIE['at_hash'])) json_file_set('./var/tokens.json', $_COOKIE['at_hash']);
	header('Location: .');
	exit(0);
}

$redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . CONFIG['site.base'] . CONFIG['google.redirect_uri'];
assert_session_start();

/// 如果是直接連來這一頁，那就轉去 Google 的登入頁
if (empty($_GET['state'])) {
	$state = $_SESSION['csrf'] = base64url_encode(random_bytes(24));
	if (! empty($_SERVER['HTTP_REFERER'])) {
		$parts = parse_url($_SERVER['HTTP_REFERER']);
		if ($parts['host'] === $_SERVER['HTTP_HOST']
			&& ! str_ends_with($parts['path'], 'login.php')
		) {
			$referer = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], '/', 10));
			$state .= $referer;
		}
	} // append redirect target after csrf token

	$query = http_build_query(array(
		'access_type' => 'offline',
		'client_id' => CONFIG['google.id'],
		'redirect_uri' => $redirect_uri,
		'response_type' => 'code',
		'scope' => 'openid profile email',
		'state' => $state
	));
	header("Location: https://accounts.google.com/o/oauth2/auth?$query");
	exit(0);
}

/// 處理 OAuth2 登入
$csrf = $_SESSION['csrf'];
unset($_SESSION['csrf']);
if (! str_starts_with($_GET['state'], $csrf)) {
	site_log('CSRF validation failed: no match.');
	header('Location: login.php');
	exit(0);
}

$time = microtime(true);
$res = fetch_curl('https://oauth2.googleapis.com/token', array(
	'method' => 'POST',
	'body' => array(
		'grant_type' => 'authorization_code',
		'client_id' => CONFIG['google.id'],
		'client_secret' => CONFIG['google.secret'],
		'code' => $_GET['code'],
		'redirect_uri' => $redirect_uri
	)
));
site_log('請求權杖花了 %d 毫秒', 1000 * (microtime(true) - $time));
if (isset($res['errno'])) {
	site_log($res);
	finish(401, '登入失敗。');
}

$res['body'] = json_decode($res['body']);
$id_token = $res['body']->id_token = jwt_decode($res['body']->id_token)->payload;
json_file_write('./var/last_access_token.json', (object) $res);


/**
 * 直接拿 at_hash 當 key ，存在 Cookie ；其他需要的則存在伺服器。
 * Cookie 存活的時間要比 token 長，伺服器才有可能知道要去確認 token 是否過期。
 */
set_cookie('at_hash', $id_token->at_hash, 3600 * 168);

json_file_set('./var/tokens.json', $id_token->at_hash, array(
	'access_token' => $res['body']->access_token,
	'refresh_token' => property_exists($res['body'], 'refresh_token') ? $res['body']->refresh_token : null,
	'exp' => $id_token->exp,
	'email' => $id_token->email,
	'name' => $id_token->name
));

site_log("$id_token->email 登入成功");
$referer = substr($_GET['state'], strlen($csrf));
header('Location: ' . ($referer ?: '.'));
