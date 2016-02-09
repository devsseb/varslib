<?php

/*
 * https://github.com/devsseb/varslib
 *
 */

/*
 * GET
 *
 */

define('GET_FLAG_KEYS', "\x0b*1"); //'*' . chr(11) . '1'
define('GET_FLAG_REF', "\x0b*2"); //'*' . chr(11) . '2'

function exists(&$var)
{
	if (1 === $count = func_num_args())
		return isset($var);

	$args = func_get_args();

	$_var = &$var;
	for ($i = 1; $i < $count; $i++)
		if ($exists = (is_object($_var) and (property_exists($_var, $args[$i]) or method_exists($_var, $args[$i]))))
			$_var = &$_var->$args[$i];
		elseif ($exists = (is_array($_var) and array_key_exists($args[$i], $_var)))
			$_var = &$_var[$args[$i]];
		else
			break;

	return $exists;
}

		
function getIsFlag($var, $flag)
{
	return is_array($var) and reset($var) === $flag;
}

function k($args)
{
	return array_merge(array(GET_FLAG_KEYS), is_array($args) ? $args : func_get_args());
}

/*
 * Get from array reference and return reference
 * getar $array, k($keys)
 * getar $array, k($keys), $default
 * getar $arraykey, null
 * getar $arraykey, $default
 *
 */
function &getar(&$array, $keysOrDefault = null, $defaultOrEmpty = null, $empty = false)
{

	if (getIsFlag($keysOrDefault, GET_FLAG_KEYS)) {
		$keys = $keysOrDefault;
		$keys[0] = &$array;
		$default = $defaultOrEmpty;
	} else {
		$keys = $array;
		$keys[0] = &$array[0];
		$default = $keysOrDefault;
		$empty = $defaultOrEmpty;
	}

	if (call_user_func_array('exists', $keys)) {
		$_var = &$keys[0];
		$count = count($keys);
		for ($i = 1; $i < $count; $i++)
			if (is_object($_var)) $_var = &$_var->$keys[$i];
			else $_var = &$_var[$keys[$i]];

		if (!$empty or !empty($_var))
			return $_var;
	}
	return $default;
}

/*
 * get &$var
 * get &$var, $default
 * get &$var, k($keys)
 * get &$var, k($keys), $default
 *
 */
function &get(&$var, $keysOrDefault = null, $default = null)
{
	if (getIsFlag($keysOrDefault, GET_FLAG_KEYS))
		$refVar = &$var;
	else
		$refVar = array(&$var);
	return getar($refVar, $keysOrDefault, $default);
}

/*
 * Get from array
 * geta $array, k($keys)
 * geta $array, k($keys), $default
 * geta $arraykey, null
 * geta $arraykey, $default
 *
 */
function &geta($array, $keysOrDefault = null, $default = null)
{
	return getar($array, $keysOrDefault, $default);
}

/*
 * Get if non empty
 * 
 */
function &gete(&$var, $keysOrDefault = null, $default = null)
{
	if (getIsFlag($keysOrDefault, GET_FLAG_KEYS))
		$refVar = &$var;
	else {
		$refVar = array(&$var);
		$default = true;
	}
	return getar($refVar, $keysOrDefault, $default, true);
}

/*
 * Get if non empty from array
 * 
 */
function &getea($array, $keysOrDefault = null, $default = null)
{
	if (!getIsFlag($keysOrDefault, GET_FLAG_KEYS))
		$default = true;
	return getar($array, $keysOrDefault, $default, true);
}

/*
 * Transmit a var by ref in function
 *
 * Usage : myFunction( ref($var) )
 *
 */
function ref(&$ref) {
	return array(GET_FLAG_REF, &$ref);
}

/*
 * Return argument number $num passed with ref() by reference
 *
 * Usage : myFunction( ref($var) )
 *
 */
function &getArg($num)
{
	$arg = &geta(debug_backtrace(0), k(1, 'args', $num));
	if (getIsFlag($arg, GET_FLAG_REF))
		$arg = &$arg[1];

	return $arg;
}

function getn($class)
{
	$args = func_get_args();
	array_shift($args);
	return getna($class, $args);
}

function getna($class, $args)
{
    $class = new ReflectionClass($class);
    return $class->newInstanceArgs($args);
}

/*
 * DEBUG
 *
 */

class Debug {

	public static $colors, $styles;
	private static $active = false;
	
	public function __construct()
	{

		error_reporting(0);
		set_error_handler(array($this, 'errorHandler'));
		register_shutdown_function(array($this, 'errorHandler'));

		$this->setDefault();

	}

	private function setDefault()
	{
		self::$colors = (object)array('backgroundOdd' => '#C5DAFF', 'backgroundEven' => '#D9E6FF', 'main' => '#335AE8', 'interline' => '#999', 'font' => 'inherit');
		self::$styles = '';
	}

	static public function active($active = true)
	{
		self::$active = (bool)$active;
	}

	static public function isActive()
	{
		return self::$active;
	}

	public function errorHandler($type = null, $message = null, $file = null, $line = null)
	{

		if ($type === null) {
			$error = error_get_last();
			$type == $error['type'];
			$message = $error['message'];
			$file = $error['file'];
			$line = $error['line'];
		}
		
		if ($message) {
			if (self::isHtml()) {
				echo '<p>An error occurred : <strong>' . $this->html($message) . '</strong></p>';
			
				if (self::isActive()) {
					echo '<p><em>' . $this->html($file) . ' (' . $this->html($line) . ')</em></p>';
					echo '<p>Stack : </p><ul>';
					foreach ($this->stack(true) as $stack)
						echo '<li>' . $this->html($stack) . '</li>';
					echo '</ul>';
				}
			} else {
				echo 'An error occurred : ' . $message . chr(10);
				echo '-> ' . $file . ' (' . $line . ')' . chr(10);
				echo chr(10);
				echo 'Stack : ' . chr(10);
				foreach ($this->stack(true) as $stack)
					echo chr(9) . $stack . chr(10);
			
			}
		
			exit();
		}
	}

	static function isHtml()
	{
		return php_sapi_name() != 'cli' and PHP_SAPI != 'cli';
	}

	private function html($message)
	{
		$result = '';
		if (is_array($message)) {
			foreach ($message as &$value)
				$value = $this->html($value);
			unset($value);
			$result = $message;
		} elseif (is_null($message))
			$result = '<span style="font-style:italic;">null</span>';
		elseif ($message === '')
			$result = '<span style="font-style:italic;">empty string</span>';
		elseif (is_string($message))
			$result = print_r(htmlentities($message, ENT_QUOTES, 'UTF-8'), true);
		elseif (is_bool($message))
			$result = '<span style="font-style:italic;">' . ($message ? 'true' : 'false') . '</span>';
		else
			$result = print_r($message, true);
	
		return $result;
	}
	
	public function trace()
	{
		if (self::isActive()) {
			$args = func_get_args();
			
			if (self::isHtml())
				echo '<div style="border:1px solid ' . self::$colors->main . ';background:' . self::$colors->backgroundOdd . ';text-align:left;margin:1px 0px;overflow:auto;color:' . self::$colors->font . ';font:12px monospace;' . self::$styles . '">';
			foreach ($args as $index => $message) {
				if (self::isHtml()) {
					echo '<pre style="margin:0px;' . ($index > 0 ? 'border-top:1px dotted ' . self::$colors->interline . ';' : '') . ($index%2 == 0 ? '' : 'background-color:' . self::$colors->backgroundEven . ';') . '">';
					$message = print_r($this->html($message), true);
					echo preg_replace('/(\\[.*?\\])( => .*?\n\\()/', '<strong>$1</strong>$2', $message);
					echo '</pre>';
				} else {
					print_r($message);
					echo chr(10);
				}
				
			}
			if (self::isHtml())
				echo '</div>';
		}
		
		$this->setDefault();
	}

	public function stack($return = false)
	{
		$result = array();
		$stack = debug_backtrace();
		array_shift($stack);
		foreach ($stack as $line) {
			$args = array();
			foreach (get($line, k('args'), array()) as $arg) {
				switch (true) {
					case is_array($arg) : 
						$args[] = 'Array(' . count($arg) . ')';
					break;
					case is_null($arg) :
						$args[] = 'null';
					break;
					case is_string($arg) :
						$args[] = '"' . $arg . '"';
					break;
					case is_bool($arg) :
						$args[] = $arg ? 'true' : 'false';
					break;
					case is_object($arg) :
						$name = '';
						if (method_exists($arg, '__toString')) {
							$name = '*' . $arg;
						}					
						$args[] = 'Object(' . get_class($arg) . $name . ')';
					break;
					case is_numeric($arg) :
						$args[] = $arg;
					break;
					default :
						$args[] = '<' . $arg . '>';
				}
			}
			$class = '';
			if (exists($line, 'object')) {
				$class = $line['class'];
				if (method_exists($line['object'], '__toString')) {
					$class.= '*' . $line['object'];
				}
				$class.= $line['type'];
			}
			$result[] = $class . $line['function'] . '(' . implode(',', $args) . ')' . (exists($line, 'file') ? ' in file ' . $line['file'] . '(' . $line['line'] . ')' : '');
		}
		if ($return)
			return $result;
		$this->trace($result);
	}

	static public function chronoStart($id = '')
	{
		$GLOBALS['varslib_debug_chrono_' . $id] = explode(' ', microtime());
		return 0;
	}
	
	static public function chronoGet($id = '')
	{
		$start = get($GLOBALS['varslib_debug_chrono_' . $id], array(0,0));
		$time = explode(' ', microtime());
		return $time[1] + $time[0] - $start[1] - $start[0];
	}
}

$GLOBALS['varslib_debug'] = new Debug();

function trace()
{
	call_user_func_array(array($GLOBALS['varslib_debug'], 'trace'), func_get_args());
}

function quit()
{
	if ($args = func_get_args())
		call_user_func_array('trace', $args);
	exit();
}

function tracec()
{
	$args = func_get_args();
	Debug::$colors->font = array_shift($args);
	call_user_func_array('trace', $args);
}

function traceStack()
{
	call_user_func_array(array($GLOBALS['varslib_debug'], 'stack'), func_get_args());
}

/*
 * HTML
 *
 */

function toHtml($string)
{
	$string = htmlentities((string)$string, ENT_QUOTES, 'UTF-8');
	$string = preg_replace('/\\x92/', '&#146;', $string);
	$string = preg_replace('/\\xC2\\x80/i', '&#128;', $string); //€
	$string = preg_replace('/\\xA0/', '&#160;', $string);
	return $string;
}

/*
 * OBJECT
 *
 */

class Object implements countable
{
	public function __construct($var = null)
	{
		if (is_array($var))
			foreach ($var as $key => $value)
				$this->$key = $value;
		else {

			$args = func_get_args();
			$total = func_num_args();
			for ($i = 0; $i < $total; $i = $i + 2)
				$this->{$args[$i]} = $args[$i + 1];
		
		}
	}

	public function count()
	{
		return count(get_object_vars($this));
	}
}


function object($var = null)
{
	return call_user_func_array(array(new ReflectionClass('Object'), 'newInstance'), func_get_args());
}
?>
