<?php
/**
 * Polyfill to use functions new in PHP 8.3.
 *
 * Following new features in PHP 8.3 are NOT implemented:
 * * attribute Override
 *
 * @see https://www.php.net/releases/8.3/
 */
if (PHP_VERSION_ID < 80300) {

if (! function_exists('json_validate')) {
/**
 * Checks whether a given array is a list
 *
 * @param string $json The string to validate.
 * @param int $depth Maximum nesting depth of the structure being decoded. The value must be greater than 0, and less than or equal to 2147483647.
 * @param int $flags Currently only `JSON_INVALID_UTF8_IGNORE` is accepted.
 *
 * @throws ValueError
 * @return bool Returns `true` if the given string is syntactically valid JSON, otherwise returns `false`.
 */
function json_validate($array, $depth = 512, $flags = 0) {
	if ($flags && $flags !== JSON_INVALID_UTF8_IGNORE) throw new ValueError;
	json_decode($json, true, $depth, $flags);
	return json_last_error() === JSON_ERROR_NONE;
}
} // function json_validate


if (! function_exists('mb_str_pad')) {
/**
 * Pad a multibyte string to a certain length with another multibyte string
 *
 * @param string $string The input string.
 *
 * @param int $length If the value of `length` is negative,
 * 		less than, or equal to the length of the input string,
 * 		no padding takes place, and `string` will be returned.
 *
 * @param string $pad_string may be truncated
 * 		if the required number of padding characters can't be evenly divided
 * 		by the pad_string's length.
 *
 * @param int $pad_type can be `STR_PAD_RIGHT`, `STR_PAD_LEFT`, or `STR_PAD_BOTH`.
 *
 * @param string $encoding the character encoding. If it is omitted or `null`,
 * 		the internal character encoding value will be used.
 *
 * @return string
 */
function mb_str_pad($string, $length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = null) {
	if ($pad_string === '') throw new ValueError('Argument #3 ($pad_string) must not be empty');

	$length_diff = $length - mb_strlen($string, $encoding);
	if ($length_diff <= 0) return $string;

	switch ($pad_type) {
		case STR_PAD_LEFT: {
			$left = $length_diff;
			$right = 0;
			break;
		}
		case STR_PAD_RIGHT: {
			$left = 0;
			$right = $length_diff;
			break;
		}
		case STR_PAD_BOTH: {
			$left = intdiv($length_diff, 2);
			$right = $length_diff - $left;
			break;
		}
		default: throw new ValueError('Argument #4 ($pad_type) must be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH');
	}
	for ($str_left = ''; mb_strlen($str_left, $encoding) < $left; $str_left .= $pad_string);
	if ($left) $str_left = mb_substr($str_left, 0, $left, $encoding);

	for ($str_right = ''; mb_strlen($str_right, $encoding) < $right; $str_right .= $pad_string);
	if ($right) $str_right = mb_substr($str_right, 0, $right, $encoding);

	return $str_left . $string . $str_right;
}
} // function mb_str_pad


} // if (PHP_VERSION_ID < 80300)
