<?php

class ParametersParser extends LogLinePartParser {

	private $maxParamSize;

	public function __construct($maxParamSize) {
		$this->maxParamSize = $maxParamSize;
	}

	/**
	 *
	 * @param LogLine $logLine
	 */
	public function parse($logLine) {

		if (!(($lastPart = $logLine->getLastPart()) instanceof LogLevelPart)) {
			return false;
		}
		if ($lastPart->getLevel() != 'DETAIL') {
			return false;
		}

		$matches = null;

		$params = null;
		if (!ematch('/^parameters: (.*)/', $logLine->getRemainder(), $params)) {
			return false;
		}

		$line = $params[1];

		if (preg_match_all('/(\$[0-9]+) = (.*)(?=(?:, \$[0-9]+ = |\z))/U', $line, $params, PREG_SET_ORDER)) {
			$logLine->addPart($paramPart = new ParametersPart());

			$this->addParameters($paramPart, $params);

			$logLine->setRemainder(false);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param ParametersPart $paramPart
	 */
	private function addParameters($paramPart, $params) {
		foreach ($params as $param) {

			$paramValue = $param[2];
			if (substr($paramValue, 0, 1) == "'") {
				$trimmedValue = substr($paramValue, 1, -1);
				if (is_numeric($trimmedValue)) {
					$paramValue = $trimmedValue;
				}
			}

			if (strlen($paramValue) > $this->maxParamSize) {
				$paramValue = 'LOB replaced';
			}

			$paramPart->addParam($param[1], $paramValue);
		}
	}

}
