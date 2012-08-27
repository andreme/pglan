<?php

class FileReader extends Reader {

	private $filename;

	public function __construct($filename) {
		$this->filename = $filename;
	}

	public function init() {

		$filename = $this->filename;

		switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
			case 'bz2':
				$filename = "compress.bzip2://$filename";
				break;
			case 'gz':
				$filename = "compress.zlib://$filename";
				break;
		}

		$this->handle = fopen($filename, 'r');

		if ($this->handle === false) {
			throw new Exception('Can not open file: '.$this->filename);
		}

		$this->nextLine();
	}

}
