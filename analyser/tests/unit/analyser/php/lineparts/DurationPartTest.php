<?php

class DurationPartTest extends PGLANTestCase {

	/**
	 * @var DurationPart
	 */
	private $part;

	public function testConvertToSecondsToMS() {
		$this->part = new DurationPart('1', 's');

		$this->assertEquals(1000, $this->part->getDurationInMS());
	}

	public function testConvertToMSToMS() {
		$this->part = new DurationPart('1', 'ms');

		$this->assertEquals(1, $this->part->getDurationInMS());
	}

	public function testConvertToUSToMS() {
		$this->part = new DurationPart('1000', 'us');

		$this->assertEquals(1, $this->part->getDurationInMS());
	}

	public function testUnknowUnitThrowsException() {
		$this->part = new DurationPart('1', 'X');

		$this->setExpectedException('ParseException');

		$this->part->getDurationInMS();
	}

}
