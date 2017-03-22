<?php

class FileReaderTest extends PGLANTestCase {

	/**
	 * @var FileReader
	 */
	private $reader;

	/**
	 * @dataProvider getExtensions
	 */
	public function testRemoveExtensions($ext) {
		$this->reader = new FileReader('test.log'.$ext);

		$this->assertEquals('test.log', $this->reader->getOutputName());
	}

	public function getExtensions() {
		return array(
			array(''),
			array('.bz2'),
			array('.gz'),
		);
	}

}
