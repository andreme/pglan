<?php

class JSONWriter extends Writer {

	private $filename;

	public function __construct($list, $filename) {
		parent::__construct($list);

		$this->filename = $filename;
	}

	protected function doWrite() {
		$handle = fopen($this->filename, 'w');

		fwrite($handle, '{"log": {');

		$this->writeTypes($handle);

		fwrite($handle, '}}');

		fclose($handle);
	}

	private function writeTypes($handle) {
		$i = 0;

		foreach ($this->list->getTypes() as $type) {
			fwrite($handle, ($i++ > 0 ? ',' : '').'"'.$type.'": [');
			$this->writeEntries($handle, $type);
			fwrite($handle, ']');
		}
	}

	private function writeEntries($handle, $type) {
		$i = 0;

		/* @var $entry LogObject */
		foreach ($this->list->getEntries($type) as $entry) {
			fwrite($handle, ($i++ > 0 ? ',' : '').json_encode($entry->jsonSerialize()));
		}
	}

}
