<?php
/**
 * Polyfill to use functions new in PHP 8.2.
 *
 * Following new features in PHP 8.2 are NOT implemented:
 * * attribute AllowDynamicProperties
 * * attribute SensitiveParameter
 * * method mysqli::execute_query
 * * methods ZipArchive::getStreamIndex, ZipArchive::getStreamName, ZipArchive::clearError
 * * methods ReflectionFunction::isAnonymous, ReflectionMethod::hasPrototype
 * * function curl_upkeep
 * * function memory_reset_peak_usage
 * * function libxml_get_external_entity_loader
 * * function sodium_crypto_stream_xchacha20_xor_ic
 * * functions odbc_connection_string_*quote*
 *
 * @see https://www.php.net/releases/8.2/
 */
if (PHP_VERSION_ID < 80200) {

if (! defined('MYSQLI_REFRESH_REPLICA')) {
	define('MYSQLI_REFRESH_REPLICA', MYSQLI_REFRESH_SLAVE);
}

if (! function_exists('mysqli_execute_query')) {
/**
 * Prepares, binds parameters, and executes SQL statement
 *
 * @param mysqli $mysql A mysqli object returned by `mysqli_connect()` or `mysqli_init()`
 * @param string $query The query, as a string. It must consist of a single SQL statement.
 * @param ?array $params An optional list array with as many elements as there are bound parameters in the SQL statement being executed. Each value is treated as a string.
 *
 * @return mysqli_result|bool
 * 	Returns `false` on failure.
 *  For successful queries which produce a result set,
 *  such as `SELECT`, `SHOW`, `DESCRIBE` or `EXPLAIN`,
 *  returns a `mysqli_result` object.
 *  For other successful queries, returns `true`.
 */
function mysqli_execute_query($mysql, $query, $params = null) {
	// $method = strtoupper(explode(' ', trim($query), 2)[0]);
	$stmt = mysqli_prepare($mysql, $query);
	if (! $stmt) return false;
	if (is_array($params)) {
		$success = $stmt->bind_param(
			str_repeat('s', count($params)),
			...$params
		);
		if (! $success) return false;
	}
	$success = $stmt->execute();
	if (! $success) return false;
	/**
	 * `mysqli_stmt::execute()` did NOT support `params` argument until PHP 8.1.
	 * Therefore as a polyfill, we shall bind the params seperately.
	 */

	$result = $stmt->get_result();
	return $result ?: true;
	/**
	 * `mysqli_stmt::get_result()` returns `false` on successful queries without a result set.
	 */
}
} // function mysqli_execute_query


if (! function_exists('ini_parse_quantity')) {
/**
 * Get interpreted size from ini shorthand syntax
 *
 * @param string $shorthand Ini shorthand to parse, must be a number followed by an optional multiplier.
 *
 * @return int Returns the interpreted size in bytes as an int.
 */
function ini_parse_quantity($shorthand) {
	$shorthand = strtolower($shorthand);
	preg_match('/^(0(x|o|b)?)?([\da-f]+(k|m|g)?$/', $shorthand, $matches);
	if (! $matches) return 0;
	$base = 10;
	if ($matches[1]) {
		if ($matches[2] === 'x') $base = 16;
		else if ($matches[2] === 'b') $base = 2;
		else $base = 8;
	}
	$result = intval($matches[3], $base);
	switch ($matches[4]) {
		case 'k': return $result * 1024;
		case 'm': return $result * 1048576;
		case 'g': return $result * 1073741824;
		default: return $result;
	}
}
} // function ini_parse_quantity


if (! function_exists('openssl_cipher_key_length')) {
/**
 * Gets the cipher key length
 *
 * @see https://tubring.cn/articles/php82-new-openssl_cipher_key_length-function
 *
 * @param string $cipher_algo
 *
 * @return int|false Returns the cipher length on success, or `false` on failure.
 */
function openssl_cipher_key_length($cipher_algo) {
	if (! in_array($cipher_algo, openssl_get_cipher_methods())) return false;
	$parts = explode('-', $cipher_algo);
	switch ($parts[0]) {
		case 'aes':
		case 'aria':
		case 'camellia': {
			$length = intval($parts[1]) / 8;
			if ($parts[2] === 'xts') $length *= 2;
			return $length;
		}
		case 'chacha20': return 32;
		case 'des': {
			switch ($parts[1]) {
				case 'ede': return 16;
				case 'ede3': return 24;
				default: return false;
			}
		}
		case 'des3': return 24;
		case 'sm4': return 16;
		default: return false;
	}
}
} // function openssl_cipher_key_length

} // if (PHP_VERSION_ID < 80200)
