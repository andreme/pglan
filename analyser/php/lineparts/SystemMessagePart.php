<?php

class SystemMessagePart extends LogLinePart {

	private $message;

	public function __construct($message) {
		parent::__construct();

		$this->message = $message;
	}

	public function getMessage() {
		return $this->message;
	}

}
