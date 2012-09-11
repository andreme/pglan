<?php

class LogEntryTest extends PGLANTestCase {

	const DEFAULT_TEXT = 'line1';
	const DATE = '2012-09-11 09:40:00';
	const DURATION = 1000;
	const LEVEL = 'notice';
	const USER = 'testuser';
	const DB = 'testdb';

	/**
	 * @var LogEntry
	 */
	private $logEntry;

	protected function setUp() {
		$this->logEntry = new LogEntry(self::DATE, self::USER, self::DB, self::LEVEL, self::DEFAULT_TEXT, self::DURATION);
	}

	public function testIsDummy() {
		$this->assertFalse($this->logEntry->isDummy());
		$this->logEntry->setDummy(true);
		$this->assertTrue($this->logEntry->isDummy());
	}

	public function testMultiLineDefaultsToFalse() {
		$this->assertFalse($this->logEntry->isMultiLine());
	}

	public function testCalculateHash() {

		$this->logEntry->finish();

		$this->assertNotEmpty($this->logEntry->getHash());
	}

	public function testAddParam() {

		$name = 'testparam';
		$value = 'testvalue';

		$this->logEntry->addParam($name, $value);

		$this->assertArrayHasKeyWithValue($name, $value, $this->logEntry->getParams());
	}

	public function testGetDatetime() {
		$this->assertEquals(strtotime(self::DATE), $this->logEntry->getDatetime());
	}

	public function testGetText() {
		$this->assertEquals(self::DEFAULT_TEXT, $this->logEntry->getText());
	}

	public function testGetDuration() {
		$this->assertEquals(self::DURATION, $this->logEntry->getDuration());
	}

	public function testGetExportData() {

		$this->logEntry->finish();

		$result = $this->logEntry->getExportData();

		$this->assertArrayHasKeyWithValue('Text', self::DEFAULT_TEXT, $result);
		$this->assertArrayHasKeyWithValue('User', self::USER, $result);
		$this->assertArrayHasKeyWithValue('DB', self::DB, $result);
		$this->assertArrayHasKeyWithValue('Level', self::LEVEL, $result);
		$this->assertArrayHasKeyWithValue('Hash', $this->logEntry->getHash(), $result);
	}

	public function testCanNotAddLineIfNotMultiline() {
		$this->setExpectedException('DOMException');

		$this->logEntry->addLine('');
	}

}
