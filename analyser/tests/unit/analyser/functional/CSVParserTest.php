<?php

class CSVParserTestAnalyser extends Analyser {

	private $part;

	public function parse($s) {

		$this->part = $s;

		$this->init();

		$this->initReader('');

		$this->initParser('.csv');

		$this->parser->parse($this->reader);

		$this->destruct();
	}

	protected function initReader($filename) {
		$file = new SplTempFileObject();
		$file->fwrite($this->part);
		$file->rewind();

		$this->reader = new CSVFileReader($file);
		$this->reader->init();
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

class CSVParserTest extends PGLANTestCase {

	/**
	 *
	 * @var CSVParserTestAnalyser
	 */
	private $analyser;

	protected function setUp() {
		$this->analyser = new CSVParserTestAnalyser(null);
	}

//	public function testSkipParse() {
//		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 7.652 ms  parse pdo_stmt_00000001: SELECT 1";
//
//		$this->analyser->parse($log);
//
//		$this->assertTrue($this->analyser->getList()->isEmpty());
//	}

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
		$log = <<<EOT
2017-03-21 01:51:35.393 GMT,"user","db",10816,"10.69.90.52:59396",58d086f7.2a40,1,"SELECT",2017-03-21 01:50:47 GMT,16/575414,0,LOG,00000,"duration: 19749.155 ms  execute a12: SELECT COUNT(*) FROM ""apps""",,,,,,,,,"app"
EOT;

		$logEntry = $this->extractOneEntry($log);

		$this->assertEquals(strtotime('2017-03-21 01:51:35 UTC'), $logEntry->getDatetime());
		$this->assertEquals(19749.155, $logEntry->getDuration());
		$this->assertEquals('SELECT COUNT(*) FROM "apps"', $logEntry->getText());
		$this->assertCount(0, $logEntry->getParams());
	}

	public function testCopy() {
		$log = <<<EOT
2017-03-21 01:51:35.393 GMT,"user","db",10816,"10.69.90.52:59396",58d086f7.2a40,1,"COPY",2017-03-21 01:50:47 GMT,16/575414,0,LOG,00000,"duration: 2296.221 ms  statement: COPY tab FROM STDIN WITH CSV HEADER",,,,,,,,,"app"
EOT;

		$logEntry = $this->extractOneEntry($log);

		$this->assertEquals(strtotime('2017-03-21 01:51:35 UTC'), $logEntry->getDatetime());
		$this->assertEquals(2296.221, $logEntry->getDuration());
		$this->assertEquals('COPY tab FROM STDIN WITH CSV HEADER', $logEntry->getText());
		$this->assertCount(0, $logEntry->getParams());
	}

	public function testMultiLineQuery() {
		$log = <<<EOT
2017-03-21 01:51:35.393 GMT,"user","db",10816,"10.69.90.52:59396",58d086f7.2a40,1,"SELECT",2017-03-21 01:50:47 GMT,16/575414,0,LOG,00000,"duration: 19749.155 ms  execute a12: SELECT COUNT(*)
FROM ""apps""",,,,,,,,,"app"
EOT;

		$logEntry = $this->extractOneEntry($log);

		$this->assertEquals("SELECT COUNT(*)\nFROM \"apps\"", $logEntry->getText());
	}

	public function testTemporaryFile() {
		$log = <<<EOT
2017-03-22 02:13:57.152 GMT,"user","db",732,"10.69.90.52:21996",58d1d42c.2dc,13,"SELECT",2017-03-22 01:32:28 GMT,46/629813,0,LOG,00000,"temporary file: path ""base/pgsql_tmp/pgsql_tmp732.12"", size 17721624",,,,,,"SELECT 1",,,""
EOT;

		$logEntry = $this->extractOneEntry($log, 1, 'Temp', 'TemporaryFileEntry');

		$this->assertEquals("SELECT 1", $logEntry->getText());
	}

	private function extractParams($log) {
		$logEntry = $this->extractOneEntry($log);

		return $logEntry->getParams();
	}

	public function testQueryWithBindVar() {
		$log = <<<EOT
2017-03-21 01:51:35.393 GMT,"user","db",10816,"10.69.90.52:59396",58d086f7.2a40,1,"SELECT",2017-03-21 01:50:47 GMT,16/575414,0,LOG,00000,"duration: 19749.155 ms  execute a12: SELECT COUNT(*) FROM ""apps"" WHERE z = $1","parameters: $1 = 'Active'",,,,,,,,"app"
EOT;

		$params = $this->extractParams($log);

		$this->assertCount(1, $params);

		$this->assertArrayHasKeyWithValue('$1', '\'Active\'', $params);
	}

//// TODO check if this works, duration after temp file...
////	2016-12-09 15:21:53 AEDT postgres palletwatch LOG:  statement: CREATE temp table tbl as SELECT * FROM docket_adv_data(7441) WHERE (((Lower(CONCAT(docket_no, org_docket)) LIKE Lower('%123%'))) AND ((Lower(docket_type_desc) LIKE Lower('%Dehire%'))))
////2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  temporary file: path "base/pgsql_tmp/pgsql_tmp10516.0", size 68976640
////2016-12-09 15:21:56 AEDT postgres palletwatch STATEMENT:  CREATE temp table tbl as SELECT * FROM docket_adv_data(7441) WHERE (((Lower(CONCAT(docket_no, org_docket)) LIKE Lower('%123%'))) AND ((Lower(docket_type_desc) LIKE Lower('%Dehire%'))))
////2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  duration: 2851.000 ms
////2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  statement: SELECT COUNT(*) FROM tbl
////2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  duration: 1.000 ms
////2016-12-09 15:21:56 AEDT postgres palletwatch LOG:  statement: SELECT docket_header_id, docket_no, hire_company_name, docket_type_id, docket_type_desc, movement_date, despatch_date, received_date, receipt_date, raised_by, date_raised, reference_1, reference_2, reference_3, equipment, effective_date, quantity, site_name, tp_account_no, tp_name, tp_details, tp_site_name, used_site_name, account_id, cancelled, invoice_date, export_date, status, status_icon, status_desc, last_alteration, active, (docket_notes_table(docket_header_id)).*, org_docket, link_reference, batch_no, pallet_qty, pod_received FROM( SELECT * FROM tbl ORDER BY date_raised DESC
////	 LIMIT 25 OFFSET 0) tbldata;
//
//
//	public function testNormaliseNumber() {
//		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT 1";
//
//		$params = $this->extractParams($log);
//
//		$this->assertCount(1, $params);
//		$this->assertContains('1', $params);
//	}
//
//	public function testNormaliseString() {
//		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT 'a'";
//
//		$params = $this->extractParams($log);
//
//		$this->assertCount(1, $params);
//		$this->assertContains("'a'", $params);
//	}
//
//	public function testAggregateQueries() {
//		$log = "2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000001: SELECT 'a'
//2012-07-11 20:42:32 EST u d LOG:  duration: 0.295 ms  execute pdo_stmt_00000002: SELECT 'b'";
//
//		$this->extractOneEntry($log, 2);
//	}
//
//	public function testSystemMessageDatabaseSystem() {
//		$log = "2012-07-11 20:42:27 EST u d FATAL:  the database system is starting up";
//
//		$this->extractOneEntry($log, 1, 'System', 'SystemLogEntry');
//	}
//
//	public function testSystemMessageAutovacuum() {
//		$log = "2012-07-11 20:42:27 EST   LOG:  autovacuum launcher started";
//
//		$this->extractOneEntry($log, 1, 'System', 'SystemLogEntry');
//	}
//
//	public function testSystemMessageWALMissing() {
//		$log = "2016-12-30 13:40:44 EST postgres [unknown] ERROR:  requested WAL segment 00000001000000850000001A has already been removed";
//
//		$this->extractOneEntry($log, 1, 'System', 'SystemLogEntry');
//	}

}
