<?php

class CSVLogParser extends LogParser {

	public function parse(Reader $reader) {

		while (!$reader->isEof()) {
			$ignored = false;
			$line = $reader->getLine();

			$logLine = new LogLine(null);
			$logLine->addPart(new LogTimePart($line[0]));

			switch ($line[7]) {
				case 'SELECT':
				case 'COPY':

					$logLine->setRemainder($line[13]);

					$logLine->addPart(new LogLevelPart($line[11]));


					$duration = new DurationParser();
					$duration->parse($logLine);

					$temp = new TemporaryFileParser();

					$query = new QueryParser();
					if ($query->parse($logLine)) {

						if ($line[14]) {
							$logLine->setRemainder($line[14]);
							$params = new ParametersParser(10240);
							$params->parse($logLine);

							$logLine->getEntry()->setParams($logLine->getLastPart()->getParams());
						}

						$this->entries->addEntry($logLine->getEntry());
					} elseif ($temp->canParse($logLine)) {
						$temp->parse($logLine);
						$logLine->getEntry()->setText($line[19]);
						$this->entries->addEntry($logLine->getEntry());
					} else {
						$ignored = true;
					}
					break;
				case 'BIND':
				case 'PARSE':
					// don't show
					break;
				default:
					$ignored = true;
			}

			if ($ignored) {
				echo implode(',', $line), "\n";
			}

			$reader->nextLine();
		}
	}

}
