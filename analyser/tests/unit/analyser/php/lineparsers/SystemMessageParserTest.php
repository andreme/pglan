<?php

class SystemMessageParserTest extends PGLANTestCase {

	const LINE_DB_STARTUP = 'the database system is starting up';
	const LINE_AUTOVAC_STARTED = 'autovacuum launcher started';

	/**
	 * @var SystemMessageParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new SystemMessageParser();
	}

	private function setupLine($line) {
		$line = new LogLine($line);

		$levelPart = new LogLevelPart('FATAL');

		$line->addPart($levelPart);

		return $line;
	}

	public function testParseDBStartup() {

		$line = $this->setupLine(self::LINE_DB_STARTUP);

		$this->parser->parse($line);

		$part = $line->getPart('SystemMessage');
		$this->assertInstanceOf('SystemMessagePart', $part);
	}

	public function testParseExecute() {

		$line = $this->setupLine(self::LINE_AUTOVAC_STARTED);

		$this->parser->parse($line);

		$part = $line->getPart('SystemMessage');
		$this->assertInstanceOf('SystemMessagePart', $part);
	}

}
