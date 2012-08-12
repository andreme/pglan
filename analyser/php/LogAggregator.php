<?php

class LogAggregator {

	private $list = array();

	/**
	 *
	 * @param LogEntry $entry
	 */
	public function addEntry($entry) {
		/* @var $existingEntry LogObject */
		$existingEntry = @$this->list[$entry->getHash()];

		if ($existingEntry) {
			$existingEntry->addEvent($entry);
		} else {
			$this->list[$entry->getHash()] = new LogObject($entry);
		}
	}

	public function getEntries() {
		return $this->list;
	}

}
