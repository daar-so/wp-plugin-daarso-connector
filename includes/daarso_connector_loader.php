<?php

namespace daarso\includes;

use Throwable;
use WP_CLI;

class daarso_connector_loader {

	protected array $actions  = [];
	protected array $commands = [];
	protected array $filters  = [];

	public function add_action(
		string $hook,
		object|string $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	public function add_filter( string $hook, object|string $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	public function add_command( string $name, object|string $component, string $callback, array $arguments = [] ): void {
		$this->commands[] = [
			'name'      => $name,
			'component' => $component,
			'callback'  => $callback,
			'arguments' => $arguments,
		];
	}

	private function add( array $hooks, string $hook, object|string $component, string $callback, int $priority, int $accepted_args ): array {

		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];

		return $hooks;

	}

	/**
	 * @throws Throwable
	 */
	public function run(): void {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [
				$hook['component'],
				$hook['callback'],
			],          $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [
				$hook['component'],
				$hook['callback'],
			],          $hook['priority'], $hook['accepted_args'] );
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			foreach ( $this->commands as $command ) {
				WP_CLI::add_command(
					$command['name'],
					[ $command['component'], $command['callback'] ],
					$command['arguments'],
				);
			}
		}
	}

}
