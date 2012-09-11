<?php

if (class_exists('PHPUnit_Framework_TestCase')) {

class PGLANTestCase extends PHPUnit_Framework_TestCase {

	protected function assertArrayHasKeyWithValue($expectedKey, $expectedValue, $actual) {
		$this->assertArrayHasKey($expectedKey, $actual);
		$this->assertEquals($expectedValue, $actual[$expectedKey]);
	}

}

}
