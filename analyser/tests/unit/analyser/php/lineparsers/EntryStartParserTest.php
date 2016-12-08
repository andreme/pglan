<?php

class EntryStartParserTest extends PGLANTestCase {

	const LOGLINE = '2012-10-14 21:39:48 LOG:  X';
	const LOGLINE_WITH_CONNECTION = '2012-10-14 21:39:48 USER DB LOG:  X';
	const LOGLINE_TZ = '2012-10-14 21:39:48 EST LOG:  X';
	const LOGLINE_4CHAR_TZ = '2012-10-14 21:39:48 AEST LOG:  X';

	/**
	 * @var LogTimeParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new EntryStartParser();
	}

	private function setupLine($line) {
		$line = new LogLine($line);

		$beginOfLinePart = new BeginOfLinePart();

		$line->addPart($beginOfLinePart);

		return $line;
	}

	public function testParseTime() {

		$line = $this->setupLine(self::LOGLINE);

		$this->assertTrue($this->parser->parse($line));

		$part = $line->getPart('LogTime');

		$this->assertInstanceOf('LogTimePart', $part);

		$this->assertEquals(strtotime('2012-10-14 21:39:48 UTC'), $part->getTimestamp());
	}

	public function testParseConnection() {

		$line = $this->setupLine(self::LOGLINE_WITH_CONNECTION);

		$this->assertTrue($this->parser->parse($line));

		$part = $line->getPart('Connection');
		$this->assertInstanceOf('ConnectionPart', $part);

		$this->assertEquals('USER', $part->getUser());
		$this->assertEquals('DB', $part->getDB());
	}

	public function testParseLogLevel() {

		$line = $this->setupLine(self::LOGLINE);

		$this->assertTrue($this->parser->parse($line));

		$part = $line->getPart('LogLevel');
		$this->assertInstanceOf('LogLevelPart', $part);

		$this->assertEquals('LOG', $part->getLevel());
	}

	public function testIgnoreTZWhenParsing() {

		$line = $this->setupLine(self::LOGLINE_TZ);

		$this->assertTrue($this->parser->parse($line));

		$this->assertEquals(strtotime('2012-10-14 21:39:48 UTC'), $line->getPart('LogTime')->getTimestamp());
	}

	public function test4CharTZ() {

		$line = $this->setupLine(self::LOGLINE_4CHAR_TZ);

		$this->assertTrue($this->parser->parse($line));

		$this->assertEquals(strtotime('2012-10-14 21:39:48 UTC'), $line->getPart('LogTime')->getTimestamp());
	}

}
