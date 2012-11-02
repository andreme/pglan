<?php

class LogTimePartTest extends PGLANTestCase {

	const DATETIME = '2012-10-14 21:39:48';

	/**
	 * @var LogTimePart
	 */
	private $part;

	protected function setUp() {
		$this->part = new LogTimePart(self::DATETIME);
	}

	public function testParseDateTime() {
		$this->assertEquals(strtotime(self::DATETIME), $this->part->getTimestamp());
	}

}
