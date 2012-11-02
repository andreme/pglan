<?php

class LogLevelPart extends LogLinePart {

	private $level;

	public function __construct($level) {
		parent::__construct();

		$this->level = $level;
	}

	public function getLevel() {
		return $this->level;
	}

}
