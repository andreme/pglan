<?php

class DurationPart extends LogLinePart {

	private $duration;
	private $unit;

	public function __construct($duration, $unit) {
		parent::__construct();

		$this->duration = $duration;
		$this->unit = $unit;
	}

	public function getDurationInMS() {
		switch ($this->unit) {
			case 's':
				return (float)($this->duration * 1000);
			case 'us':
				return (float)(round($this->duration) / 1000);
			case 'ms':
				return (float)$this->duration;
			default:
				throw new ParseException('Unknown duration unit: '.$this->unit);
		}
	}

}
