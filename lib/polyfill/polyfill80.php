<?php
/**
 * Polyfill to use functions new in PHP 8.0.
 *
 * @see https://www.php.net/manual/en/migration80.new-features.php
 *
 * This file has implemented:
 * * constant FILTER_VALIDATE_BOOL
 * * interface Stringable
 * * functions str_contains, str_starts_with, str_ends_with
 * * function fdiv
 * * function get_debug_type
 * * function get_resource_id
 * * function preg_last_error_msg
 * * class PhpToken
 *
 */
if (PHP_VERSION_ID < 80000) {

if (! defined('FILTER_VALIDATE_BOOL')) {
/**
 * Validation Filters.
 * @var int
 */
define('FILTER_VALIDATE_BOOL', FILTER_VALIDATE_BOOLEAN);
}


if (! interface_exists('Stringable', false)) {
/**
 * Its primary value is to allow functions to type check against the union type `string|Stringable`
 * to accept either a string primitive or an object that can be cast to a string.
 */
interface Stringable {
	/**
	 * @return string
	 */
	function __toString();
}
} // interface Stringable


if (! function_exists('str_contains')) {
/**
 * Determine if a string contains a given substring.
 * @param string $haystack The string to search in.
 * @param string $needle The substring to search for in the `haystack`.
 * @return bool Returns `true` if `needle` is in `haystack`, `false` otherwise.
 */
function str_contains($haystack, $needle) {
	return $needle !== '' && strpos($haystack, $needle) !== false;
	/*
	 * Check whether `needle` is empty is not only for efficiency.
	 * `strpos()` does not support empty string to be `needle` until 8.0.0.
	 */
}
} // function str_contains


if (! function_exists('str_starts_with')) {
/**
 * Checks if a string starts with a given substring.
 * @param string $haystack The string to search in.
 * @param string $needle The substring to search for in the `haystack`.
 * @return bool Returns `true` if `haystack` begins with `needle`, `false` otherwise.
 */
function str_starts_with($haystack, $needle) {
	return strncmp($haystack, $needle, strlen($needle)) === 0;
}
} // function str_starts_with


if (! function_exists('str_ends_with')) {
/**
 * Checks if a string ends with a given substring.
 * @param string $haystack The string to search in.
 * @param string $needle The substring to search for in the `haystack`.
 * @return bool Returns `true` if `haystack` ends with `needle`, `false` otherwise.
 */
function str_ends_with($haystack, $needle) {
	if ($haystack === $needle) return true;
	$length = strlen($needle);
	if (strlen($haystack) <= $length) return false;
	return substr_compare($haystack, $needle, - $length) === 0;
	/*
	 * For `substr_compare($haystack, $needle, $offset, $length, ...)`:
	 * > If `offset` is equal to (prior to PHP 7.2.18, 7.3.5) or greater than the length of `haystack`,
	 * > or the `length` is set and is less than 0,
	 * > `substr_compare()` prints a warning and returns `false`.
	 */
}
} // function str_ends_with


if (! function_exists('fdiv')) {
/**
 * Divides two numbers, according to IEEE 754.
 * @param float $num1 The dividend (numerator)
 * @param float $num2 The divisor
 * @return float The floating point result of `num1`/`num2`
 */
function fdiv($num1, $num2) {
	if ($num2) return $num1 / $num2;
	if ($num1 > 0) return INF;
	if ($num1 < 0) return -INF;
	return NAN;
}
} // function fdiv


if (! function_exists('get_debug_type')) {
/**
 * Gets the type name of a variable in a way that is suitable for debugging.
 * @param mixed $value The variable being type checked.
 * @return string
 */
function get_debug_type($value) {
	if (is_array($value)) return 'array';
	if (is_bool($value)) return 'bool';
	if (is_float($value)) return 'float';
	if (is_int($value)) return 'int';
	if (is_null($value)) return 'null';
	if (is_string($value)) return 'string';
	if ($value instanceof __PHP_Incomplete_Class) return '__PHP_Incomplete_Class';
	if (is_object($value)) {
		$class = get_class($value);
		if (strpos($class, '@') === false) return $class;

		$parent = get_parent_class($class);
		if (! $parent) {
			$array = class_implements($class);
			$parent = empty($array) ? 'class' : $array[0];
		}
		return "$parent@anonymous";
	}
	$type = get_resource_type($value);
	if ($type === 'Unknown') $type = 'closed';
	return "resource ($type)";
}
} // function get_debug_type


if (! function_exists('get_resource_id')) {
/**
 * Returns an integer identifier for the given resource.
 * @param resource $resource The evaluated resource handle.
 * @return int The int identifier for the given resource.
 */
function get_resource_id($resource) {
	return (int) $resource;
}
} // function get_resource_id


if (! function_exists('preg_last_error_msg')) {
/**
 * Returns the error message of the last PCRE regex execution.
 * @return string Returns "No error" if no error has occurred.
 */
function preg_last_error_msg() {
	switch (preg_last_error()) {
		case PREG_NO_ERROR: return 'No error';
		case PREG_INTERNAL_ERROR: return 'Internal error';
		case PREG_BACKTRACK_LIMIT_ERROR: return 'Backtrack limit exhausted';
		case PREG_RECURSION_LIMIT_ERROR: return 'Recursion limit exhausted';
		case PREG_BAD_UTF8_ERROR: return 'Malformed UTF-8 characters, possibly incorrectly encoded';
		case PREG_BAD_UTF8_OFFSET_ERROR: return 'The offset did not correspond to the beginning of a valid UTF-8 code point';
		case PREG_JIT_STACKLIMIT_ERROR: return 'JIT stack limit exhausted';
		default:
			throw new UnexpectedValueException('Unknown PREG error');
	}
}
} // function preg_last_error_msg


if (! class_exists('PhpToken')) {
/**
 * This class provides an alternative to `token_get_all()`.
 */
class PhpToken implements Stringable {
	/** @var int */
	public $id;

	/** @var string */
	public $text;

	/** @var int */
	public $line;

	/** @var int */
	public $pos;

	/**
	 * Constructor
	 * @param int $id
	 * @param string $text
	 * @param int $line
	 * @param int $pos
	 */
	final public function __construct($id, $text, $line = -1, $pos = -1) {
		$this->id = (int) $id;
		$this->text = (string) $text;
		$this->line = (int) $line;
		$this->pos = (int) $pos;
	} // PhpToken::__construct

	/**
	 * Returns the name of the token.
	 * @return ?string An ASCII character for single-char tokens, or one of `T_*` constant names for known tokens, or `null` for unknown tokens.
	 */
	public function getTokenName() {
		if ($this->id < 256) return chr($this->id);
		$name = token_name($this->id);
		return ($name === 'UNKNOWN') ? null : $name;
	} // PhpToken::getTokenName

	/**
	 * Tells whether the token is of given kind.
	 * @param int|string|array $kind Either a single value to match the token's id or textual content, or an array thereof.
	 * @return bool
	 */
	public function is($kind) {
		switch (gettype($kind)) {
			case 'integer': return $kind === $this->id;
			case 'string': return $kind === $this->text;
			case 'array': {
				foreach ($kind as $value) {
					if ($value === $this->id || $value === $this->text) return true;
				}
				return false;
			}
			default: throw new TypeError;
		}
	} // PhpToken::is

	/**
	 * Tells whether the token would be ignored by the PHP parser.
	 * @return bool A boolean value whether the token would be ignored by the PHP parser (such as whitespace or comments).
	 */
	public function isIgnorable() {
		return in_array($this->id, array(
			T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG
		));

	} // PhpToken::isIgnorable

	/**
	 * Returns the textual content of the token.
	 * @return string
	 */
	public function __toString() {
		return $this->text;
	} // PhpToken::__toString

	/**
	 * Splits given source into PHP tokens, represented by PhpToken objects.
	 * @param string $code
	 * @param int $flags
	 * @return static[]
	 */
	public static function tokenize($code, $flags = 0) {
		$tokens = array();
		$line = 1;
		$position = 0;
		foreach (token_get_all($code, $flags) as $token) {
			if (is_array($token)) {
				$tokens[] = new static($token[0], $token[1], $token[2], $position);
				$line = $token[2];
				$position += strlen($token[1]);
			}
			else {
				$tokens[] = new static(ord($token), $token, $line, $position);
				$position += 1;
			}
		}
		return $tokens;
	} // PhpToken::tokenize
}
} // class PhpToken

} // if (PHP_VERSION_ID < 80000)
