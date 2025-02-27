<?php

namespace daarso\includes\tracing;

class DummyWriter implements TraceWriter {
	public function write(string $message, ...$args): void {
		// Do nothing, dat is wat een dummy writer doet.
	}
}
