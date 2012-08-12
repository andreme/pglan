<?php

abstract class LogEntry {

	protected $multiLine = false;

	protected $dummy = false;

	protected $datetime;
	protected $user;
	protected $db;
	protected $level;

	protected $text;

	protected $hash;

	protected $duration = null;

	protected $params = array();

	public function __construct($datetime, $user, $db, $level, $text, $duration = null) {
		$this->datetime = strtotime($datetime);
		$this->user = $user;
		$this->db = $db;
		$this->level = $level;
		$this->text = $text;
		$this->duration = $duration;
	}

	public function isMultiLine() {
		return $this->multiLine;
	}

	public function isDummy() {
		return $this->dummy;
	}

	public function setDummy($dummy) {
		$this->dummy = !!$dummy;
	}

	public function addLine($line) {
		if (!$this->multiLine) {
			throw new Exception();
		}

		$this->text .= "\n".$line;
	}

	public function finish() {
		$this->calcHash();
	}

	protected function calcHash() {
		$this->hash = md5($this->text."#".$this->db.'#'.$this->user.'#'.$this->level);
	}

	public function getHash() {
		return $this->hash;
	}

	public function addParam($name, $value) {
		$this->params[$name] = $value;
	}

	public function getDatetime() {
		return $this->datetime;
	}

	public function getText() {
		return $this->text;
	}

	public function getParams() {
		return $this->params;
	}

	public function getDuration() {
		return $this->duration;
	}

	public function getExportData() {
		$result = array(
			'Text' => $this->text,
			'User' => $this->user,
			'DB' => $this->db,
			'Level' => $this->level,
			'Hash' => $this->hash,
		);

		return $result;
	}

	abstract public function getType();

}
