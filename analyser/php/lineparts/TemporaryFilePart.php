<?php

class TemporaryFilePart extends LogLinePart {

	private $file;
	private $size;

	public function __construct($file, $size) {
		parent::__construct();

		$this->file = $file;
		$this->size = $size;
	}

	public function getFile() {
		return $this->file;
	}

	public function getSize() {
		return $this->size;
	}

}
