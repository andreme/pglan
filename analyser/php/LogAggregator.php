<?php

class LogAggregator {

	private $list = array();

	/**
	 *
	 * @param LogEntry $entry
	 */
	public function addEntry($entry) {
		/* @var $existingEntry LogObject */
		$existingEntry = @$this->list[$entry->getType()][$entry->getHash()];

		if ($existingEntry) {
			$existingEntry->addEvent($entry);
		} else {
			if (!isset($this->list[$entry->getType()])) {
				$this->list[$entry->getType()] = array();
			}
			$this->list[$entry->getType()][$entry->getHash()] = new LogObject($entry);
		}
	}

	public function getEntries($type) {
		return $this->list[$type];
	}

	public function getTypes() {
		return array_keys($this->list);
	}

	public function isEmpty() {
		return !$this->list;
	}

}
