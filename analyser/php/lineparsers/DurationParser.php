<?php

class DurationParser extends LogLinePartParser {

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {

		if (!($logLine->getLastPart() instanceof LogLevelPart)) {
			return false;
		}

		$matches = null;

		if (ematch("/^duration:\s+(?<duration>[\d\.]+)\s(?<unit>sec|ms|us)[\s]+(?<remainder>.*)$/i",
				$logLine->getRemainder(), $matches)) {

			$logLine->addPart(new DurationPart($matches['duration'], $matches['unit']));

			$logLine->setRemainder($matches['remainder']);

			return true;
		}

		return false;
	}

}
