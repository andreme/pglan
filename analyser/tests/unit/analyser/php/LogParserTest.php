<?php

class LogParserTest extends PGLANTestCase {

	const LOGLINE = "2012-10-14 21:39:48 EST LOG:  duration: 2.0 ms  statement: select 2\n";
	const LOGLINE_PARSE = "2012-10-14 21:39:48 EST LOG:  duration: 2.0 ms  parse pdo_stmt_00000001: SELECT 1\n";
	const LOGLINE_MULTILINE = "2012-10-14 21:39:48 EST LOG:  duration: 2.0 ms  statement: select z\nfrom test";
	const LOGLINE_MULTILINE_WITH_EMPTY_LINE = "2012-10-14 21:39:48 EST LOG:  duration: 2.0 ms  statement: select z\n\nfrom test";
	const LOGLINE_PARAMETER = "2012-10-14 21:39:48 EST LOG:  duration: 2.0 ms  statement: select 3\n2012-10-14 21:39:48 EST DETAIL:  parameters: $1 = 'A'";
	const LOGLINE_CHECKPOINT = "2012-10-14 21:39:48 EST LOG:  checkpoint complete: wrote 1563 buffers (0.4%); 0 transaction log file(s) added, 0 removed, 0 recycled; write=269.905 s, sync=0.003 s, total=269.909 s; sync files=45, longest=0.000 s, average=0.000 s";

	/**
	 * @var LogParser
	 */
	private $logParser;

	/**
	 *
	 * @var LogLineParsers
	 */
	private $parsers;

	/**
	 *
	 * @var LogAggregator
	 */
	private $entries;

	protected function setUp() {

		$this->parsers = new LogLineParsers();

		$this->entries = new LogAggregator();

		$this->logParser = new LogParser($this->parsers, $this->entries);
	}

	private function createReader($text) {
		$reader = new Reader();

		$fp = fopen("php://memory", 'r+');
		fputs($fp, $text);
		rewind($fp);

		$reader->setHandle($fp);

		$reader->nextLine();

		return $reader;
	}

	public function testParseWithOutParserThrowsException() {
		$this->markTestSkipped();

		$this->setExpectedException('ParseException');

		$reader = $this->createReader('X');

		$this->logParser->parse($reader);
	}

	public function testParse() {

		$reader = $this->createReader(self::LOGLINE);

		$this->parsers->addParser(new QueryParser());
		$this->parsers->addParser(new DurationParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());

		$this->logParser->parse($reader);

		$this->assertFalse($this->entries->isEmpty());
	}

	public function testParseIgnoresIgnoredLines() {

		$reader = $this->createReader(self::LOGLINE_PARSE);

		$this->parsers->addParser(new QueryParser());
		$this->parsers->addParser(new DurationParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());

		$this->logParser->parse($reader);

		$this->assertTrue($this->entries->isEmpty());
	}

	public function testMultiLineParse() {

		$reader = $this->createReader(self::LOGLINE_MULTILINE);

		$this->parsers->addParser(new NoMatchParser());
		$this->parsers->addParser(new QueryParser());
		$this->parsers->addParser(new DurationParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());

		$this->logParser->parse($reader);

		$this->assertFalse($this->entries->isEmpty());

		$queryEntries = $this->entries->getEntries('Query');
		reset($queryEntries);
		$logObject = current($queryEntries);
		/* @var $logObject SQLLogEntry */

		$this->assertEquals("select z\nfrom test", $logObject->getEntry()->getText());
	}

	public function testMultiLineWithEmptyLineParse() {

		$reader = $this->createReader(self::LOGLINE_MULTILINE_WITH_EMPTY_LINE);

		$this->parsers->addParser(new NoMatchParser());
		$this->parsers->addParser(new QueryParser());
		$this->parsers->addParser(new DurationParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());

		$this->logParser->parse($reader);

		$this->assertFalse($this->entries->isEmpty());

		$queryEntries = $this->entries->getEntries('Query');
		reset($queryEntries);
		$logObject = current($queryEntries);
		/* @var $logObject SQLLogEntry */

		$this->assertEquals("select z\n\nfrom test", $logObject->getEntry()->getText());
	}

	public function testQueryWithParametersParse() {

		$reader = $this->createReader(self::LOGLINE_PARAMETER);

		$this->parsers->addParser(new NoMatchParser());
		$this->parsers->addParser(new ParametersParser(10));
		$this->parsers->addParser(new QueryParser());
		$this->parsers->addParser(new DurationParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());

		$this->logParser->parse($reader);

		$this->assertFalse($this->entries->isEmpty());

		$queryEntries = $this->entries->getEntries('Query');
		reset($queryEntries);
		$logObject = current($queryEntries);
		/* @var $queryEntries SQLLogEntry */

		$this->assertArrayHasKeyWithValue("$1", "'A'", $logObject->getEntry()->getParams());
	}

	public function testParseCheckpoint() {

		$reader = $this->createReader(self::LOGLINE_CHECKPOINT);

		$this->parsers->addParser(new CheckpointParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());

		$this->logParser->parse($reader);

		$queryEntries = $this->entries->getEntries('Checkpoint');
		reset($queryEntries);
		$entry = current($queryEntries);

		$this->assertInstanceOf("CheckpointEntry", $entry);
	}

}
