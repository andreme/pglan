<?php

class DurationParserTest extends PGLANTestCase {

	/**
	 * @var DurationParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new DurationParser();
	}

	public function testParse() {

		$line = new LogLine('duration: 2.000 ms  statement:');

		$part = new LogLevelPart('Z');

		$line->addPart($part);

		$this->parser->parse($line);

		$part = $line->getPart('Duration');
		$this->assertInstanceOf('DurationPart', $part);
	}

}
