<?php

class ParserTestAnalyser extends Analyser {

	private $part;

	public function parse($s) {

		$this->part = $s;

		$this->init();

		$this->initReader();

		$this->parser->parse($this->reader);

		$this->destruct();
	}

	protected function initReader() {
		$this->reader = new Reader();

		$stream = fopen("php://temp", 'r+');
		fputs($stream, $this->part);
		rewind($stream);

		$this->reader->setHandle($stream);
		$this->reader->nextLine();
	}

	protected function initWriter() {
	}

	private function destruct() {
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
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.164 ms  bind pdo_stmt_00000001: SELECT $1\n2012-07-11 20:42:32 EST u d DETAIL:  parameters: $1 = '100002'";

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

		$this->assertEquals(strtotime('2012-07-11 20:42:32 UTC'), $logEntry->getDatetime());
		$this->assertEquals(0.295, $logEntry->getDuration());
		$this->assertEquals('SELECT X FROM Y', $logEntry->getText());
		$this->assertCount(0, $logEntry->getParams());
	}

	public function testQueryWithLogLevelStatement() {
		$log = "2016-12-30 13:25:18 EST u d STATEMENT:  SELECT a";

		$logEntry = $this->extractOneEntry($log);

		$this->assertEquals(strtotime('2016-12-30 13:25:18 UTC'), $logEntry->getDatetime());
		$this->assertEquals('SELECT a', $logEntry->getText());
		$this->assertCount(0, $logEntry->getParams());
	}

	public function testMultiLineQuery() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT X\nFROM Y";

		$logEntry = $this->extractOneEntry($log);

		$this->assertEquals("SELECT X\nFROM Y", $logEntry->getText());
	}

// TODO link temp file to statement...
//	public function testTemporaryFile() {
//		$log = "2016-12-30 13:25:18 EST postgres palletwatch LOG:  temporary file: path \"base/pgsql_tmp/pgsql_tmp11684.0\", size 74366976
//2016-12-30 13:25:18 EST u db STATEMENT:  CREATE temp table tbl as SELECT * FROM z(424)\nWHERE (((Lower(concat( r1, r_2, r3)) LIKE Lower('%34443%'))))";
//
//		$logEntry = $this->extractOneEntry($log, 1, 'Temp', 'TemporaryFileEntry');
//
//		$this->assertEquals("CREATE temp table tbl as SELECT * FROM z(424)\nWHERE (((Lower(concat( r1, r_2, r3)) LIKE Lower('%34443%'))))", $logEntry->getText());
//	}

	private function extractParams($log) {
		$logEntry = $this->extractOneEntry($log);

		return $logEntry->getParams();
	}

	public function testQueryWithBindVar() {
		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT $1
2012-07-11 20:42:32 EST u d DETAIL:  parameters: $1 = '100002'";

		$params = $this->extractParams($log);

		$this->assertCount(1, $params);

		$this->assertArrayHasKeyWithValue('$1', '100002', $params);
	}

	public function testQueryWithDurationLoggedOnSecondLine() {
		$log = "2016-12-08 22:34:54 AWST u d LOG:  statement: SELECT * from public.x(45)
2016-12-08 22:34:56 AWST u d LOG:  duration: 2064.956 ms
";

		$logEntry = $this->extractOneEntry($log);
		$this->assertEquals(2064.956, $logEntry->getDuration());
	}

// TODO check if this works, duration after temp file...	
//	2016-12-09 15:21:53 AEDT postgres palletwatch LOG:  statement: CREATE temp table tbl as SELECT * FROM docket_adv_data(7441) WHERE (((Lower(CONCAT(docket_no, org_docket)) LIKE Lower('%123%'))) AND ((Lower(docket_type_desc) LIKE Lower('%Dehire%'))))
//2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  temporary file: path "base/pgsql_tmp/pgsql_tmp10516.0", size 68976640
//2016-12-09 15:21:56 AEDT postgres palletwatch STATEMENT:  CREATE temp table tbl as SELECT * FROM docket_adv_data(7441) WHERE (((Lower(CONCAT(docket_no, org_docket)) LIKE Lower('%123%'))) AND ((Lower(docket_type_desc) LIKE Lower('%Dehire%'))))
//2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  duration: 2851.000 ms
//2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  statement: SELECT COUNT(*) FROM tbl
//2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  duration: 1.000 ms
//2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  statement: SELECT docket_header_id, docket_no, hire_company_name, docket_type_id, docket_type_desc, movement_date, despatch_date, received_date, receipt_date, raised_by, date_raised, reference_1, reference_2, reference_3, equipment, effective_date, quantity, site_name, tp_account_no, tp_name, tp_details, tp_site_name, used_site_name, account_id, cancelled, invoice_date, export_date, status, status_icon, status_desc, last_alteration, active, (docket_notes_table(docket_header_id)).*, org_docket, link_reference, batch_no, pallet_qty, pod_received FROM( SELECT * FROM tbl ORDER BY date_raised DESC
//	 LIMIT 25 OFFSET 0) tbldata;

	
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

	public function testSystemMessageWALMissing() {
		$log = "2016-12-30 13:40:44 EST postgres [unknown] ERROR:  requested WAL segment 00000001000000850000001A has already been removed";

		$this->extractOneEntry($log, 1, 'System', 'SystemLogEntry');
	}

}
