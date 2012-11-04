<?php

class CheckpointParserTest extends PGLANTestCase {

	const LINE = 'checkpoint complete: wrote 1563 buffers (0.4%); 0 transaction log file(s) added, 0 removed, 0 recycled; write=269.905 s, sync=0.003 s, total=269.909 s; sync files=45, longest=0.000 s, average=0.000 s';
	const LINE_CHECKPOINT_START = 'checkpoint starting: time';

	/**
	 * @var CheckpointParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new CheckpointParser();
	}

	private function setupLine($line = '') {
		$line = new LogLine($line);

		$levelPart = new LogLevelPart('LOG');

		$line->addPart($levelPart);

		return $line;
	}

	public function testIgnoreStart() {

		$line = $this->setupLine(self::LINE_CHECKPOINT_START);

		$this->parser->parse($line);

		$this->assertTrue($line->getIgnoreEntry());
	}

	public function testParse() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Checkpoint');
		$this->assertInstanceOf('CheckpointPart', $part);
	}

	public function testParseBuffersWritten() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Checkpoint');

		$this->assertEquals('1563', $part->getBuffersWritten());
	}

	public function testParseBuffersWrittenPercentage() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Checkpoint');

		$this->assertEquals(0.4, $part->getBuffersPercentage());
	}

	public function testParseWriteTime() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Checkpoint');

		$this->assertEquals(269.905, $part->getWriteTime());
	}

	public function testParseSyncTime() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Checkpoint');

		$this->assertEquals(0.003, $part->getSyncTime());
	}

	public function testParseTotalTime() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('Checkpoint');

		$this->assertEquals(269.909, $part->getTotalTime());
	}

}
