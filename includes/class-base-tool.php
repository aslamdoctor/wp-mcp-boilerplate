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
				'name'                => $this->set_name(),
				'description'         => $this->set_description(),
				'type'                => $this->set_type(),
				'inputSchema'         => $this->set_input_schema(),
				'callback'            => array( $this, 'execute' ),
				'permission_callback' => array( $this, 'permission' ),
				'annotations'         => $this->set_annotations(),
			)
		);
	}

	/**
	 * Set the name of the tool.
	 *
	 * @return string The name of the tool.
	 */
	abstract protected function set_name(): string;

	/**
	 * Set the description of the tool.
	 *
	 * @return string The description of the tool.
	 */
	abstract protected function set_description(): string;

	/**
	 * Set the type of the tool.
	 *
	 * @return string The type of the tool.
	 */
	abstract protected function set_type(): string;

	/**
	 * Set the input schema of the tool.
	 *
	 * @return array The input schema of the tool.
	 */
	abstract protected function set_input_schema(): array;

	/**
	 * Set the annotations of the tool.
	 *
	 * @return array The annotations of the tool.
	 */
	abstract protected function set_annotations(): array;

	/**
	 * Execute the tool.
	 *
	 * @param array $args The arguments of the tool.
	 * @return array The result of the tool.
	 */
	abstract public function execute( array $args ): array;

	/**
	 * Set the permission to access the tool.
	 *
	 * @return bool The permission of the tool.
	 */
	public function permission(): bool {
		return true;
	}
}
