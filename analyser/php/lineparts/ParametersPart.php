<?php

class ParametersPart extends LogLinePart {

	private $params = array();

	public function addParam($name, $value) {
		$this->params[$name] = $value;
	}

	public function getParams() {
		return $this->params;
	}

}
