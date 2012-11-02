<?php

class NoMatchPart extends LogLinePart {

	private $text;

	public function __construct($text) {
		parent::__construct();

		$this->text = $text;
	}

	public function getText() {
		return $this->text;
	}

}
