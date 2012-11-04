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
	 * @var LogLineParsers
	 */
	protected $parsers;

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

		$this->parser->parse($this->reader);

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

		$this->initParsers();

		$this->initParser();

		$this->initWriter();
	}

	protected function initConfig() {
		require_once __DIR__.'/../../php/DefaultConfig.php';

		$this->config = DefaultConfig::create();
	}

	protected function initAutoload() {
		spl_autoload_register(function ($class) {

			$file = __DIR__;
			if (file_exists("$file/lineparsers/$class.php")) {
				$file .= "/lineparsers/$class.php";
			} elseif (file_exists("$file/lineparts/$class.php")) {
				$file .= "/lineparts/$class.php";
			} else {
				$file .= "/$class.php";
			}

			require_once $file;
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

	protected function initParsers() {
		$this->parsers = new LogLineParsers();

		$this->parsers->addParser(new NoMatchParser());
		$this->parsers->addParser(new SystemMessageParser());
		$this->parsers->addParser(new CheckpointParser());
		$this->parsers->addParser(new ParametersParser($this->config->MaxParamSize));
		$this->parsers->addParser(new QueryParser());
		$this->parsers->addParser(new DurationParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());
	}

	protected function initParser() {
		if (SysLogParser::isSysLog($this->reader->getLine())) {
			$this->parsers->addParser(new SysLogParser());
		}

		$this->parser = new LogParser($this->parsers, $this->list);
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
