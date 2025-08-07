<?php
/**
 * Base class for MCP tools.
 */

declare(strict_types=1);

namespace MyMCPTools;

/**
 * Base class for MCP tools.
 */
abstract class BaseTool {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wordpress_mcp_init', array( $this, 'register_tools' ) );
	}

	/**
	 * Register the tool.
	 *
	 * @return void
	 */
	public function register_tools(): void {
		WPMCP()->register_tool(
			array(
				'name'                => $this->get_name(),
				'description'         => $this->get_description(),
				'type'                => $this->get_type(),
				'inputSchema'         => $this->get_input_schema(),
				'callback'            => array( $this, 'execute' ),
				'permission_callback' => array( $this, 'permission' ),
				'annotations'         => $this->get_annotations(),
			)
		);
	}

	/**
	 * Get the name of the tool.
	 *
	 * @return string The name of the tool.
	 */
	abstract protected function get_name(): string;

	/**
	 * Get the description of the tool.
	 *
	 * @return string The description of the tool.
	 */
	abstract protected function get_description(): string;

	/**
	 * Get the type of the tool.
	 *
	 * @return string The type of the tool.
	 */
	abstract protected function get_type(): string;

	/**
	 * Get the input schema of the tool.
	 *
	 * @return array The input schema of the tool.
	 */
	abstract protected function get_input_schema(): array;

	/**
	 * Get the annotations of the tool.
	 *
	 * @return array The annotations of the tool.
	 */
	abstract protected function get_annotations(): array;

	/**
	 * Execute the tool.
	 *
	 * @param array $args The arguments of the tool.
	 * @return array The result of the tool.
	 */
	abstract public function execute( array $args ): array;

	/**
	 * Get the permission of the tool.
	 *
	 * @return bool The permission of the tool.
	 */
	public function permission(): bool {
		return true;
	}
}
