<?php

class LogObject {

	private $entry;

	private $events = array();

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

		$event = array(
			'DateTime' => $entry->getDatetime(),
		);

		if ($entry->getParams()) {
			$event['Params'] = $entry->getParams();
		}

		if ($entry->getDuration()) {
			$event['Duration'] = $entry->getDuration();
		}

		$this->events[] = $event;
	}

	public function jsonSerialize() {

		$result = $this->entry->getExportData();

		$result['Events'] = $this->events;

		return $result;
	}

}
