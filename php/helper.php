<?php

ini_set('pcre.backtrack_limit', 1024 * 1024 * 1024); // allow 1GB for preg_* subjects

function beginsWith($str, $beginsWith) {
	return strncmp($str, $beginsWith, strlen($beginsWith)) === 0;
}

function ematch($pattern, $subject, &$matches) {
	$result = preg_match($pattern, $subject, $matches);

	if ($result === false) {
		throw new Exception('Regexp caused error: '.preg_last_error());
	}

	return $result;
}