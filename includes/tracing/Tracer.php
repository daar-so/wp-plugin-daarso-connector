<?php

namespace daarso\includes\tracing;

class Tracer {
	private static ?TraceWriter $writer = null;

	public static function write(string $message, ...$args): void {
		if (self::$writer === null) {
			self::initializeWriter();
		}

		self::$writer->write($message, ...$args);
	}

	private static function initializeWriter(): void {
		/** @noinspection PhpUndefinedConstantInspection */
		if ( defined( 'DAARSO_TRACING') && DAARSO_TRACING) {
			self::$writer = new DefaultFileWriter();
		} else {
			self::$writer = new DummyWriter();
		}
	}
}
