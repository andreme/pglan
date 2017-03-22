<?php

use League\Csv\Reader;

class CSVFileReader extends \Reader {

	private $filename;

	/**
	 *
	 * @var Iterator
	 */
	private $csv;

	public function __construct($filename) {
		$this->filename = $filename;
	}

	public function init() {

		if (is_object($this->filename)) {
			$reader = Reader::createFromFileObject($this->filename);
		} else {
			$reader = Reader::createFromPath($this->filename, 'r');
		}

		$this->csv = $reader->fetch();
		$this->csv->rewind();

		$this->nextLine();
	}

	public function nextLine() {
		$this->line = $this->csv->current();
		$this->eof = !$this->csv->valid();

		if (!$this->eof) {
			$this->csv->next();
		}
	}

	public function getOutputName() {
		return basename($this->filename);
	}

}
