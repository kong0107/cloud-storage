<?php
/// 本頁完全不須驗證身分
require_once __DIR__ . '/../lib/init.php';
assert_session_start();

$token = base64url_encode(random_bytes(24));
$_SESSION['csrf'] = $token;

header('Cache-Control: no-store');
exit_text($token);
