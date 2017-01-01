<?php

class TemporaryFileParserTest extends PGLANTestCase {

	const LINE = 'temporary file: path "base/pgsql_tmp/pgsql_tmp11684.0", size 74366976';

	/**
	 * @var TemporaryFileParser
	 */
	private $parser;

	protected function setUp() {
		$this->parser = new TemporaryFileParser();
	}

	private function setupLine($line = '') {
		$line = new LogLine($line);

		return $line;
	}

	public function testParseLine() {

		$line = $this->setupLine(self::LINE);

		$this->parser->parse($line);

		$part = $line->getPart('TemporaryFile');

		$this->assertInstanceOf(TemporaryFilePart::class, $part);
		
		$this->assertEquals('base/pgsql_tmp/pgsql_tmp11684', $part->getFile());
		$this->assertEquals(74366976, $part->getSize());
	}

}
