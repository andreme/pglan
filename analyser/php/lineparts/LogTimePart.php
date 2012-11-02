<?php

class LogTimePart extends LogLinePart {

	private $timestamp;

	public function __construct($datetime) {
		parent::__construct();

		$this->timestamp = strtotime($datetime);
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

}
