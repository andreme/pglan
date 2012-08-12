<?php

class PgSysLogParser extends PgLogParser {

	public static function isSysLog($s) {
		return preg_match('/^\w{3} [\d\s]\d \d\d:\d\d:\d\d [^\s]* [^:]*: \[\d+-\d+\] /i', $s);
	}

	protected function inspectLine() {

		$this->line = preg_replace('/^\w{3} [\d\s]\d \d\d:\d\d:\d\d [^\s]* [^:]*: \[\d+-\d+\] /i', null, $this->line);

		$this->line = str_replace(array('#011', '#015'), array("\t", null), $this->line);

		return parent::inspectLine();
	}

}
