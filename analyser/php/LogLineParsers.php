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

			if ($parser->canParse($logLine)) {
				if (($parseResult = $parser->parse($logLine))) {
					return $parseResult;
				}
			}
		}

		return false;
	}

	public function hasParser($class) {
		foreach ($this->parsers as $parser) {
			if ($parser instanceof $class) {
				return true;
			}
		}

		return false;
	}

	public function removeParser($class) {
		foreach ($this->parsers as $key => $parser) {
			if ($parser instanceof $class) {
				unset($this->parsers[$key]);
				return;
			}
		}
	}

}
