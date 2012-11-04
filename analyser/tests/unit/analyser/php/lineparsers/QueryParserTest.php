<?php

class QueryParserTest extends PGLANTestCase {

	const LINE = 'statement: select 1';
	const LINE_EXECUTE = 'execute pdo_stmt_00000001: SELECT 1';
	const LINE_PARSE = 'parse pdo_stmt_00000001: SELECT 1';
	const LINE_BIND = 'bind pdo_stmt_00000001: SELECT $1';
	const LINE_DEALLOCATE = 'statement: DEALLOCATE pdo_stmt_00000001';
	const LINE_EMPTY = 'statement:';
	const LINE_NORMALISE = "parse pdo_stmt_00000001: SELECT 1, 'A'";

	/**
	 * @var QueryParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new QueryParser();
	}

	private function setupLine($line = '') {
		$line = new LogLine($line);

		$durationPart = new DurationPart(1, 'ms');

		$line->addPart($durationPart);

		return $line;
	}

	public function testParseStatement() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Query');
		$this->assertInstanceOf('QueryPart', $part);
	}

	public function testParseExecute() {

		$line = $this->setupLine(self::LINE_EXECUTE);

		$this->parser->parse($line);

		$part = $line->getPart('Query');
		$this->assertInstanceOf('QueryPart', $part);
	}

	public function testParseIgnoreParse() {

		$line = $this->setupLine(self::LINE_PARSE);

		$this->parser->parse($line);

		$part = $line->getPart('Query');
		$this->assertInstanceOf('QueryPart', $part);
		$this->assertTrue($line->getIgnoreEntry());
	}

	public function testParseIgnoreBind() {

		$line = $this->setupLine(self::LINE_BIND);

		$this->parser->parse($line);

		$part = $line->getPart('Query');
		$this->assertInstanceOf('QueryPart', $part);
		$this->assertTrue($line->getIgnoreEntry());
	}

	public function testParseIgnoreDeallocate() {

		$line = $this->setupLine(self::LINE_DEALLOCATE);

		$this->parser->parse($line);

		$part = $line->getPart('Query');
		$this->assertInstanceOf('QueryPart', $part);
		$this->assertTrue($line->getIgnoreEntry());
	}

	public function testParseFirstLineCanBeEmpty() {

		$line = $this->setupLine(self::LINE_EMPTY);

		$this->parser->parse($line);

		$part = $line->getPart('Query');
		$this->assertInstanceOf('QueryPart', $part);
	}

	public function testParseQueryText() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Query');

		$this->assertEquals('select 1', $part->getText());
	}

	public function testParseQueryCreatesLogEntry() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$this->assertInstanceOf('SQLLogEntry', $line->getEntry());
	}

	public function testParseSetsMultiLine() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$this->assertTrue($line->isMultiLine());
	}

	public function testParseMultiLine() {

		$currentLine = $this->setupLine(self::LINE);
		$currentLine->setEntry(new SQLLogEntry(null, null, null, null, null));

		$nextLine = $this->getMock('LogLine', array(), array(''));

		$secondLine = new NoMatchPart('from x');

        $nextLine->expects($this->atLeastOnce())
             ->method('getLastPart')
             ->will($this->returnValue($secondLine));

		$this->assertEquals(PARSER_MULTLINE_ACTION_SKIP_NEXT, $this->parser->parseNextLine($currentLine, $nextLine));

		$this->assertEquals("\nfrom x", $currentLine->getEntry()->getText());
	}

	public function testParseNormalise() {

		$currentLine = $this->setupLine();
		$currentLine->setEntry(new SQLLogEntry(null, null, null, null, self::LINE_NORMALISE));

		$this->assertEquals(PARSER_MULTLINE_ACTION_FINISH_PENDING, $this->parser->parseNextLine($currentLine, null));

		$this->assertCount(2, $currentLine->getEntry()->getParams());
		$this->assertArrayHasKeyWithValue('$A', "'A'", $currentLine->getEntry()->getParams());
		$this->assertArrayHasKeyWithValue('$B', "1", $currentLine->getEntry()->getParams());
	}

	public function testParseWithParameters() {

		$currentLine = $this->setupLine(self::LINE);
		$currentLine->setEntry(new SQLLogEntry(null, null, null, null, null));

		$nextLine = $this->getMock('LogLine', array(), array(''));

		$paramPart = new ParametersPart();
		$paramPart->addParam('P1', "'T'");

        $nextLine->expects($this->atLeastOnce())
             ->method('getLastPart')
             ->will($this->returnValue($paramPart));

		$this->assertEquals(PARSER_MULTLINE_ACTION_SKIP_NEXT, $this->parser->parseNextLine($currentLine, $nextLine));

		$this->assertArrayHasKeyWithValue('P1', "'T'", $currentLine->getEntry()->getParams());
	}

}
