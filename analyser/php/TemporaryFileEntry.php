<?php

class TemporaryFileEntry extends LogEntry {

	private $file;
	private $size;
	
	public function getType() {
		return 'Temp';
	}

	public function __construct($datetime, $user, $db, $level, $text, $file, $size) {
		parent::__construct($datetime, $user, $db, $level, $text, null);
		
		$this->file = $file;
		$this->size = $size;
	}

	public function getExportData() {
		$result = parent::getExportData();

		$result['File'] = $this->file;

		return $result;
	}

	protected function calcHash() {
		$this->hash = md5($this->file."#".$this->db.'#'.$this->user.'#'.$this->level);
	}

	public function getEventData() {
		$event = parent::getEventData();
		
		unset($event['Duration']);
		
		$event['Size'] = $this->size;
		
		return $event;
	}

}
