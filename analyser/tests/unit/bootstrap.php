<?php

require_once __DIR__.'/../pglantestcase.php';

require_once __DIR__.'/../../../php/helper.php';

spl_autoload_register(function ($class) {

	$file = __DIR__."/../../php";
	if (file_exists("$file/lineparsers/$class.php")) {
		$file .= "/lineparsers/$class.php";
	} elseif (file_exists("$file/lineparts/$class.php")) {
		$file .= "/lineparts/$class.php";
	} elseif (file_exists("$file/$class.php")) {
 		$file .= "/$class.php";
	} else {
		return;
 	}

	require_once $file;

	return true;
});
