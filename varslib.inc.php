<?php

/*
 * https://github.com/devsseb/varslib
 *
 */

function exists(&$var)
{
	if (1 === $count = func_num_args())
		return isset($var);

	$args = func_get_args();

	$_var = &$var;
	for ($i = 1; $i < $count; $i++)
		if ($exists = (is_object($_var) and (property_exists($_var, $args[$i]) or method_exists($_var, $args[$i]))))
			$_var = &$_var->{$args[$i]};
		elseif ($exists = (is_array($_var) and array_key_exists($args[$i], $_var)))
			$_var = &$_var[$args[$i]];
		else
			break;

	return $exists;
}

/*
 * gde $var, $default, true|false
 * gde $var, $key1, $key2, $key3, ..., $default, true|false|[...]
 *
 */
function getDefaultEmpty($var)
{
	$keys = func_get_args();
	$keys[0] = &$var;

	// Last index is $compare
	$compare = array_pop($keys);
	// Last index is $default
	$default = array_pop($keys);
	if (call_user_func_array('exists', $keys)) {
		$_var = &$keys[0];
		$count = count($keys);
		for ($i = 1; $i < $count; $i++)
			if
				(is_object($_var)) $_var = &$_var->{$keys[$i]};
			else
				$_var = &$_var[$keys[$i]];

		if (
			(is_array($compare) and in_array($_var, $compare)) or
			$compare === false or
			($compare === true and !empty($_var))
		) {
			return $_var;
		}
	}
	
	return $default;
}

/*
 * g &$var
 * g &$var, $key1, $key2, $key3, ...
 *
 */
function g($var)
{
	$args = func_get_args();
	$args[0] = &$var;
	$args[] = null; // Default value
	$args[] = false; // No check if is empty

	return call_user_func_array('getDefaultEmpty', $args);
}

/*
 * gd &$var, $default
 * gd &$var, $key1, $key2, $key3, ..., $default
 *
 */
function gd($var)
{
	$args = func_get_args();
	$args[0] = &$var;
	$args[] = false; // No check if is empty

	return call_user_func_array('getDefaultEmpty', $args);
}

/*
 * gd &$var, $default
 * gd &$var, $key1, $key2, $key3, ..., $default
 *
 */
function gde($var)
{
	$args = func_get_args();
	$args[0] = &$var;
	$args[] = true; // Check if is empty

	return call_user_func_array('getDefaultEmpty', $args);
}

/*
 * gda &$var, $default
 * gda &$var, $key1, $key2, $key3, ..., $default, [...,...]
 *
 */
function gda($var)
{
	$args = func_get_args();
	$args[0] = &$var;

	return call_user_func_array('getDefaultEmpty', $args);
}

/*
 * DEBUG
 *
 */

class Debug {

	public static $colors, $styles, $mails = array();
	private static $active = false;
	
	public function __construct($mails = array())
	{

		error_reporting(E_ALL | E_STRICT);
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
		ini_set('display_errors', (self::$active = (bool)$active) ? 'On' : 'Off');
	}

	static public function isActive()
	{
		return self::$active;
	}

	public function errorHandler($type = null, $message = null, $file = null, $line = null)
	{

		if ($type === null) {
			$error = error_get_last();
			$type == g($error, 'type');
			$message = g($error, 'message');
			$file = g($error, 'file');
			$line = g($error, 'line');
		}
		
		if ($message) {

			$exit = !in_array($type, array(E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED));

			if (self::isActive() or $exit) {

				ob_start();
				echo 'An error occurred (' . self::errorTypeToString($type) . ') : ' . $message . chr(10);
				echo '-> ' . $file . ' (' . $line . ')' . chr(10);
				echo chr(10);
				echo 'Stack : ' . chr(10);
				foreach ($this->stack(true) as $stack)
					echo chr(9) . $stack . chr(10);
				$raw = ob_get_clean();

				if (self::isHtml()) {
					if (self::isActive())
						echo '<pre style="border-width:3px;border-color:#ff0000;background-color:#ff8e8e;overflow:auto;">';
					echo '<p>An error occurred (' . self::errorTypeToString($type) . ') : <strong>' . $this->html($message) . '</strong></p>';
				
					if (self::isActive()) {
						echo '<p><em>' . $this->html($file) . ' (' . $this->html($line) . ')</em></p>';
						echo '<p>Stack : </p><ul>';
						foreach ($this->stack(true) as $stack)
							echo '<li>' . $this->html($stack) . '</li>';
						echo '</ul>';
					}
					if (self::isActive())
						echo '</pre>';
					$subjectOn = gd($_SERVER, 'SCRIPT_URI', 'Unknown');
				} else {
					$subjectOn = php_uname('n');
					echo $raw;
				}

				if (self::isActive())
					foreach (self::$mails as $mail)
						mail($mail, 'PHP Error on ' . $subjectOn, $raw);


			}

			if ($exit)
				exit();
		}
	}

	static function errorTypeToString($type) 
	{ 
		switch($type) 
		{ 
			case E_ERROR: // 1
				return 'E_ERROR';
			case E_WARNING: // 2
				return 'E_WARNING';
			case E_PARSE: // 4
				return 'E_PARSE';
			case E_NOTICE: // 8
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR: // 64
				return 'E_COMPILE_ERROR';
			case E_COMPILE_WARNING: // 128
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384
				return 'E_USER_DEPRECATED';
		} 
		return ''; 
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
			foreach (gd($line, 'args', array()) as $arg) {
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
		$start = gd($GLOBALS, 'varslib_debug_chrono_' . $id, array(0,0));
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
	if (Debug::isActive())
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
	$string = preg_replace('/\\xC2\\x80/i', '&#128;', $string); //€
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

/*
 * FILES
 *
 */

function in_dir($dir, $file, $exists = false)
{
	$dir = realpath($dir);
	$element  = realpath(dirname($file)) . '/' . basename($file);
	$result = strpos($element, $dir) === 0;
	if ($result and $exists)
		$result = file_exists($file);
	return $result;
}

function array_index($array, $key)
{

	$result = array();
	foreach ($array as $data)
		$result[$data[$key]] = $data;
	
	return $result;

}
?>
