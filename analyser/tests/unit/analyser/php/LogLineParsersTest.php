<?php

class LogLineParsersTest extends PGLANTestCase {

	/**
	 * @var LogLineParsers
	 */
	private $parsers;

	protected function setUp() {
		$this->parsers = new LogLineParsers();
	}

	public function testFindSuitableParser() {

		$line = new LogLine('X');

        $part = new LogLinePart('X');

        $parser = $this->getMock('LogLinePartParser');

        $parser->expects($this->once())
             ->method('parse')
             ->will($this->returnValue(true));

		$this->parsers->addParser($parser);

		$this->assertTrue($this->parsers->parse($line));
	}

	public function testFindingNoParser() {
		$line = new LogLine('X');

		$this->assertFalse($this->parsers->parse($line));
	}

	public function testNothingLeftToParseReturnsFalse() {

		$line = new LogLine('');

		$this->assertFalse($this->parsers->parse($line));
	}

	public function testHasParserReturnsTrueWhenFindingParser() {
		$parser = new BeginOfLineParser();

		$this->parsers->addParser($parser);

		$this->assertTrue($this->parsers->hasParser('BeginOfLineParser'));
	}

	public function testHasParserReturnsFalseWhenNotFindingParser() {

		$this->assertFalse($this->parsers->hasParser('X'));
	}

	public function testRemoveParserRemovesParser() {
		$parser = new BeginOfLineParser();

		$this->parsers->addParser($parser);

		$this->parsers->removeParser('BeginOfLineParser');

		$this->assertFalse($this->parsers->hasParser('BeginOfLineParser'));
	}

}
