<?php

class Analyser {

	private $filenames;

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

	private $outputName;

	public function __construct($filenames) {
		$this->filenames = $filenames;
	}

	public function analyse() {
		$this->init();

		$this->doAnalyse();

		$this->end();
	}

	protected function doAnalyse() {
		$this->startTime = new DateTime();

		foreach ($this->filenames as $filename) {

			$this->initReader($filename);

			$this->initParser($filename);

			if (!$this->outputName) {
				$this->outputName = $this->reader->getOutputName();
			}

			$this->parser->parse($this->reader);
		}

		$this->initWriter();

		$this->writer->write();

		$this->endTime = new DateTime();
	}

	protected function init() {

		require_once __DIR__.'/../../php/helper.php';

		$this->initConfig();

		$this->initAutoload();

		$this->initLogger();

		$this->initList();

		$this->initParsers();
	}

	protected function initConfig() {
		require_once __DIR__.'/../../php/DefaultConfig.php';

		$this->config = DefaultConfig::create();
	}

	protected function initAutoload() {
		require_once __DIR__.'/../../vendor/autoload.php';
		
		spl_autoload_register(function ($class) {

			$file = __DIR__;
			if (file_exists("$file/lineparsers/$class.php")) {
				$file .= "/lineparsers/$class.php";
			} elseif (file_exists("$file/lineparts/$class.php")) {
				$file .= "/lineparts/$class.php";
			} else {
				$file .= "/$class.php";
			}

			if (!file_exists($file)) {
				return false;
			}
			
			require_once $file;
			return true;
		});
	}

	protected function initReader($filename) {
		if ($this->isCSV($filename)) {
			$this->reader = new CSVFileReader($filename);
			$this->reader->init();
		} else {
			$this->reader = new FileReader($filename);
			$this->reader->init();

			while (($this->reader->getLine() === '') and !$this->reader->isEof()) {
				$this->reader->nextLine();
			}

			$this->parsers->removeParser('SysLogParser');
			if (SysLogParser::isSysLog($this->reader->getLine())) {
				$this->parsers->addParser(new SysLogParser());
			}
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
		$this->parsers->addParser(new TemporaryFileParser());
		$this->parsers->addParser(new DurationParser());
		$this->parsers->addParser(new EntryStartParser());
		$this->parsers->addParser(new BeginOfLineParser());
	}

	private function isCSV($filename) {
		return preg_match('/\.csv/i', $filename);
	}

	protected function initParser($filename = null) {

		if ($this->isCSV($filename)) {
			$this->parser = new CSVLogParser([], $this->list);
		} else {
			$this->parser = new LogParser($this->parsers, $this->list);
		}
	}

	protected function initWriter() {

		$filename = $this->config->DataPath.$this->outputName.'.json';

		$this->writer = new JSONWriter($this->list, $filename);
	}

	protected function end() {
		echo "\n", 'Start: ', $this->startTime->format('H:i:s'), "\n";
		echo 'End: ', $this->endTime->format('H:i:s'), "\n";
		echo "\nDone\n";
	}

}
