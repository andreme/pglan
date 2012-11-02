<?php

class LogLineParsers {

	private $parsers = array();

	public function addParser($parser) {
		array_unshift($this->parsers, $parser);
	}

	/**
	 *
	 * @param LogLine $logLine
	 * @return LogLinePart
	 */
	public function parse($logLine) {

		if ($logLine->getRemainder() === false) {
			return false;
		}

		foreach ($this->parsers as $parser) {
			/* @var $parser LogLinePartParser */

			if (($parseResult = $parser->parse($logLine))) {
				return $parseResult;
			}
		}

		return false;
	}

}
