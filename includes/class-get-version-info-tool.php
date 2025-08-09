<?php
/**
 * Get Version Info Tool.
 */
declare(strict_types=1);

namespace WPMCPBoilerplate;

// include the base tool.
require_once WP_MCP_BOILERPLATE_PATH . 'includes/class-base-tool.php';

// include the base tool.
use WPMCPBoilerplate\BaseTool;

/**
 * Get Version Info Tool.
 */
class GetVersionInfoTool extends BaseTool { // phpcs:ignore

	/**
	 * Set the name of the tool.
	 *
	 * @return string The name of the tool.
	 */
	protected function set_name(): string {
		return 'wmb_get_version_info';
	}

	/**
	 * Set the description of the tool.
	 *
	 * @return string The description of the tool.
	 */
	protected function set_description(): string {
		return 'Get WordPress version and PHP version used on the site.';
	}

	/**
	 * Set the type of the tool.
	 *
	 * @return string The type of the tool.
	 */
	protected function set_type(): string {
		return 'read';
	}

	/**
	 * Set the input schema of the tool.
	 *
	 * @return array The input schema of the tool.
	 */
	protected function set_input_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'no_parameters' => array(
					'type'        => 'string',
					'description' => 'No parameters',
				),
			),
		);
	}

	/**
	 * Set the annotations of the tool.
	 *
	 * @return array The annotations of the tool.
	 */
	protected function set_annotations(): array {
		return array(
			'title'         => 'Get Version Info',
			'readOnlyHint'  => true,
			'openWorldHint' => false,
		);
	}

	/**
	 * Execute the tool.
	 *
	 * @param array $args The arguments of the tool.
	 * @return array The result of the tool.
	 */
	public function execute( array $args ): array {
		global $wp_version;

		return array(
			'wordpress_version' => $wp_version,
			'php_version'       => phpversion(),
		);
	}

	/**
	 * Set the permission to access the tool.
	 *
	 * @return bool The permission of the tool.
	 */
	public function permission(): bool {
		return true;
	}
}
