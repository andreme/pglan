<?php

require_once __DIR__.'/analyser/php/Analyser.php';

$analyser = new Analyser($argv[1]);
$analyser->analyse();
