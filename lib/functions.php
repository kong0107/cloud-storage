<?php
/**
 * Functions for this project.
 */
foreach (array('polyfill', 'utility') as $dir) {
	foreach (scandir(__DIR__ . "/$dir") as $filename) {
		if (substr($filename, -4) !== '.php') continue;
		require_once __DIR__ . "/$dir/$filename";
	}
}


/**
 * Append message to `error_log`
 * @param mixed $target
 * @param mixed ...$values Used as arguments for `sprintf` if and only if `target` is string
 * @return int bytes written
 */
function site_log($target, ...$values) {
	$message = is_string($target)
		? (count($values) ? sprintf($target, ...$values) : $target)
		: var_export($target, true)
	;

	$filepath = ini_get('error_log');
	if (empty($filepath) || $filepath === 'syslog') {
		error_log($message);
		return strlen($message);
	}

	$time_req = date('ymd_His', $_SERVER['REQUEST_TIME']) . substr($_SERVER['REQUEST_TIME_FLOAT'], 10, 4);
	$request_uri = substr($_SERVER['REQUEST_URI'], strlen(CONFIG['site.base']) - 1);
	$time_diff = number_format(1000 * (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 3);
	return file_put_contents(
		$filepath,
		"$time_req $request_uri\n{$time_diff}ms\t$message\n\n",
		FILE_APPEND | LOCK_EX
	);
}


/**
 * Error handler to be set by `set_error_handler()`
 * @param int $number
 * @param string $message
 * @param string $filepath
 * @param int $line
 * @return true To skip the normal error handler
 */
function error_handler($number, $message, $filepath, $line) {
	$type_name = array(
		'Error', 'Warning', 'Parse', 'Notice',
		'CoreError', 'CoreWarning', 'CompileError', 'CompileWarning',
		'UserError', 'UserWarning', 'UserNotice',
		'Strict', 'RecoverableError', 'Deprecated', 'UserDeprecated'
	)[intlog($number)];
	site_log("$type_name: $message\nin $filepath:$line");
	return true;
}


/**
 * Exception handler to set by `set_exception_handler()`
 * @param Throwable $ex
 * @param string $title Not used if called by `set_exception_handler()`
 * @return never
 */
function exception_handler($ex, $title = '') {
	$str = get_class($ex);
	if ($code = $ex->getCode()) $str .= "#$code";
	if ($msg = $ex->getMessage()) $str .= ": $msg";
	site_log($str . chr(10) . $ex->getTraceAsString());
	finish(500, $title);
}


/**
 * Function to set by `register_shutdown_function()`
 * @return void
 */
function shutdown_function() {
	global $current_user;
	$str = sprintf('%s + %s bytes sent; mem peak %s bytes',
		number_format(ob_get_length()),
		number_format(array_reduce(headers_list(), fn ($sum, $h) => $sum + strlen($h), 0)),
		number_format(memory_get_peak_usage())
	);

	$request_info = array(
		'user' => isset($current_user) ? $current_user->email : null,
		'tcp_ip' => $_SERVER['REMOTE_ADDR']
	);
	if (preg_match_all('/(Chrome|Firefox|Edge?|Safari|Opera)\/\d+\.\d+/i', $_SERVER['HTTP_USER_AGENT'], $matches))
		$request_info['Navigator'] = array_pop($matches[0]);
	else $request_info['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];

	foreach (array('X-Forwarded-For', 'X-Real-IP', 'Client-IP', 'GeoIP-Country-Code', 'CF-Connecting-IP', 'True-Client-IP', 'Origin', 'Forwarded', 'Via') as $header_name) {
		$key = 'HTTP_' . strtr(strtoupper($header_name), '-', '_');
		if (isset($_SERVER[$key])) $request_info[$header_name] = $_SERVER[$key];
	}
	$str .= "\n" . json_encode_fake($request_info);

	if (! empty($_POST)) {
		$copy = $_POST; // 去掉敏感資訊
		unset($copy['csrf']);
		if (isset($copy['password'])) $copy['password'] = null;
		if (isset($copy['password-again'])) $copy['password-again'] = null;
		$str .= "\n\$_POST = " . json_encode_fake($copy);
	}
	if (! empty($_FILES)) {
		$copy = array();
		foreach ($_FILES as $file) {
			unset($file['full_path']); // 這個不可信又占空間
			$copy[] = $file;
		}
		$str .= "\n\$_FILES = " . json_encode_fake($copy);
	}
	site_log($str);

	if ($err = error_get_last()) {
		error_handler($err['type'], $err['message'], $err['file'], $err['line']);
		finish(500);
	}
}


/**
 * Exit after sending an HTTP status code, maybe with error message
 * @see https://jsonapi.org/format/#error-objects
 * @param int $status
 * @param string $title
 * @param mixed $meta
 * @return never
 */
function finish($status = 204, $title = '', $meta = null) {
	$obj = array(
		'status' => (string) $status
	);
	if ($title) $obj['title'] = $title;

	if ($meta) {
		if (gettype($meta) === 'string') {
			$obj['detail'] = $meta;
			site_log("$status $title\n$meta");
		}
		else {
			$obj['meta'] = $meta;
			site_log("$status $title");
			site_log($meta);
		}
	}
	else if ($title) site_log("$status $title");

	if ($status < 400) {
		http_response_code($status);
		exit(0);
	}

	/// 若是伺服器錯誤，也記一下 cookie 和 session 的狀態
	if ($status >= 500) {
		$message = '$_COOKIE = ' . json_encode_fake($_COOKIE);
		if (session_status() === PHP_SESSION_ACTIVE)
			$message .= "\n\$_SESSION = " . json_encode_fake($_SESSION);
		site_log($message);
	}

	exit_json(array('errors' => array($obj)), $status);
}
