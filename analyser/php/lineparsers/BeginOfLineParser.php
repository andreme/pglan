<?php

class BeginOfLineParser extends LogLinePartParser {

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {
		if (!$logLine->getLastPart()) {

			$logLine->addPart(new BeginOfLinePart());
			return true;
		}

		return false;
	}

}
