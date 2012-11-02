<?php

class QueryPart extends LogLinePart {

	private $type;
	private $text;

	public function __construct($type, $text) {
		parent::__construct();

		$this->type = $type;
		$this->text = $text;
	}

	public function getText() {
		return $this->text;
	}

}
