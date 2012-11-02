<?php

class BeginOfLineParserTest extends PGLANTestCase {

	/**
	 * @var BeginOfLineParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new BeginOfLineParser();
	}

	public function testCreatePartForUnparsedLine() {

		$line = new LogLine('');

		$this->assertTrue($this->parser->parse($line));
	}

	public function testDoesNotCreatePartIfLineAlreadyHasParts() {

		$line = new LogLine('');

		$line->addPart(new LogLinePart(''));

		$part = $this->parser->parse($line);

		$this->assertEquals(null, $part);
	}

}
