<?php

class CheckpointEntry extends LogEntry {

	protected $aggregatable = false;

	private $buffersWritten;
	private $buffersPercentage;
	private $writeTime;
	private $syncTime;
	private $totalTime;

	public function __construct($datetime, $buffersWritten, $buffersPercentage, $writeTime, $syncTime, $totalTime) {
		parent::__construct($datetime, null, null, null, null);

		$this->buffersWritten = $buffersWritten;
		$this->buffersPercentage = $buffersPercentage;
		$this->writeTime = $writeTime;
		$this->syncTime = $syncTime;
		$this->totalTime = $totalTime;
	}

	public function getExportData() {
		$result = parent::getExportData();

		unset($result['Hash']);

		$result['DateTime'] = $this->getDatetime();
		$result['BuffersWritten'] = $this->buffersWritten;
		$result['BuffersPercentage'] = $this->buffersPercentage;
		$result['WriteTime'] = $this->writeTime;
		$result['SyncTime'] = $this->syncTime;
		$result['TotalTime'] = $this->totalTime;

		return $result;
	}

	public function jsonSerialize() {
		$result = $this->getExportData();

		return $result;
	}

	public function getType() {
		return 'Checkpoint';
	}

}
