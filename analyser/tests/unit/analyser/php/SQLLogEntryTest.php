<?php

class SQLLogEntryTest extends PHPUnit_Framework_TestCase {

	const VALUE_NUMBER = 9812;
	const VALUE_STRING = 'abc';

	const SECOND_LINE = 'def';

	/**
	 * @var SQLLogEntry
	 */
	private $sqlLogEntry;

	protected function setUp() {
		$this->sqlLogEntry = new SQLLogEntry('now', '', '', '', "SELECT ".self::VALUE_NUMBER.", '".self::VALUE_STRING."' FROM");
	}

	public function testTypeIsQuery() {
		$this->assertEquals('Query', $this->sqlLogEntry->getType());
	}

	public function testHashGetsCalculated() {
		$this->sqlLogEntry = new SQLLogEntry('now', '', '', '', "SELECT ".self::VALUE_NUMBER.", ".self::VALUE_NUMBER." FROM");

		$this->sqlLogEntry->finish();

		$this->assertNotEmpty($this->sqlLogEntry->getHash());
	}

	public function testAddLine() {
		$this->sqlLogEntry->addLine(self::SECOND_LINE);

		$this->assertContains(self::SECOND_LINE, $this->sqlLogEntry->getText());
	}

}
