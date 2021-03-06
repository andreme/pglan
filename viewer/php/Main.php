<?php

class Main {

	private $dataFiles = array();

	/**
	 *
	 * @var DefaultConfig
	 */
	private $config;

	public function __construct($config) {
		$this->config = $config;
	}

	public function run() {

		$this->prepareRun();

		if (isset($_REQUEST['loadfile'])) {
			$filename = $_REQUEST['loadfile'].'.json';
			if (isset($this->dataFiles[$filename])) {
				readfile($this->config->DataPath.$filename);
				die();
			} else {
				throw new Exception('File not found');
			}
		}

		require_once __DIR__.'/index.php';
	}

	private function prepareRun() {

		$this->retrieveFileList();
	}

	private function retrieveFileList() {
		foreach (glob($this->config->DataPath.'*.json') as $filename) {
			$this->dataFiles[basename($filename)] = array('FileName' => basename(preg_replace('/\.json$/i', null, $filename)), 'FileSize' => filesize($filename));
		}
	}

}
