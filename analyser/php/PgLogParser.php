<?php

class PgLogParser extends Parser {

	/**
	 *
	 * @var LogEntry
	 */
	private $entry = null;

	private $start;
	private $duration;

	protected $line;

	/**
	 *
	 * @param Reader $reader
	 */
	public function parse() {

		while (!$this->reader->isEof()) {

			$this->line = $this->reader->getLine();

			if (!$this->inspectLine()) {
//				$this->logger->unknown($this->line, $this->reader->getLineNo());
//				$this->logger->setDebugging(true);
//				$this->line = $this->reader->getLine();
//				$this->inspectLine();
//				$this->logger->setDebugging(false);
			}
//			echo $this->reader->getLine(), "\n";

			$this->reader->nextLine();
		}

		$this->endEntry();
	}

	protected function inspectLine() {
		if ($this->isStartOfLogEntry()) {

			$this->logger->debug('Is Start Of Log Entry, Level: '.$this->start['level']);

			switch ($this->start['level']) {
				case 'LOG':
					if ($this->isDuration()) {
						$this->logger->debug('Is Duration, Type: '.$this->duration['type']);
						switch ($this->duration['type']) {
							case 'statement':
								$this->logger->debug('Dur3: '.$this->duration['remainder']);
								if (beginsWith($this->duration['remainder'], ': DEALLOCATE')) {
									return true; // ignore
								}
								// fallthrough here
							case 'execute':
								if (beginsWith($this->duration['remainder'], ': COPY ')) {
									$this->startSQLEntry('');
									$this->entry->setDummy(true); // ignore these
									return true; // ignore backups
								}

								$sql = null;
								if (preg_match('/^[^:]*:\s?(.*)$/i', $this->duration['remainder'], $sql)) {
									$this->startSQLEntry($sql[1], $this->durationToMS($this->duration['time'], $this->duration['timeunit']));
									return true;
								} else {
									return false;
								}
							case 'parse':
							case 'bind':
								$this->startSQLEntry('');
								$this->entry->setDummy(true); // ignore these
								return true;
						}
					} elseif ($this->isSystemMessage($this->start['remainder'])) {
						return true;
					}
				case 'DETAIL':
					if ($this->isParameters($this->start['remainder'])) {
						return true;
					}
				case 'FATAL':
					if ($this->isSystemMessage($this->start['remainder'])) {
						return true;
					}
			}

			$this->endEntry();

			return false;
			// if $lastEntry finish
		} elseif ($this->entry and $this->entry->isMultiLine()) {
			$this->entry->addLine($this->line);
			return true;
		} else {
			return false;
		}

		return false;
	}

	protected function isStartOfLogEntry() {
		if (preg_match("/^(?<datetime>\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)(?: [a-z]{3})?\s+(?<user>[^\s]*)?\s?(?<db>[^\s]*)?\s(?<level>LOG|DEBUG|CONTEXT|WARNING|ERROR|FATAL|PANIC|HINT|DETAIL|NOTICE|STATEMENT|INFO|LOCATION):[\s]+(?<remainder>.*)$/i",
			$this->line, $this->start)) {

			return true;
		}
		return false;
	}

	protected function isDuration() {
		$this->logger->debug('Trying to match duration: '.$this->start['remainder']);
		if (preg_match("/^duration:\s+(?<time>[\d\.]+)\s(?<timeunit>sec|ms|us)[\s]+(?<type>[^\s:]+)\s*(?<remainder>.*)$/i",
			$this->start['remainder'], $this->duration)) {

			return true;
		}

		return false;
	}

	private function durationToMS($duration, $unit) {
		switch ($unit) {
			case 's':
				$duration = $duration * 1000;
				break;
			case 'us':
				$duration = round($duration) / 1000;
				break;
			case 'ms':
				break;
			default:
				throw new Exception('Unknown duration unit: '.$unit);
		}

		return (float)$duration;
	}

	private function startSQLEntry($sql, $duration = null) {
		$this->endEntry();

		$this->entry = new SQLLogEntry($this->start['datetime'].' UTC', $this->start['user'],
			$this->start['db'], $this->start['level'], $sql, $duration);
	}

	private function endEntry() {
		if (!$this->entry) {
			return;
		}

		if (!$this->entry->isDummy()) {
			$this->entry->finish();

			$this->list->addEntry($this->entry);
		}

		$this->entry = null;
	}

	private function isSystemMessage($line) {
		if ((stripos($line, 'database system') !== false)
			or (stripos($line, 'autovacuum') !== false)
		) {
			$this->endEntry();

			$this->entry = new SystemLogEntry($this->start['datetime'], $this->start['user'],
				$this->start['db'], $this->start['level'], $line);
			return true;
		}

		return false;
	}

	private function isParameters($line) {
		$params = null;
		if (!($this->entry instanceof SQLLogEntry) or !preg_match('/^parameters: (.*)/', $line, $params)) {
			return false;
		}

		$line = $params[1];

		if (preg_match_all('/(\$[0-9]+) = (.*)(?=(?:, \$[0-9]+ = |\z))/U', $line, $params, PREG_SET_ORDER)) {

			foreach ($params as $param) {

				$paramValue = $param[2];
				if (substr($paramValue, 0, 1) == "'") {
					$trimmedValue = substr($paramValue, 1, -1);
					if (is_numeric($trimmedValue)) {
						$paramValue = $trimmedValue;
					}
				}

				$this->entry->addParam($param[1], $paramValue);
			}
			return true;
		}

		return false;
	}

}
