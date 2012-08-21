<?php

class DefaultConfig {

	public $DataPath;
	
	public function init() {
		$this->DataPath = __DIR__.'/../data/';
	}

	public static function create() {
		$configFile = __DIR__.'/../config.php';
		if (file_exists($configFile)) {
			require_once $configFile;
		} else {
			require_once __DIR__.'/../config.dist.php';
		}

		$config = new Config();
		$config->init();
		return $config;
	}

}
