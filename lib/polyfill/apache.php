<?php
/**
 * Polyfill to use functions only exist on Apache.
 *
 * @see https://www.php.net/manual/book.apache.php
 *
 * This file has implemented:
 * * function getallheaders
 *
 */


/**
 * Fetch all HTTP request headers
 * @return array An associative array of all the HTTP headers in the current request.
 */
if (! function_exists('getallheaders')) {
function getallheaders() {
	$headers = array();
	foreach ($_SERVER as $key => $value) {
		if (str_starts_with($key, 'HTTP_')) $key = substr($key, 5);
		else if (! str_starts_with($key, 'CONTENT_')) continue;
		$key = strtr(ucwords(strtolower($key), '_'), '_', '-');
		$headers[$key] = $value;
	}
	if (empty($headers['Authorization'])) {
		$value = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $_SERVER['PHP_AUTH_DIGEST'] ?? null;
		if (! $value && ($user = $_SERVER['PHP_AUTH_USER'] ?? null)) {
			$pw = $_SERVER['PHP_AUTH_PW'] ?? '';
			$value = 'Basic ' . base64_encode("$user:$pw");
		}
		if ($value) $headers['Authorization'] = $value;
	}
	return $headers;
}
} // function getallheaders
