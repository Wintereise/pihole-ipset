<?php

require_once("static.php");
$target = isset($_GET['name']) ? $_GET['name'] : null;
if (!isset($target)) {
	die('# No name given.');
}

$escapedArg = escapeshellarg($target);

$bareCommand = "sudo ipset list $escapedArg -o plain";
$command = "$bareCommand | grep -E '([0-9]{1,3}[\.]){3}[0-9]{1,3}'";

$bareOutput = [];
$bareReturnCode = 0;
exec($bareCommand, $bareOutput, $bareReturnCode);
if ($bareReturnCode > 0) {
	die("# Export failed, you probably gave the name of a set that doesn't exist. Cmd returned $bareReturnCode");
}

$output = [];
exec($command, $out);

$filteredCollection = [];
foreach ($out as $ipPreamble) {
	list($ip, $no, $timeout) = explode(" ", $ipPreamble);
	if (filter_var($ip, FILTER_VALIDATE_IP)) {
		$filteredCollection[] = $ip;
	}
}

if (isset($static[$target])) {
	$filteredCollection = array_merge($filteredCollection, $static[$target]);
}

if (count($filteredCollection) === 0) {
	$filteredCollection[] = "127.1.50.100";
}


echo implode(PHP_EOL, $filteredCollection);
