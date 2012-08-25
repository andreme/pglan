<?php

function beginsWith($str, $beginsWith) {
	return strncmp($str, $beginsWith, strlen($beginsWith)) === 0;
}
