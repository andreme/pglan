<?php

abstract class LogLinePartParser {

	/**
	 *
	 * @param LogLine $logLine
	 * @return LogLinePart
	 */
	public abstract function parse($logLine);

}

interface MultiLineParser {

	/**
	 *
	 * @param LogLine $currentLogLine
	 * @param LogLine $nextLogLine
	 */
	public function parseNextLine($currentLogLine, $nextLogLine);

}

define('PARSER_MULTLINE_ACTION_FINISH_PENDING', 1);
define('PARSER_MULTLINE_ACTION_SKIP_NEXT', 2);
define('PARSER_MULTLINE_ACTION_IGNORE', 3);