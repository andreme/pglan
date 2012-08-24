<?php

class SQLLogEntry extends LogEntry {

	protected $multiLine = true;

	private $replacedValues = array();

	public function finish() {
		$this->replaceSQLValues();

		parent::finish();
	}

	private function replaceSQLValues() {
		$patterns = array(
			"/('[^']*')/", // strings
			"/([^a-zA-Z_\$-\d])(-?\d[\d\.]*)/", // numbers
		);

		$this->text = preg_replace_callback($patterns, array($this, 'replaceSQLValuesAddParam'), $this->text);
	}

	private function replaceSQLValuesAddParam($match) {

		$prefix = null;
		if (count($match) > 2) {
			$prefix = $match[1];
			$value = $match[2];
		} else {
			$value = $match[1];
		}

		if (isset($this->replacedValues['Z'.$value])) {
			return $prefix.$this->replacedValues['Z'.$value];
		}

		$name = '$';

		$ct = count($this->params);

		$countFullAlpha = floor($ct / 26);
		
		$ct -= ($countFullAlpha * 26);
		
		while ($countFullAlpha) {
			$name .= chr(64+min(26, $countFullAlpha));
			$countFullAlpha -= min(26, $countFullAlpha);
		}

		$name .= chr(65+$ct);

		$this->addParam($name, $value);

		$this->replacedValues['Z'.$value] = $name;

		return $prefix.$name;
	}

	public function getType() {
		return 'Query';
	}

}
