<?php

namespace daarso\includes\tracing;

interface TraceWriter
{
	public function write(string $message, ...$args): void;
}
