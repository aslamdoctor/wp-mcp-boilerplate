<?php
declare(strict_types=1);

namespace MyMCPTools;

class MCPTools {

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
				'name'                => 'mmt_get_version_info',
				'description'         => 'Get WordPress version and PHP version used on the site.',
				'type'                => 'read',
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'no_parameters' => array(
							'type'        => 'string',
							'description' => 'No parameters',
						),
					),
				),
				'callback'            => array( $this, 'get_version_info' ),
				'permission_callback' => array( $this, 'permissions_to_get_version_info' ),
				'annotations'         => array(
					'title'         => 'Get Version Info',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);
	}

	/**
	 * Get version info.
	 *
	 * @param array $args Tool arguments (unused for this tool).
	 * @return array Tool execution result with version information.
	 */
	public function get_version_info( array $args ): array {
		global $wp_version;

		return array(
			'wordpress_version' => $wp_version,
			'php_version'       => phpversion(),
		);
	}

	/**
	 * Permissions to get version info.
	 *
	 * @return bool Whether the current user can use this tool.
	 */
	public function permissions_to_get_version_info(): bool {
		// Allow any user to check WordPress version (read-only operation).
		return true;
	}
}
