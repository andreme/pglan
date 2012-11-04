<?php

class CheckpointPart extends LogLinePart {

	private $buffersWritten;
	private $buffersPercentage;
	private $writeTime;
	private $syncTime;
	private $totalTime;

	public function __construct($buffersWritten, $buffersPercentage, $writeTime, $syncTime, $totalTime) {
		$this->buffersWritten = (int)$buffersWritten;
		$this->buffersPercentage = (float)$buffersPercentage;
		$this->writeTime = (float)$writeTime;
		$this->syncTime = (float)$syncTime;
		$this->totalTime = (float)$totalTime;
	}

	public function getBuffersWritten() {
		return $this->buffersWritten;
	}

	public function getBuffersPercentage() {
		return $this->buffersPercentage;
	}

	public function getWriteTime() {
		return $this->writeTime;
	}

	public function getSyncTime() {
		return $this->syncTime;
	}

	public function getTotalTime() {
		return $this->totalTime;
	}

}
