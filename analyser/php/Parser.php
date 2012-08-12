<?php

abstract class Parser {

	/**
	 *
	 * @var Reader
	 */
	protected $reader;

	/**
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 *
	 * @var LogAggregator
	 */
	protected $list;

	/**
	 *
	 * @param Reader $reader
	 * @param Logger $logger
	 * @param LogAggregator $list
	 */
	public function __construct($reader, $logger, $list) {
		$this->reader = $reader;
		$this->logger = $logger;
		$this->list = $list;
	}

	abstract public function parse();

}
