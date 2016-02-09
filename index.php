<?php

	include 'varslib.inc.php';
	Debug::active();

	$a = array(
		'name' => 'Ess',
		'phone' => '0213465798',
		'mail' => ''
	);

	echo '<h1>Get</h1>';

	echo '<pre>';
	print_r($a);
	echo '--- exists() ---' . chr(10);
	echo '<strong>Address : </strong>' . (exists($a, 'adress', '1') ? 'yes' : 'no') . chr(10);
	echo '--- get() ---' . chr(10);
	echo '<strong>Name : </strong>' . get($a, k('name'), 'No name') . chr(10);
	echo '<strong>Sex : </strong>' . get($a, k('sex'), 'M') . chr(10);
	echo '--- geta() ---' . chr(10);
	echo '<strong>Phone : </strong>' . geta(array($a, 'phone'), 'none') . chr(10);
	echo '--- gete() ---' . chr(10);
	echo '<strong>Mail : </strong>' . gete($a, k('mail'), 'none') . chr(10);
	echo '--- getea() ---' . chr(10);
	echo '<strong>Mail : </strong>' . getea(array($a, 'mail'), 'none') . chr(10);
	echo '--- ref() ---' . chr(10);

	$n = 5;
	add(ref($n), 8);
	
	echo '<strong>$n : </strong>' . $n . chr(10);
	echo '</pre>';
	
	function add($a, $b)
	{
		$a = &getArg(0);
		return $a+= $b;
	}

	echo '<h1>Object</h1>';

	$o = object(
		'name', 'Ess',
		'phone', '0213465798'
	);

	echo '<pre>';
	print_r($o);
	echo '--- count() ---' . chr(10);
	echo '<strong>Total : </strong>' . count($o) . chr(10);
	echo '--- foreach ---' . chr(10);
	foreach ($o as $k => $v)
		echo '<strong>' . $k . '</strong> : ' . $v . chr(10);
	echo '</pre>';

	$o = object([
		'name' => 'Ess',
		'phone' => '0213465798'
	]);

	echo '<pre>';
	print_r($o);
	echo '--- count() ---' . chr(10);
	echo '<strong>Total : </strong>' . count($o) . chr(10);
	echo '--- foreach ---' . chr(10);
	foreach ($o as $k => $v)
		echo '<strong>' . $k . '</strong> : ' . $v . chr(10);
	echo '</pre>';

	echo '<h1>Debug</h1>';

	Debug::chronoStart('test');

	echo '<strong>Debug is active ?</strong><br />';
	echo (Debug::isActive() ? 'yes' : 'no') . '<br />';

	echo '<strong>Trace</strong>';
	trace($a);
	
	echo '<strong>Trace whith color</strong>';
	tracec('blue', 'Trace in blue');
	
	echo '<strong>Stack</strong>';
	traceStack();
	echo '<strong>Chrono</strong><br />';
	echo Debug::chronoGet('test') . '<br />';
	echo '<strong>Quit</strong>';
	quit('Trace and exit');

?>
