<?php

class Reader {

	protected $eof = false;

	protected $line;

	private $handle;

	private $lineNo = 0;

	public function nextLine() {
		$this->line = fgets($this->handle);
		$this->eof = ($this->line === false);
		if (!$this->eof) {
			$this->lineNo++;
			$this->line = rtrim($this->line);
		}
	}

	public function getLine() {
		return $this->line;
	}

	public function isEof() {
		return $this->eof;
	}

	public function __destruct() {
		$this->handle and fclose($this->handle);
	}

	public function getLineNo() {
		return $this->lineNo;
	}

	public function getHandle() {
		return $this->handle;
	}

	public function setHandle($handle) {
		$this->handle = $handle;
	}

	public function getOutputName() {
		throw new Exception('Not implemented');
	}

}
