<?php
/**
 * Polyfill to use some features in PHP 8.4.
 *
 * @see https://www.php.net/manual/en/migration84.new-features.php
 *
 * This file has implemented:
 * *
 *
 */
if (PHP_VERSION_ID < 80400) {

if (! function_exists('array_find')) {
/**
 * Returns the first element satisfying a callback function
 *
 * @param array $array
 * @param callable $callback
 *
 * @return mixed
 */
function array_find($array, $callback) {
	foreach ($array as $key => $value) {
		if ($callback($value, $key)) return $value;
	}
	return null;
}
} // function array_find


if (! function_exists('array_find_key')) {
/**
 * Returns the key of the first element satisfying a callback function
 *
 * @param array $array
 * @param callable $callback
 *
 * @return mixed
 */
function array_find_key($array, $callback) {
	foreach ($array as $key => $value) {
		if ($callback($value, $key)) return $key;
	}
	return null;
}
} // function array_find_key


if (! function_exists('array_any')) {
/**
 * Checks if at least one array element satisfies a callback function
 *
 * @param array $array
 * @param callable $callback
 *
 * @return bool
 */
function array_any($array, $callback) {
	foreach ($array as $key => $value) {
		if ($callback($value, $key)) return true;
	}
	return false;
}
} // function array_any


if (! function_exists('array_all')) {
/**
 * Checks if all array elements satisfy a callback function
 *
 * @param array $array
 * @param callable $callback
 *
 * @return bool
 */
function array_all($array, $callback) {
	foreach ($array as $key => $value) {
		if (! $callback($value, $key)) return false;
	}
	return true;
}
} // function array_all



if (! function_exists('bcceil')) {
function bcceil($num) {
	$int = bcadd($num, '0', 0);
	switch (bccomp($num, $int)) {
		case 0:
		case -1:
			return $int;
		default: return bcadd($num, '1', 0);
	}
}
}


if (! function_exists('bcfloor')) {
	function bcfloor($num) {
		$int = bcadd($num, '0', 0);
		switch (bccomp($num, $int)) {
			case 0:
			case 1:
				return $int;
			default: return bcsub($num, '1', 0);
		}
	}
}

if (! function_exists('bcdivmod')) {
function bcdivmod($num1, $num2, $scale = null) {
	if ($scale === null) $scale = (PHP_VERSION_ID < 70300)
		? intval(ini_get('bcmath.scale') ?: 0)
		: bcscale()
	;
	if (! bccomp($num2, '0', $scale)) throw new DivisionByZeroError;
	return array(
		bcdiv($num1, $num2, $scale),
		bcmod($num1, $num2, $scale)
	);
}
}

if (! class_exists('RoundingMode')) {
class RoundingMode {
	public const HalfAwayFromZero = 0;
	public const HalfTowardsZero = 1;
	public const HalfEven = 2;
	public const HalfOdd = 3;
	public const TowardsZero = 4;
	public const AwayFromZero = 5;
	public const NegativeInfinity = 6;
	public const PositiveInfinity = 7;
}
}


if (! function_exists('bcround')) {
function bcround($num, $precision = 0, $mode = RoundingMode::HalfAwayFromZero) {
	if (! preg_match('/^[+-]?[0-9]*(\.[0-9]*)?$/', $num, $matches))
		throw new ValueError;



}
}


if (! function_exists('fpow')) {
function fpow($num, $exponent) {
	if ($num === 0 && $exponent < 0) return INF;
	if ($num < 0 && fmod($exponent, 1)) return NAN;
	return $num ** $exponent;
}
}


} // if (PHP_VERSION_ID < 80400)
