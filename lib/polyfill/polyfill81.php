<?php
/**
 * Polyfill to use functions new in PHP 8.1.
 *
 * Following new features in PHP 8.1 are NOT implemented:
 * * attribute ReturnTypeWillChange
 * * class CURLStringFile
 * * function enum_exists
 * * function sodium_crypto_stream_xchacha20_xor
 *
 * @see https://www.php.net/releases/8.1/
 */
if (PHP_VERSION_ID < 80100) {

if (! defined('MYSQLI_REFRESH_REPLICA')) {
	define('MYSQLI_REFRESH_REPLICA', MYSQLI_REFRESH_SLAVE);
}

if (! function_exists('array_is_list')) {
/**
 * Checks whether a given array is a list
 *
 * @param array $array The array being evaluated.
 *
 * @return bool Returns `true` if `array` is a `list`, `false` otherwise.
 */
function array_is_list($array) {
	$count = count($array);
	for ($i = 0; $i < $count; ++$i) {
		if (! array_key_exists($i, $array)) return false;
	}
	return true;
}
} // function array_is_list

} // if (PHP_VERSION_ID < 80100)
