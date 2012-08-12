<?php

class JSONWriter extends Writer {

	private $filename;

	public function __construct($list, $filename) {
		parent::__construct($list);

		$this->filename = $filename;
	}

	protected function doWrite() {
		$handle = fopen($this->filename, 'w');

		fwrite($handle, '{"log": [');

		$i = 0;
//		fwrite($handle, json_encode($this->list->getEntries()));
		/* @var $entry LogObject */
		foreach ($this->list->getEntries() as $entry) {
			fwrite($handle, ($i++ > 0 ? ',' : '').json_encode($entry->jsonSerialize()));
		}

		fwrite($handle, ']}');
		fclose($handle);
	}

}
