<?php

namespace daarso\includes\tracing;

class DefaultFileWriter  implements TraceWriter {
	private string $logFile;

	public function __construct() {
		$this->logFile = DAARSO_CONNECTOR_ROOT . 'daarso-trace.log';
	}

	public function write(string $message, ...$args): void {
		$timestamp = date('Y-m-d H:i:s');
		$formattedMessage = sprintf("[%s] %s\n", $timestamp, sprintf($message, ...$args));

		file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
	}
}
