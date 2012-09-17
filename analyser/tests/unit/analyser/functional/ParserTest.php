<?php

class ParserTestAnalyser extends Analyser {

	private $part;

	public function parse($s) {

		$this->part = $s;

		$this->init();

		$this->parser->parse();

		$this->destruct();
	}

	protected function initReader() {
		$this->reader = new Reader();

		$stream = fopen("php://temp", 'r+');
		fputs($stream, $this->part);
		rewind($stream);

		$this->reader->setHandle($stream);
	}

	private function destruct() {
		fclose($this->reader->getHandle());
	}

	/**
	 *
	 * @return LogAggregator
	 */
	public function getList() {
		return $this->list;
	}

}

class ParserTest extends PGLANTestCase {

	/**
	 *
	 * @var ParserTestAnalyser
	 */
	private $analyser;

	protected function setUp() {
		$this->analyser = new ParserTestAnalyser(null);
	}

	public function testSkipParse() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 7.652 ms  parse pdo_stmt_00000001: SELECT 1";

		$this->analyser->parse($log);

		$this->assertTrue($this->analyser->getList()->isEmpty());
	}

	public function testSkipBind() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.164 ms  bind pdo_stmt_00000001: SELECT $1
2012-07-11 20:42:32 EST u d DETAIL:  parameters: $1 = '100002'";

		$this->analyser->parse($log);

		$this->assertTrue($this->analyser->getList()->isEmpty());
	}

	public function testSkipDeallocate() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.137 ms  statement: DEALLOCATE pdo_stmt_00000001";

		$this->analyser->parse($log);

		$this->assertTrue($this->analyser->getList()->isEmpty());
	}

	/**
	 *
	 * @return LogEntry
	 */
	private function extractOneEntry($log, $expectedEventCount = 1, $entryType = 'Query', $expectedClass = 'SQLLogEntry') {
		$this->analyser->parse($log);

		$list = $this->analyser->getList()->getEntries($entryType);

		$this->assertCount(1, $list);
		reset($list);
		/* @var $logObject LogObject */
		$logObject = current($list);

		$this->assertCount($expectedEventCount, $logObject->getEvents());

		$logEntry = $logObject->getEntry();

		$this->assertInstanceOf($expectedClass, $logEntry);

		return $logEntry;
	}

	public function testQuery() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT X FROM Y";

		$logEntry = $this->extractOneEntry($log);

		$this->assertEquals(strtotime('2012-07-11 20:42:32 EST'), $logEntry->getDatetime());
		$this->assertEquals(0.295, $logEntry->getDuration());
		$this->assertEquals('SELECT X FROM Y', $logEntry->getText());
		$this->assertCount(0, $logEntry->getParams());
	}

	public function testMultiLineQuery() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT X
FROM Y";

		$logEntry = $this->extractOneEntry($log);

		$this->assertInstanceOf('SQLLogEntry', $logEntry);

		$this->assertEquals("SELECT X\nFROM Y", $logEntry->getText());
	}

	private function extractParams($log) {
		$logEntry = $this->extractOneEntry($log);

		$this->assertInstanceOf('SQLLogEntry', $logEntry);

		return $logEntry->getParams();
	}

	public function testQueryWithBindVar() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT $1
2012-07-11 20:42:32 EST u d DETAIL:  parameters: $1 = '100002'";

		$params = $this->extractParams($log);

		$this->assertCount(1, $params);

		$this->assertArrayHasKeyWithValue('$1', '100002', $params);
	}

	public function testNormaliseNumber() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT 1";

		$params = $this->extractParams($log);

		$this->assertCount(1, $params);
		$this->assertContains('1', $params);
	}

	public function testNormaliseString() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT 'a'";

		$params = $this->extractParams($log);

		$this->assertCount(1, $params);
		$this->assertContains("'a'", $params);
	}

	public function testAggregateQueries() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT 'a'
2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000002: SELECT 'b'";

		$this->extractOneEntry($log, 2);
	}

	public function testSystemMessageDatabaseSystem() {
		$log = "2012-07-11 20:42:27 EST u d FATAL:  the database system is starting up";

		$this->extractOneEntry($log, 1, 'System', 'SystemLogEntry');
	}

	public function testSystemMessageAutovacuum() {
		$log = "2012-07-11 20:42:27 EST   LOG:  autovacuum launcher started";

		$this->extractOneEntry($log, 1, 'System', 'SystemLogEntry');
	}

}
