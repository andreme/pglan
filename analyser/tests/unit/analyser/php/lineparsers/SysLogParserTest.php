<?php

class SysLogParserTest extends PGLANTestCase {

	const LOGLINE = 'Jul 29 13:42:19 host postgres[17384]: [4805-1] 2012-07-29 13:42:19 EST   LOG:  #011 #015';

	/**
	 * @var SysLogParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new SysLogParser();
	}

	private function setupLine($line) {
		$line = new LogLine($line);

		$beginOfLinePart = new BeginOfLinePart();

		$line->addPart($beginOfLinePart);

		return $line;
	}

	public function testDetectSysLog() {

		$this->assertTrue(SysLogParser::isSysLog(self::LOGLINE));
	}

	public function testStripSysLogPrefix() {

		$line = $this->setupLine(self::LOGLINE);

		$this->parser->parse($line);

		$this->assertStringStartsWith('2012-', $line->getRemainder()) ;
	}

	public function testConvertSpecialCharacters() {

		$line = $this->setupLine(self::LOGLINE);

		$this->parser->parse($line);

		$this->assertNotContains('#011', $line->getRemainder()) ;
		$this->assertNotContains('#015', $line->getRemainder()) ;
	}

}
