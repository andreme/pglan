<?php

class SysLogParser extends LogLinePartParser {

	const SYSLOG_PREFIX_REGEX = '/^\w{3} [\d\s]\d \d\d:\d\d:\d\d [^\s]* [^:]*: \[\d+-\d+\] /i';

	public static function isSysLog($s) {
		return preg_match(self::SYSLOG_PREFIX_REGEX, $s) == 1;
	}

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {
		if ($logLine->getLastPart() instanceof BeginOfLinePart) {

			$remainder = preg_replace(self::SYSLOG_PREFIX_REGEX, null, $logLine->getRemainder());

			$remainder = str_replace(array('#011', '#015'), array("\t", null), $remainder);

			$logLine->setRemainder($remainder);

			// returning true here would result in an endless loop
		}

		return false;
	}

}
