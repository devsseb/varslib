<?php

	include 'varslib.inc.php';
	Debug::active();

	$a = array(
		'name' => 'Ess',
		'phone' => '0213465798',
		'mail' => '',
		'type' => 'test'
	);
	echo '<h1>Get</h1>';
	echo '<pre>';
	print_r($a);
	echo '--- exists() ---' . chr(10);
	echo '<strong>Address : </strong>' . (exists($a, 'adress', '1') ? 'yes' : 'no') . chr(10);
	echo '--- g() ---' . chr(10);
	echo '<strong>Name : </strong>' . g($a, 'name') . chr(10);
	echo '<strong>Sex : </strong>' . g($a, 'sex') . chr(10);
	echo '--- gd() ---' . chr(10);
	echo '<strong>Name : </strong>' . gd($a, 'name', 'No name') . chr(10);
	echo '<strong>Sex : </strong>' . gd($a, 'sex', 'M') . chr(10);
	echo '--- gde() ---' . chr(10);
	echo '<strong>Mail : </strong>' . gde($a, 'mail', 'none') . chr(10);
	echo '--- gda() ---' . chr(10);
	echo '<strong>Type dev or test : </strong>' . gda($a, 'type', 'none', ['dev', 'test']) . chr(10);
	echo '<strong>Type dev or empty : </strong>' . gda($a, 'type', 'none', ['dev', '']) . chr(10);

	echo '</pre>';

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
