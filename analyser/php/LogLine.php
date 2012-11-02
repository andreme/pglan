<?php

class LogLine {

	private $line;

	private $remainder;

	private $parts = array();

	/**
	 *
	 * @var LogLinePart
	 */
	private $lastPart;

	/**
	 *
	 * @var LogEntry
	 */
	private $entry;

	/**
	 *
	 * @var MultiLineParser
	 */
	private $multiLineParser;

	private $ignoreEntry = false;

	public function __construct($line) {
		$this->line = $line;
		$this->remainder = $line;
	}

	public function addPart($part) {
		$this->parts[] = $this->lastPart = $part;
	}

	public function getLastPart() {
		return $this->lastPart;
	}

	public function getLine() {
		return $this->line;
	}

	/**
	 *
	 * @return LogLinePart
	 */
	public function getPart($partClass) {
		$partClass .= 'Part';

		foreach ($this->parts as $part) {
			if ($part instanceof $partClass) {
				return $part;
			}
		}

		return null;
	}

	public function getRemainder() {
		return $this->remainder;
	}

	public function setRemainder($remainder) {
		$this->remainder = $remainder;
	}

	public function getEntry() {
		return $this->entry;
	}

	public function setEntry($entry) {
		$this->entry = $entry;
	}

	public function setMultiLineParser(MultiLineParser $multiLineParser) {
		$this->multiLineParser = $multiLineParser;
	}

	public function isMultiLine() {
		return !!$this->multiLineParser;
	}

	public function parseNextLine($nextLogLine) {
		return $this->multiLineParser->parseNextLine($this, $nextLogLine);
	}

	public function getIgnoreEntry() {
		return $this->ignoreEntry;
	}

	public function setIgnoreEntry($ignoreEntry) {
		$this->ignoreEntry = $ignoreEntry;
	}

}
