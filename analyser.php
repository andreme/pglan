<?php

spl_autoload_register(function ($class) {
	require_once __DIR__."/analyser/php/$class.php";
	return true;
});

function beginsWith($str, $beginsWith) {
	return strncmp($str, $beginsWith, strlen($beginsWith)) === 0;
}

$reader = new FileReader($argv[1]);
$reader->init();
while (($reader->getLine() === '') and !$reader->isEof()) {
	$reader->nextLine();
}

$logger = new Logger(null);

$list = new LogAggregator();


if (PgSysLogParser::isSysLog($reader->getLine())) {
	$parser = new PgSysLogParser($reader, $logger, $list);
} else {
	$parser = new PgLogParser($reader, $logger, $list);
}

$start = new DateTime();

$parser->parse();

$writer = new JSONWriter($list, __DIR__.'/data/'.basename($argv[1]).'.json');

$writer->write();

$end = new DateTime();

echo "\n", 'Start: ', $start->format('H:i:s'), "\n";
echo 'End: ', $end->format('H:i:s'), "\n";
echo "\nDone\n";