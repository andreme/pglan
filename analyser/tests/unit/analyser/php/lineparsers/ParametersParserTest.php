<?php

class ParametersParserTest extends PGLANTestCase {

	const PARAMETER = "parameters: $1 = 'X'";
	const PARAMETER_NUMBER = "parameters: $1 = '100002'";
	const PARAMETER_MULTI = "parameters: $1 = 'A', $2 = 'B'";
	const PARAMETER_LONG = "parameters: $1 = '1234567890A'";

	const MAX_PARAM_SIZE = 10;

	/**
	 * @var ParametersParser
	 */
	private $parser;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->parser = new ParametersParser(self::MAX_PARAM_SIZE);
	}

	private function setupLine($line) {
		$line = new LogLine($line);

		$logLevelPart = new LogLevelPart('DETAIL');

		$line->addPart($logLevelPart);

		return $line;
	}

	public function testParseParameter() {

		$line = $this->setupLine(self::PARAMETER);

		$this->parser->parse($line);

		$part = $line->getPart('Parameters');
		$this->assertInstanceOf('ParametersPart', $part);

		$this->assertCount(1, $params = $part->getParams());

		$this->assertArrayHasKeyWithValue('$1', "'X'", $params);
	}

	public function testParseMultipleParameters() {

		$line = $this->setupLine(self::PARAMETER_MULTI);

		$this->parser->parse($line);

		$part = $line->getPart('Parameters');

		$this->assertCount(2, $params = $part->getParams());

		$this->assertArrayHasKeyWithValue('$1', "'A'", $params);
		$this->assertArrayHasKeyWithValue('$2', "'B'", $params);
	}

	public function testParseStripQuotesForNumbers() {

		$line = $this->setupLine(self::PARAMETER_NUMBER);

		$this->parser->parse($line);

		$part = $line->getPart('Parameters');

		$params = $part->getParams();

		$this->assertArrayHasKeyWithValue('$1', "100002", $params);
	}

	public function testLongParametersAreReplaced() {

		$line = $this->setupLine(self::PARAMETER_LONG);

		$this->parser->parse($line);

		$part = $line->getPart('Parameters');

		$params = $part->getParams();

		$this->assertArrayHasKey('$1', $params);

		$this->assertNotContains('1234567890A', $params['$1']);
	}

}
