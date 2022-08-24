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
	Params :
	0 : var
	... : keys
	before last : default value
	last :
		(bool) check if empty
		(array) authorized values
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
			$compare === false or
			(is_array($compare) and in_array($_var, $compare)) or
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
 * gde $var, $default, true|false
 * gde $var, $key1, $key2, $key3, ..., $default, true|false|[...]
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

// Same with reference
function gr(&$var) { return call_user_func_array('g', func_get_args()); };
function grd(&$var) { return call_user_func_array('gd', func_get_args()); };
function grde(&$var) { return call_user_func_array('gde', func_get_args()); };
function grda(&$var) { return call_user_func_array('gda', func_get_args()); };

/*
 * Error management
 *
 */
class ErrorManagement
{
	const ERROR_MAIL_MAX = 10;
	static protected $mailedCount = 0;
	static protected $mails = [];
	static protected $throwException = false;
	static protected $throwExceptionForNext = false;

	static public function init()
	{

		error_reporting(E_ALL | E_STRICT);
		set_error_handler([get_class(), 'handler']);
		register_shutdown_function([get_class(), 'handler']);
		ini_set('display_errors', 'Off');

	}

	static public function setMails(array $mails)
	{
		self::$mails = $mails;
	}

	static public function throwException($active = true)
	{
		self::$throwException = (bool)$active;
	}

	static public function isThrowException()
	{
		return self::$throwException;
	}

	// Throw exception on error and immediately disabled throwException option 
	static public function throwExceptionForNext()
	{
		self::throwException();
		self::$throwExceptionForNext = true;
	}

	static public function handler($type = null, $message = null, $file = null, $line = null)
	{

		if ($type === null) {
			$error = error_get_last();
			$type = g($error, 'type');
			$message = preg_capture('#([\s\S]+)\nStack trace:#', gd($error, 'message', 'Unknown error'));
			$file = g($error, 'file');
			$line = g($error, 'line');
		}

		if (self::isThrowException()) {
			if (self::$throwExceptionForNext) {
				self::$throwExceptionForNext = false;
				self::throwException(false);
			}
			throw new \ErrorHandledException($message, 0, $type, $file, $line);
		}

		if ($message) {

			$exit = !in_array($type, array(E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED));

			if ($exit or \Debug::isActive()) {

				if ($type === E_ERROR) // 1
					$typeStringify = 'E_ERROR';
				elseif ($type === E_WARNING) // 2
					$typeStringify = 'E_WARNING';
				elseif ($type === E_PARSE) // 4
					$typeStringify = 'E_PARSE';
				elseif ($type === E_NOTICE) // 8
					$typeStringify = 'E_NOTICE';
				elseif ($type === E_CORE_ERROR) // 16
					$typeStringify = 'E_CORE_ERROR';
				elseif ($type === E_CORE_WARNING) // 32
					$typeStringify = 'E_CORE_WARNING';
				elseif ($type === E_COMPILE_ERROR) // 64
					$typeStringify = 'E_COMPILE_ERROR';
				elseif ($type === E_COMPILE_WARNING) // 128
					$typeStringify = 'E_COMPILE_WARNING';
				elseif ($type === E_USER_ERROR) // 256
					$typeStringify = 'E_USER_ERROR';
				elseif ($type === E_USER_WARNING) // 512
					$typeStringify = 'E_USER_WARNING';
				elseif ($type === E_USER_NOTICE) // 1024
					$typeStringify = 'E_USER_NOTICE';
				elseif ($type === E_STRICT) // 2048
					$typeStringify = 'E_STRICT';
				elseif ($type === E_RECOVERABLE_ERROR) // 4096
					$typeStringify = 'E_RECOVERABLE_ERROR';
				elseif ($type === E_DEPRECATED) // 8192
					$typeStringify = 'E_DEPRECATED';
				elseif ($type === E_USER_DEPRECATED) // 16384
					$typeStringify = 'E_USER_DEPRECATED';
				else
					$typeStringify = 'unknown';

				$detailledMessage =
					'An error ' . $typeStringify . ' occurred' . chr(10) .
					'Message : '. $message . chr(10) .
					'File : ' . $file . chr(10) .
					'Line : ' . $line
				;

				$stack = debug_backtrace();
				array_shift($stack);
				foreach ($stack as $i => $line) {
					$args = array();
					foreach (gd($line, 'args', array()) as $arg) {
						if (is_array($arg))
							$args[] = 'Array(' . count($arg) . ')';
						elseif (is_null($arg))
							$args[] = 'null';
						elseif (is_string($arg))
							$args[] = '"' . substr($arg, 0, 25) . (strlen($arg) > 25 ? '...' : '') . '"';
						elseif (is_bool($arg))
							$args[] = $arg ? 'true' : 'false';
						elseif (is_object($arg))
							$args[] = 'Object(' . get_class($arg) . ')';
						elseif (is_numeric($arg))
							$args[] = $arg;
						else
							$args[] = '<' . $arg . '>';
					}
					$function = $line['function'];
					if (array_key_exists('class', $line)) {
						$function = $line['class'];
						$function.= $line['type'];
						$function.= $line['function'];
					}
					if ($i === 0)
						$detailledMessage.= chr(10) . 'Stack : ';
					$detailledMessage.= chr(10) . chr(9) . '[' . $i . '] Function : ' . $function . '(' . implode(',', $args) . ')';
					$detailledMessage.= chr(10) . chr(9) . chr(9) . 'File : ' . (array_key_exists('file', $line) ? $line['file'] : 'unknown');
					$detailledMessage.= chr(10) . chr(9) . chr(9) . 'Line : ' . (array_key_exists('line', $line) ? $line['line'] : 'unknown');
				}

				if (\Debug::isActive() and self::$mailedCount <= self::ERROR_MAIL_MAX) {

					$last = '';
					if (self::$mailedCount == self::ERROR_MAIL_MAX)
						$last = ' (max error reached for mail)';

					mail(implode(';', self::$mails), 'PHP Error on ' . gd($_SERVER, 'SCRIPT_URI', php_uname('n')) . $last,
						$detailledMessage . chr(10) .
						'$_SERVER : ' . print_r(array_diff_key($_SERVER, array('REMOTE_USER' => null, 'PHP_AUTH_USER' => null, 'PHP_AUTH_PW' => null)), true) . chr(10) .
						'$_SESSION : ' . (isset($_SESSION) ? print_r($_SESSION, true) : 'null')
					);
					self::$mailedCount++;
				}

			}

			if ($exit and !\Debug::isActive()) {
				http_response_code(500);
				exit('An error ' . $typeStringify . ' occurred');
			}

			if (\Debug::isActive())
				\Debug::trace([
					'style' => 'error',
					'label' => 'ERROR',
					'exit' => $exit,
					'messages' => [$detailledMessage]
				]);
		}
	}

}

class ErrorHandledException extends \ErrorException{}

/*
 * DEBUG
 *
 */

class Debug {

	static public $traceHtmlStyles = [
		'container' => [
			'default' => 'border:1px solid #335AE8;text-align:left;margin:1px 0px;overflow:auto;color:#000;font:12px monospace;',
			'error' => 'border-color:#ff0000;'
			
		],
		'line' => [
			'odd' => [
				'default' => 'margin:0px;background-color:#C5DAFF;color:inherit;font-size:inherit;padding:inherit;line-height:inherit;border:none;',
				'error' => 'background-color:#ff8e8e;'
			],
			'even' => [
				'default' => 'margin:0px;background-color:#D9E6FF;color:inherit;font-size:inherit;padding:inherit;line-height:inherit;border:none;',
				'error' => 'background-color:#ff8e8e;'
			]
		]
	];
	static public $traceCliStyles = [
		'char' => '░',
		'len' => 64
	];
	static public $colors = [];
	static public $styles = [];
	
	static protected $active = false;
	static protected $html = false;
	static protected $chronos = [];
	
	static public function init()
	{

		\ErrorManagement::init();

		self::html(php_sapi_name() != 'cli' and PHP_SAPI != 'cli');

	}

	static public function getTraceHtmlStyles()
	{
		return self::$traceHtmlStyles;
	}

	static public function setTraceHtmlStyles($traceHtmlStyles)
	{
		self::$traceHtmlStyles = $traceHtmlStyles;
	}

	static public function active($active = true)
	{
		self::$active = (bool)$active;
	}

	static public function isActive()
	{
		return self::$active;
	}

	static public function html($html = true)
	{
		self::$html = (bool)$html;
	}

	static public function isHtml()
	{
		return self::$html;
	}

	static public function trace(array $options)
	{

		$label = (array_key_exists('label', $options) ? $options['label'] : 'TRACE');
		$exit = (array_key_exists('exit', $options) ? $options['exit'] : false);
		$style = (array_key_exists('style', $options) ? $options['style'] : 'default');
		$messages = $options['messages'];

		if (self::isActive()) {
			
			if (self::isHtml()) {
				echo '<div style="' . self::$traceHtmlStyles['container'][$style] . '">';
				foreach ($messages as $index => $message) {
					echo '<pre style="' . self::$traceHtmlStyles['line'][$index%2 == 0 ? 'odd' : 'even'][$style] . '">';
					$message = print_r(self::traceMessageToHtml($message), true);
					echo preg_replace('/(\\[.*?\\])( => .*?\n\\()/', '<strong>$1</strong>$2', $message);
					echo '</pre>';
				}
				echo '</div>';
			} else {
				foreach ($messages as $index => $message) {
					$label = self::$traceCliStyles['char'] . $label . (count($messages) > 1 ? self::$traceCliStyles['char'] . ($index + 1) : '');
					echo $label . str_repeat(self::$traceCliStyles['char'], self::$traceCliStyles['len'] - mb_strlen($label)) . chr(10);
					print_r($message);
					echo chr(10);
				}
				echo str_repeat(self::$traceCliStyles['char'], self::$traceCliStyles['len']) . chr(10);
			}

			if ($exit)
				exit();
		}
	}

	static protected function traceMessageToHtml($message)
	{
		$result = '';
		if (is_array($message)) {
			foreach ($message as &$value)
				$value = self::traceMessageToHtml($value);
			unset($value);
			$result = $message;
		} elseif (is_null($message))
			$result = '<span style="font-style:italic;">null</span>';
		elseif ($message === '')
			$result = '<span style="font-style:italic;">empty string</span>';
		elseif (is_string($message))
			$result = toHtml($message);
		elseif (is_bool($message))
			$result = '<span style="font-style:italic;">' . ($message ? 'true' : 'false') . '</span>';
		else
			$result = print_r($message, true);
	
		return $result;
	}

	static public function chronoStart($id = '')
	{
		self::$chronos[$id] = explode(' ', microtime());
	}
	
	static public function chronoGet($id = '')
	{
		$start = gd(self::$chronos, $id, [0,0]);
		$time = explode(' ', microtime());
		return $time[1] + $time[0] - $start[1] - $start[0];
	}
}
\Debug::init();

function trace(...$messages)
{
	call_user_func(['\\Debug', 'trace'], [
		'messages' => $messages
	]);
}

function quit(...$messages)
{
	call_user_func(['\\Debug', 'trace'], [
		'exit' => true,
		'label' => 'QUIT',
		'messages' => $messages
	]);
}

function tracec()
{
	$style = \Debug::getTraceHtmlStyles();
	$currentStyle = $style['container']['default'];
	$args = func_get_args();
	$style['container']['default'].=  'color:' . array_shift($args) . ';';
	\Debug::setTraceHtmlStyles($style);
	call_user_func_array('trace', $args);
	$style['container']['default'] = $currentStyle;
	\Debug::setTraceHtmlStyles($style);
}

/*
 * MISC
 *
 */

function toHtml($string)
{
	if (is_array($string))
		return array_map('toHtml', $string);
	
	$string = htmlentities((string)$string, ENT_QUOTES, 'UTF-8');
	$string = preg_replace('/\\xC2\\x80/i', '&#128;', $string); //€
	return $string;
}

function in_dir($dir, $file, $exists = false)
{

	$dir = realpath($dir);
	$element  = realpath(dirname($file)) . '/' . basename($file);
	$result = strpos($element, $dir) === 0;
	if ($result and $exists)
		$result = file_exists($file);
	return $result;

}

function array_index($array, $keys, $keySeparator = ',')
{

	if (!is_array($keys))
		$keys = [$keys];

	$indexedArray = [];
	foreach ($array as $line) {
		$dataKey = [];
		foreach ($keys as $key)
			$dataKey[] = $line[$key];
		$indexedArray[implode($keySeparator, $dataKey)] = $line;
	}
	
	return $indexedArray;

}

function preg_capture($pattern, $subject)
{

	preg_match($pattern, $subject, $match);
	return gd($match, 1, g($match, 0));

}

function exit_json($data, $addcontenttype = true)
{

	if ($addcontenttype)
		header('Content-type: application/json'); 
	header('x-content-type-options: nosniff');	
	exit(json_encode($data));
}

function http_parse_query($query)
{
	parse_str($query, $output);
	return $output;
}