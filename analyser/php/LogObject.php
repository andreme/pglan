<?php

class LogObject {

	private $entry;

	private $events = [];

	/**
	 *
	 * @param LogEntry $entry
	 */
	public function __construct($entry) {
		$this->entry = $entry;

		$this->addEvent($entry);
	}

	/**
	 *
	 * @param LogEntry $entry
	 */
	public function addEvent($entry) {
		$this->events[] = $entry->getEventData();
	}

	public function jsonSerialize() {

		$result = $this->entry->getExportData();

		$result['Events'] = $this->events;

		return $result;
	}

	/**
	 *
	 * @return LogEntry
	 */
	public function getEntry() {
		return $this->entry;
	}

	public function getEvents() {
		return $this->events;
	}

}
