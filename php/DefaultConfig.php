<?php

class DefaultConfig {

	public $DataPath;

	public $MaxParamSize = 10240; // 10 kb

	public function init() {
		if (empty($this->DataPath)) {
			$this->DataPath = __DIR__.'/../data/';
		}
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
