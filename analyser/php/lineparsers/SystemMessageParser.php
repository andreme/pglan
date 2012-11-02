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

		if (!in_array($lastPart->getLevel(), array('FATAL', 'LOG'))) {
			return false;
		}

		if (in_array($logLine->getRemainder(), array(
			'the database system is starting up',
			'autovacuum launcher started',
		))) {
			$logLine->addPart($msgPart = new SystemMessagePart($logLine->getRemainder()));

			$logLine->setRemainder(false);

			$logLine->setEntry($this->createEntry($logLine));

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
