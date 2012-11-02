<?php

class NoMatchParser extends LogLinePartParser {

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {
		if ($logLine->getLastPart() instanceof BeginOfLinePart) {

			$logLine->addPart(new NoMatchPart($logLine->getRemainder()));

			$logLine->setRemainder(false);
			return true;
		}

		return false;
	}

}
