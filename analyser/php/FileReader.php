<?php

class Reader {

	private $eof = false;

	private $line;

	protected $handle;

	private $lineNo = 0;

	public function nextLine() {
		$this->line = fgets($this->handle);
		$this->eof = $this->line === false;
		if (!$this->eof) {
			$this->lineNo++;
		}
	}

	public function getLine() {
		return rtrim($this->line);
	}

	public function isEof() {
		return $this->eof;
	}

	public function __destruct() {
		fclose($this->handle);
	}

	public function getLineNo() {
		return $this->lineNo;
	}

}

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
