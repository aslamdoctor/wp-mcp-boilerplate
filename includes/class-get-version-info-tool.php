<?php
/**
 * Get Version Info Tool.
 */
declare(strict_types=1);

namespace MyMCPTools;

// include the base tool.
include_once MY_PLUGIN_PATH . 'includes/class-base-tool.php';

// include the base tool.
use MyMCPTools\BaseTool;

/**
 * Get Version Info Tool.
 */
class GetVersionInfoTool extends BaseTool {

	/**
	 * Get the name of the tool.
	 *
	 * @return string The name of the tool.
	 */
	protected function get_name(): string {
		return 'mmt_get_version_info';
	}

	/**
	 * Get the description of the tool.
	 *
	 * @return string The description of the tool.
	 */
	protected function get_description(): string {
		return 'Get WordPress version and PHP version used on the site.';
	}

	/**
	 * Get the type of the tool.
	 *
	 * @return string The type of the tool.
	 */
	protected function get_type(): string {
		return 'read';
	}

	/**
	 * Get the input schema of the tool.
	 *
	 * @return array The input schema of the tool.
	 */
	protected function get_input_schema(): array {
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
	 * Get the annotations of the tool.
	 *
	 * @return array The annotations of the tool.
	 */
	protected function get_annotations(): array {
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
}
