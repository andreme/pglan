<?php

class ConnectionPart extends LogLinePart {

	private $db;
	private $user;

	public function __construct($db, $user) {
		parent::__construct();

		$this->db = $db;
		$this->user = $user;
	}

	public function getDb() {
		return $this->db;
	}

	public function getUser() {
		return $this->user;
	}

}
