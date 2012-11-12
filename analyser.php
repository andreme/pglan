<?php

require_once __DIR__.'/analyser/php/Analyser.php';

$files = $argv;
array_shift($files);

$analyser = new Analyser($files);
$analyser->analyse();
