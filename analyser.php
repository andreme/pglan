<?php

function beginsWith($str, $beginsWith) {
	return strncmp($str, $beginsWith, strlen($beginsWith)) === 0;
}

require_once __DIR__.'/analyser/php/Analyser.php';

$analyser = new Analyser($argv[1]);
$analyser->analyse();
