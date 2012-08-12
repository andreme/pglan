<?php

class Logger {

	private $maxUnknowns;

	private $unknowns = 0;

	private $debugging = false;

	public function __construct($maxUnknowns = 50) {
		$this->maxUnknowns = $maxUnknowns;
	}

	public function info($s) {
		echo "Info: $s\n";
	}

	public function debug($s) {
		if (!$this->debugging) {
			return;
		}

//		echo "DEBUG: $s\n";
	}

	public function setDebugging($value) {
		$this->debugging = !!$value;
	}

	public function unknown($s, $lineNo) {
//		echo "Unknown: Line $lineNo, $s\n";
		if (($this->unknowns++ > $this->maxUnknowns) and ($this->maxUnknowns !== null)) {
			die('Too many unknowns');
		}
	}

}
