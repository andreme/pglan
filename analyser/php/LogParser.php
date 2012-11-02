<?php

class LogParser {

	/**
	 *
	 * @var LogLineParsers
	 */
	private $parsers;

	/**
	 *
	 * @var LogAggregator
	 */
	private $entries;

	private $pendingLines = array();

	public function __construct($parsers, $entries) {
		$this->parsers = $parsers;
		$this->entries = $entries;
	}

	public function parse(Reader $reader) {

		while (!$reader->isEof()) {
			$logLine = $this->createLogLine($reader->getLine());

			$this->parseLine($logLine);

			$reader->nextLine();
		}

		$this->foundLine(null); // flush pending lines
	}

	/**
	 *
	 * @param LogLine $logLine
	 * @return \LogEntry
	 */
	private function parseLine($logLine) {

		while ($this->parsers->parse($logLine)) {
		}

		$this->foundLine($logLine);
	}

	private function createLogLine($line) {
		return new LogLine($line);
	}

	/**
	 *
	 * @param LogLine $logLine
	 */
	private function foundLine($logLine) {

		$skipFoundEntry = false;

		foreach ($this->pendingLines as $index => $pendingLine) {
			/* @var $pendingLine LogLine */

			switch ($pendingLine->parseNextLine($logLine)) {
				case PARSER_MULTLINE_ACTION_FINISH_PENDING:
					if (!$pendingLine->getIgnoreEntry()) {
						$this->entries->addEntry($pendingLine->getEntry());
					}
					unset($this->pendingLines[$index]);
					break;
				case PARSER_MULTLINE_ACTION_SKIP_NEXT:
					$skipFoundEntry = true;
					break;
//				case PARSER_MULTLINE_ACTION_IGNORE:
//					break;
				default:
					throw new Exception('Unknown parseNextLine Result');
			}
		}

		if (!$skipFoundEntry and $logLine) {
			if ($logLine->isMultiLine()) {
				$this->pendingLines[] = $logLine;
			} elseif (!$logLine->getIgnoreEntry()) {
				if (!($logLine->getEntry() instanceof LogEntry)) {
					echo $logLine->getLine(), "\n";
					return;
//					throw new ParseException('Could not find LogEntry');
				}

				$this->entries->addEntry($logLine->getEntry());
			}
		}
	}

}
