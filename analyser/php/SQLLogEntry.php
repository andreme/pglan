<?php

class SQLLogEntry extends LogEntry {

	public function getType() {
		return 'Query';
	}

}
