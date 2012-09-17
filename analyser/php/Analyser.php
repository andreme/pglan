<?php

class Analyser {

	private $filename;

	/**
	 *
	 * @var DefaultConfig
	 */
	protected $config;

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
	 * @var Parser
	 */
	protected $parser;

	/**
	 *
	 * @var Writer
	 */
	protected $writer;

	private $startTime;
	private $endTime;

	public function __construct($filename) {
		$this->filename = $filename;
	}

	public function analyse() {
		$this->init();

		$this->doAnalyse();

		$this->end();
	}

	protected function doAnalyse() {
		$this->startTime = new DateTime();

		$this->parser->parse();

		$this->writer->write();

		$this->endTime = new DateTime();
	}

	protected function init() {

		require_once __DIR__.'/../../php/helper.php';

		$this->initConfig();

		$this->initAutoload();

		$this->initLogger();

		$this->initReader();

		$this->initList();

		$this->initParser();

		$this->initWriter();
	}

	protected function initConfig() {
		require_once __DIR__.'/../../php/DefaultConfig.php';

		$this->config = DefaultConfig::create();
	}

	protected function initAutoload() {
		spl_autoload_register(function ($class) {
			require_once __DIR__."/$class.php";
			return true;
		});
	}

	protected function initReader() {
		$this->reader = new FileReader($this->filename);
		$this->reader->init();

		while (($this->reader->getLine() === '') and !$this->reader->isEof()) {
			$this->reader->nextLine();
		}
	}

	protected function initLogger() {
		$this->logger = new Logger(null);
	}

	protected function initList() {
		$this->list = new LogAggregator();
	}

	protected function initParser() {
		if (PgSysLogParser::isSysLog($this->reader->getLine())) {
			$this->parser = new PgSysLogParser($this->reader, $this->logger, $this->list);
		} else {
			$this->parser = new PgLogParser($this->reader, $this->logger, $this->list);
		}
	}

	protected function initWriter() {
		$this->writer = new JSONWriter($this->list, $this->config->DataPath.basename($this->filename).'.json');
	}

	protected function end() {
		echo "\n", 'Start: ', $this->startTime->format('H:i:s'), "\n";
		echo 'End: ', $this->endTime->format('H:i:s'), "\n";
		echo "\nDone\n";
	}

}
