<?php

class CheckpointParser extends LogLinePartParser {

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {

		if (!($logLine->getLastPart() instanceof LogLevelPart)) {
			return false;
		}

		if ($logLine->getRemainder() == 'checkpoint starting: time') {
			$logLine->setIgnoreEntry(true);

			$logLine->setRemainder(false);

			return true;
		}

		$matches = null;

		if (ematch("/^checkpoint complete: wrote (?<BuffersWritten>\d+) buffers \((?<BuffersPercentage>[\d\.]+)%\); (?<TXFilesAdded>\d+) transaction log file\(s\) added, (?<TXFilesRemoved>\d+) removed, (?<TXFilesRecycled>\d+) recycled; write=(?<WriteTime>[\d\.]+) s, sync=(?<SyncTime>[\d\.]+) s, total=(?<TotalTime>[\d\.]+) s; sync files=(?<FilesSynced>\d+), longest=(?<LongestTime>[\d\.]+) s, average=(?<AverageTime>[\d\.]+) s$/i", $logLine->getRemainder(), $matches)) {

			$logLine->addPart(new CheckpointPart($matches['BuffersWritten'], $matches['BuffersPercentage'], $matches['WriteTime'], $matches['SyncTime'], $matches['TotalTime']));

			$logLine->setRemainder(false);

			$logLine->setEntry($this->createEntry($logLine));

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param LogLine $logLine
	 */
	protected function createEntry($logLine) {

		$datetime = null;
		$buffersWritten = null;
		$buffersPercentage = null;
		$writeTime = null;
		$syncTime = null;
		$totalTime = null;

		if (($logTimePart = $logLine->getPart('LogTime'))) {
			$datetime = $logTimePart->getTimestamp();
		}

		if (($checkpointPart = $logLine->getPart('Checkpoint'))) {
			$buffersWritten = $checkpointPart->getBuffersWritten();
			$buffersPercentage = $checkpointPart->getBuffersPercentage();
			$writeTime = $checkpointPart->getWriteTime();
			$syncTime = $checkpointPart->getSyncTime();
			$totalTime = $checkpointPart->getTotalTime();
		}

		return new CheckpointEntry($datetime, $buffersWritten, $buffersPercentage, $writeTime, $syncTime, $totalTime);
	}

}
