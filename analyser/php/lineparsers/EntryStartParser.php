<?php

class EntryStartParser extends LogLinePartParser {

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {

		if (!($logLine->getLastPart() instanceof BeginOfLinePart)) {
			return false;
		}

		$matches = null;

		if (ematch("/^(?<datetime>\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)(?: [a-z]{3})?(?:\s(?<user>[^\s]*))?(?:\s(?<db>[^\s]*))?\s(?<level>LOG|DEBUG|CONTEXT|WARNING|ERROR|FATAL|PANIC|HINT|DETAIL|NOTICE|STATEMENT|INFO|LOCATION):\s+(?<remainder>.*)$/i",
				$logLine->getRemainder(), $matches)) {

			$logLine->addPart(new LogTimePart($matches['datetime'].' UTC'));

			if ($matches['user'] or $matches['db']) {
				$logLine->addPart(new ConnectionPart($matches['db'], $matches['user']));
			}

			$logLine->addPart(new LogLevelPart($matches['level']));

			$logLine->setRemainder($matches['remainder']);

			return true;
		}

		return false;
	}

}
