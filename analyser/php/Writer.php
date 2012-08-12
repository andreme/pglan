<?php

abstract class Writer {

	/**
	 *
	 * @var LogAggregator
	 */
	protected $list;

	public function __construct($list) {
		$this->list = $list;
	}

	public function write() {
		$this->doWrite();
	}

	abstract protected function doWrite();

}
