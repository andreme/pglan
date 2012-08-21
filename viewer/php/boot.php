<?php

error_reporting(E_ALL);

spl_autoload_register(function ($class) {
	require_once __DIR__."/$class.php";
	return true;
});


require_once __DIR__.'/../../php/DefaultConfig.php';


$main = new Main(DefaultConfig::create());
$main->run();