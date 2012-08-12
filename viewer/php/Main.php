<?php

class Main {

	private $dataFiles = array();

	public function run() {

		$this->prepareRun();

		if (isset($_REQUEST['loadfile'])) {
			if (isset($this->dataFiles[$_REQUEST['loadfile']])) {
				readfile(__DIR__.'/../../data/'.$_REQUEST['loadfile']);
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
		foreach (glob(__DIR__.'/../../data/*.json') as $filename) {
			$this->dataFiles[basename($filename)] = array('FileName' => basename($filename), 'FileSize' => filesize($filename));
		}
	}

}
