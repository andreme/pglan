<?php

class TemporaryFileParser extends LogLinePartParser implements MultiLineParser {

	/**
	 *
	 * @var SQLLogEntry
	 */
	private $finalisingEntry;

	private $replacedValues = array();


	public function canParse($logLine) {
		return preg_match('/^temporary file: path/i', $logLine->getRemainder());
	}

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {

		$matches = null;
		
		if (ematch('/^temporary file: path "(?<file>.+)\.\d+", size (?<size>\d+)/i', $logLine->getRemainder(), $matches)) {
			$logLine->addPart(new TemporaryFilePart($matches['file'], (int)$matches['size']));
			
			$logLine->setRemainder(false);
			
			$logLine->setEntry($this->createEntry($logLine));
			
			$logLine->setMultiLineParser($this);
			
			return true;
		}
		
		return false;
	}

	/**
	 *
	 * @param LogLine $logLine
	 */
	protected function createEntry($logLine) {

		$datetime = null;
		$user = null;
		$db = null;
		$level = null;
		$text = null;
//		$duration = null;
		$file = null;
		$size = null;

		if (($logTimePart = $logLine->getPart('LogTime'))) {
			$datetime = $logTimePart->getTimestamp();
		}

//		if (($durationPart = $logLine->getPart('Duration'))) {
//			$duration = $durationPart->getDurationInMS();
//		}

		if (($conPart = $logLine->getPart('Connection'))) {
			$user = $conPart->getUser();
			$db = $conPart->getDB();
		}

		if (($logLevelPart = $logLine->getPart('LogLevel'))) {
			$level = $logLevelPart->getLevel();
		}

		if (($queryPart = $logLine->getPart('Query'))) {
			$text = $queryPart->getText();
		}

		if (($tempPart = $logLine->getPart('TemporaryFile'))) {
			$file = $tempPart->getFile();
			$size = $tempPart->getSize();
		}

		return new TemporaryFileEntry($datetime, $user, $db, $level, $text, $file, $size);
	}

	/**
	 *
	 * @param LogLine $currentLogLine
	 * @param LogLine $nextLogLine
	 */
	public function parseNextLine($currentLogLine, $nextLogLine) {
//		if ($nextLogLine and ($nextLogLine->getLastPart() instanceof NoMatchPart)) {
//			$entry = $currentLogLine->getEntry();
//			/* @var $entry SQLLogEntry */
//
//			$entry->addLine($nextLogLine->getLastPart()->getText());
//
//			return PARSER_MULTLINE_ACTION_SKIP_NEXT;
//		}

//		if ($nextLogLine and ($nextLogLine->getLastPart() instanceof ParametersPart)) {
//			$entry = $currentLogLine->getEntry();
//			/* @var $entry SQLLogEntry */
//
//			$entry->setParams($nextLogLine->getLastPart()->getParams());
//
//			return PARSER_MULTLINE_ACTION_SKIP_NEXT;
//		}

//		if ($nextLogLine and ($nextLogLine->getLastPart() instanceof DurationPart)) {
//			$entry = $currentLogLine->getEntry();
//			/* @var $entry SQLLogEntry */
//			$entry->setDuration($nextLogLine->getLastPart()->getDurationInMS());
//
//			return PARSER_MULTLINE_ACTION_SKIP_NEXT;
//		}

//		$this->finishLine($currentLogLine);

		return PARSER_MULTLINE_ACTION_FINISH_PENDING;
	}

	private function finishLine($logLine) {
		$this->finalisingEntry = $logLine->getEntry();
		$this->replacedValues = array();

		$this->replaceSQLValues();

		$this->finalisingEntry = null;
	}

	private function replaceSQLValues() {
		$patterns = array(
			"/('[^']*')/", // strings
			"/([^a-zA-Z_\$\-\d])(\-?\d[\d\.]*)/", // numbers
		);

		$this->finalisingEntry->setText(preg_replace_callback($patterns, array($this, 'replaceSQLValuesAddParam'), $this->finalisingEntry->getText()));
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

		$ct = count($this->finalisingEntry->getParams());

		$countFullAlpha = floor($ct / 26);

		$ct -= ($countFullAlpha * 26);

		while ($countFullAlpha) {
			$name .= chr(64+min(26, $countFullAlpha));
			$countFullAlpha -= min(26, $countFullAlpha);
		}

		$name .= chr(65+$ct);

		$this->finalisingEntry->addParam($name, $value);

		$this->replacedValues['Z'.$value] = $name;

		return $prefix.$name;
	}

}
