<?php

require_once __DIR__.'/analyser/php/Analyser.php';

$args = array_slice($argv, 1);

$files = [];

foreach ($args as $filename)  {
	$files = array_merge($files, glob($filename));
}

$analyser = new Analyser($files);
$analyser->analyse();
