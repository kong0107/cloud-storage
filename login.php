<?php
/**
 * 本檔案處理登入與登出的操作，但不包含登入狀態的驗證。
 *
 * @see https://developers.google.com/identity/protocols/oauth2/web-server
 * @see https://developers.google.com/identity/openid-connect/openid-connect
 */
require_once './lib/init.php';
$file_tokens = './var/tokens.json';

/// 登出
if (isset($_GET['logout'])) {
	/// 在這邊不要引入 user_authn ，而是遍歷整個檔，順手把過期的登入狀態都清一清，好像也不錯？
	require_once './lib/user_authn.php';
	set_cookie('at_hash', '', -1, CONFIG['site.base']);
	if (isset($current_user)) site_log("$current_user->email 主動登出了。");
	if (isset($_COOKIE['at_hash'])) json_file_set_prop($file_tokens, $_COOKIE['at_hash']);
	redirect(CONFIG['site.base']);
}

$redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . CONFIG['site.base'] . CONFIG['google.redirect_uri'];
assert_session_start();

/// 如果是直接連來這一頁，那就轉去 Google 的登入頁
if (empty($_GET['state'])) {
	$state = $_SESSION['csrf'] = base64url_encode(random_bytes(24));

	/// append redirect target after csrf token
	if (str_starts_with($_SERVER['HTTP_REFERER'] ?? '', URL_BASE)
		&& ! str_contains($_SERVER['HTTP_REFERER'], basename(__FILE__))
	) $state .= substr($_SERVER['HTTP_REFERER'], strlen(URL_BASE));

	$query = http_build_query(array(
		'access_type' => 'offline',
		'client_id' => CONFIG['google.id'],
		'redirect_uri' => $redirect_uri,
		'response_type' => 'code',
		'scope' => 'openid profile email',
		'state' => $state
	));
	redirect("https://accounts.google.com/o/oauth2/auth?$query");
}

/// 處理 OAuth2 登入
$csrf = $_SESSION['csrf'];
unset($_SESSION['csrf']);
if (! str_starts_with($_GET['state'], $csrf)) {
	site_log('CSRF validation failed: no match.');
	redirect($redirect_uri);
}

$time = microtime(true);
$res = curl_fetch('https://oauth2.googleapis.com/token', array(
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
json_file_write('./var/last_access_token.json', $res); // only for debug


/**
 * 直接拿 at_hash 當 key ，存在 Cookie ；其他需要的則存在伺服器。
 * Cookie 存活的時間要比 token 長，伺服器才有可能知道要去確認 token 是否過期。
 */
set_cookie('at_hash', $id_token->at_hash, 3600 * 168, CONFIG['site.base']);
json_file_set_prop($file_tokens, $id_token->at_hash, array(
	// 'access_token' => $res['body']->access_token,
	'refresh_token' => $res['body']->refresh_token ?? null,
	'exp' => $id_token->exp,
	'email' => $id_token->email,
	'name' => $id_token->name
));

site_log("$id_token->email 登入成功");
$referrer = substr($_GET['state'], strlen($csrf));
redirect(CONFIG['site.base'] . $referrer);
