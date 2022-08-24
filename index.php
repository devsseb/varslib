<?php

	include 'varslib.inc.php';
	Debug::active();
	Debug::chronoStart();

	$a = [
		'name' => 'Ess',
		'phone' => '0213465798',
		'mail' => '',
		'type' => 'test'
	];
	echo '<strong>Get</strong>';
	echo '<pre>';
	print_r($a);
	echo '--- exists() ---' . chr(10);
	echo '<strong>Address : </strong>' . (exists($a, 'adress', '1') ? 'yes' : 'no') . chr(10);
	echo '--- g() ---' . chr(10);
	echo '<strong>Name : </strong>' . g($a, 'name') . chr(10);
	echo '<strong>Sex : </strong>' . g($a, 'sex') . chr(10);
	echo '--- gd() ---' . chr(10);
	echo '<strong>Name : </strong>' . gd($a, 'name', 'No name') . chr(10);
	echo '<strong>Sex : </strong>' . gd($a, 'sex', 'Unknown') . chr(10);
	echo '--- gde() ---' . chr(10);
	echo '<strong>Mail : </strong>' . gde($a, 'mail', 'none') . chr(10);
	echo '--- gda() ---' . chr(10);
	echo '<strong>Type dev or test : </strong>' . gda($a, 'type', 'none', ['dev', 'test']) . chr(10);
	echo '<strong>Type dev or empty : </strong>' . gda($a, 'type', 'none', ['dev', '']) . chr(10);

	echo '</pre>';
	
	echo '<strong>Error management</strong><br />';
	echo $thisVariableIsUndefined;
	echo '<strong>Error management, handle error like an exception</strong><br />';
	\ErrorManagement::throwExceptionForNext();
	try {
		echo $thisVariableIsUndefined;
	} catch (\ErrorHandledException $e) {
		echo '<pre>';
		print_r([
			'Message' => $e->getMessage(),
			'Code' => $e->getCode(),
			'Severity' => $e->getSeverity(),
			'File' => $e->getFile(),
			'Line' => $e->getLine()
		]);
		echo '</pre>';
	}
	\ErrorManagement::throwException(false);

	echo '<strong>Chrono</strong><br />';
	echo Debug::chronoGet() . 's<br />';
	echo '<strong>Trace</strong>';
	trace('It is a simple message');
	echo '<strong>Trace multiple</strong>';
	trace('It is an array', $a, 'Third line');
	echo '<strong>Trace whith color</strong>';
	tracec('red', 'Trace in red');
	echo '<strong>Quit</strong>';
	quit('Trace and exit');

?>
