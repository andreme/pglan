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

	public function testReplaceSQLValues() {
		$this->sqlLogEntry->finish();

		$params = $this->sqlLogEntry->getParams();

		$this->assertContains("'".self::VALUE_STRING."'", $params);
		$this->assertContains(self::VALUE_NUMBER, $params);
	}

	public function testTypeIsQuery() {
		$this->assertEquals('Query', $this->sqlLogEntry->getType());
	}

	public function testIsMultiLine() {
		$this->assertTrue($this->sqlLogEntry->isMultiLine());
	}

	public function testSameSQLValuesResultInOneParam() {
		$this->sqlLogEntry = new SQLLogEntry('now', '', '', '', "SELECT ".self::VALUE_NUMBER.", ".self::VALUE_NUMBER." FROM");

		$this->sqlLogEntry->finish();

		$this->assertCount(1, $this->sqlLogEntry->getParams());
	}

	public function testAddLine() {
		$this->sqlLogEntry->addLine(self::SECOND_LINE);

		$this->assertContains(self::SECOND_LINE, $this->sqlLogEntry->getText());
	}

}
