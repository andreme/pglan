<?php

require_once __DIR__.'/../pglantestcase.php';

spl_autoload_register(function ($class) {
	require_once __DIR__."/../../php/$class.php";
	return true;
});
