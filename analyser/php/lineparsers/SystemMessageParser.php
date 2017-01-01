<?php

class SystemMessageParser extends LogLinePartParser {

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {

		if (!(($lastPart = $logLine->getLastPart()) instanceof LogLevelPart)) {
			return false;
		}

		if (!in_array($lastPart->getLevel(), ['FATAL', 'LOG', 'ERROR'])) {
			return false;
		}

		if (!$this->isSystemMessage($logLine->getRemainder())) {
			return false;
		}
		
		$logLine->addPart($msgPart = new SystemMessagePart($logLine->getRemainder()));

		$logLine->setRemainder(false);

		$logLine->setEntry($this->createEntry($logLine));

		return true;
	}

	private function isSystemMessage($text) {
		
		if ($text == 'the database system is starting up') {
			return true;
		}
		
		if ($text == 'autovacuum launcher started') {
			return true;
		}

		if (preg_match('/^requested WAL segment [\dA-F]{24} has already been removed$/i', $text)) {
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

		if (($logTimePart = $logLine->getPart('LogTime'))) {
			$datetime = $logTimePart->getTimestamp();
		}

		if (($conPart = $logLine->getPart('Connection'))) {
			$user = $conPart->getUser();
			$db = $conPart->getDB();
		}

		if (($logLevelPart = $logLine->getPart('LogLevel'))) {
			$level = $logLevelPart->getLevel();
		}

		if (($sysMsgPart = $logLine->getPart('SystemMessage'))) {
			$text = $sysMsgPart->getMessage();
		}

		return new SystemLogEntry($datetime, $user, $db, $level, $text);
	}

}
