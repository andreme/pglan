<?php

class LogLineTest extends PGLANTestCase {

	const LINE = 'x';

	/**
	 * @var LogLine
	 */
	private $logLine;

	protected function setUp() {
		$this->logLine = new LogLine(self::LINE);
	}

	public function testAddingPartSetsLastPart() {
		$part = new LogLinePart('');

		$this->logLine->addPart($part);

		$this->assertEquals($part, $this->logLine->getLastPart());
	}

	public function testGetLine() {
		$this->assertEquals(self::LINE, $this->logLine->getLine());
	}

}
