<?php
/**
 * Create MCP Tool Generator.
 *
 * @package WPMCPBoilerplate
 */

declare(strict_types=1);

namespace WPMCPBoilerplate;

// include the base tool.
require_once WP_MCP_BOILERPLATE_PATH . 'includes/class-base-tool.php';

// include the base tool.
use WPMCPBoilerplate\BaseTool;

/**
 * Create MCP Tool Generator.
 */
class CreateMcpTool extends BaseTool { // phpcs:ignore

	/**
	 * Set the name of the tool.
	 *
	 * @return string The name of the tool.
	 */
	protected function set_name(): string {
		return 'wmb_create_mcp_tool';
	}

	/**
	 * Set the description of the tool.
	 *
	 * @return string The description of the tool.
	 */
	protected function set_description(): string {
		return 'Generate a new MCP tool class file based on provided specifications.';
	}

	/**
	 * Set the type of the tool.
	 *
	 * @return string The type of the tool.
	 */
	protected function set_type(): string {
		return 'create';
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
				'tool_name'        => array(
					'type'        => 'string',
					'description' => 'The name of the tool (e.g., "MyCustomTool")',
				),
				'tool_identifier'  => array(
					'type'        => 'string',
					'description' => 'The unique identifier for the tool (e.g., "wmb_my_custom_tool")',
				),
				'tool_description' => array(
					'type'        => 'string',
					'description' => 'Description of what the tool does',
				),
				'tool_type'        => array(
					'type'        => 'string',
					'description' => 'The type of tool (read, write, execute)',
					'enum'        => array( 'read', 'write', 'execute' ),
					'default'     => 'read',
				),
				'input_schema'     => array(
					'type'        => 'object',
					'description' => 'JSON schema for the tool input parameters',
					'default'     => array(
						'type'       => 'object',
						'properties' => array(
							'parameter' => array(
								'type'        => 'string',
								'description' => 'Example parameter',
							),
						),
						'required'   => array( 'parameter' ),
					),
				),
				'execute_logic'    => array(
					'type'        => 'string',
					'description' => 'PHP code for the execute method (without the method signature)',
					'default'     => "return array(\n\t\t\t'message' => 'Tool executed successfully',\n\t\t\t'data' => \$args,\n\t\t);",
				),
				'annotations'      => array(
					'type'        => 'object',
					'description' => 'Tool annotations for metadata',
					'default'     => array(
						'title'         => 'Custom Tool',
						'readOnlyHint'  => false,
						'openWorldHint' => false,
					),
				),
				'permission_logic' => array(
					'type'        => 'string',
					'description' => 'PHP code for permission check (should return boolean)',
					'default'     => 'return true;',
				),
			),
			'required'   => array( 'tool_name', 'tool_identifier', 'tool_description' ),
		);
	}

	/**
	 * Set the annotations of the tool.
	 *
	 * @return array The annotations of the tool.
	 */
	protected function set_annotations(): array {
		return array(
			'title'         => 'Create MCP Tool',
			'readOnlyHint'  => false,
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
		// Validate required parameters.
		if ( empty( $args['tool_name'] ) || empty( $args['tool_identifier'] ) || empty( $args['tool_description'] ) ) {
			return array(
				'error'   => true,
				'message' => 'Missing required parameters: tool_name, tool_identifier, and tool_description are required.',
			);
		}

		$tool_name        = sanitize_text_field( $args['tool_name'] );
		$tool_identifier  = sanitize_text_field( $args['tool_identifier'] );
		$tool_description = sanitize_textarea_field( $args['tool_description'] );
		$tool_type        = isset( $args['tool_type'] ) ? sanitize_text_field( $args['tool_type'] ) : 'read';
		$input_schema     = isset( $args['input_schema'] ) ? $args['input_schema'] : $this->get_default_input_schema();
		$execute_logic    = isset( $args['execute_logic'] ) ? $args['execute_logic'] : $this->get_default_execute_logic();
		$annotations      = isset( $args['annotations'] ) ? $args['annotations'] : $this->get_default_annotations();
		$permission_logic = isset( $args['permission_logic'] ) ? $args['permission_logic'] : 'return true;';

		// Validate tool type.
		if ( ! in_array( $tool_type, array( 'read', 'write', 'execute' ), true ) ) {
			return array(
				'error'   => true,
				'message' => 'Invalid tool_type. Must be one of: read, write, execute',
			);
		}

		// Generate class name from tool name.
		$class_name = $this->sanitize_class_name( $tool_name );
		$file_name  = 'class-' . strtolower( str_replace( '_', '-', $this->camel_to_kebab( $class_name ) ) ) . '.php';
		$file_path  = WP_MCP_BOILERPLATE_PATH . 'includes/' . $file_name;

		// Check if file already exists.
		if ( file_exists( $file_path ) ) {
			return array(
				'error'   => true,
				'message' => "Tool file already exists: {$file_name}",
			);
		}

		// Generate the tool class content.
		$class_content = $this->generate_tool_class(
			$class_name,
			$tool_identifier,
			$tool_description,
			$tool_type,
			$input_schema,
			$execute_logic,
			$annotations,
			$permission_logic
		);

		// Write the file using WordPress filesystem.
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$result = $wp_filesystem->put_contents( $file_path, $class_content, FS_CHMOD_FILE );

		if ( false === $result ) {
			return array(
				'error'   => true,
				'message' => 'Failed to create tool file. Check file permissions.',
			);
		}

		return array(
			'success'    => true,
			'message'    => 'MCP tool created successfully!',
			'class_name' => $class_name,
			'file_name'  => $file_name,
			'file_path'  => $file_path,
			'file_size'  => strlen( $class_content ),
			'next_steps' => array(
				'1. Include the new tool file in the main plugin file',
				'2. Instantiate the tool class in the init() method',
				'3. Test the tool functionality',
			),
		);
	}

	/**
	 * Generate the tool class PHP content.
	 *
	 * @param string $class_name The class name.
	 * @param string $tool_identifier The tool identifier.
	 * @param string $tool_description The tool description.
	 * @param string $tool_type The tool type.
	 * @param array  $input_schema The input schema.
	 * @param string $execute_logic The execute logic.
	 * @param array  $annotations The annotations.
	 * @param string $permission_logic The permission logic.
	 * @return string The generated class content.
	 */
	private function generate_tool_class( $class_name, $tool_identifier, $tool_description, $tool_type, $input_schema, $execute_logic, $annotations, $permission_logic ): string {
		$schema_export      = $this->array_to_string( $input_schema );
		$annotations_export = $this->array_to_string( $annotations );

		// Format the exported arrays to be more readable.
		$schema_export      = $this->format_array_export( $schema_export );
		$annotations_export = $this->format_array_export( $annotations_export );

		// Ensure execute logic is properly indented.
		$execute_logic = $this->indent_code( $execute_logic, 2 );

		// Ensure permission logic is properly indented.
		$permission_logic = $this->indent_code( $permission_logic, 2 );

		return "<?php
/**
 * {$class_name} Tool.
 */
declare(strict_types=1);

namespace WPMCPBoilerplate;

// include the base tool.
require_once WP_MCP_BOILERPLATE_PATH . 'includes/class-base-tool.php';

// include the base tool.
use WPMCPBoilerplate\BaseTool;

/**
 * {$class_name} Tool.
 */
class {$class_name} extends BaseTool { // phpcs:ignore

	/**
	 * Set the name of the tool.
	 *
	 * @return string The name of the tool.
	 */
	protected function set_name(): string {
		return '{$tool_identifier}';
	}

	/**
	 * Set the description of the tool.
	 *
	 * @return string The description of the tool.
	 */
	protected function set_description(): string {
		return '{$tool_description}';
	}

	/**
	 * Set the type of the tool.
	 *
	 * @return string The type of the tool.
	 */
	protected function set_type(): string {
		return '{$tool_type}';
	}

	/**
	 * Set the input schema of the tool.
	 *
	 * @return array The input schema of the tool.
	 */
	protected function set_input_schema(): array {
		return {$schema_export};
	}

	/**
	 * Set the annotations of the tool.
	 *
	 * @return array The annotations of the tool.
	 */
	protected function set_annotations(): array {
		return {$annotations_export};
	}

	/**
	 * Execute the tool.
	 *
	 * @param array \$args The arguments of the tool.
	 * @return array The result of the tool.
	 */
	public function execute( array \$args ): array {
		{$execute_logic}
	}

	/**
	 * Set the permission to access the tool.
	 *
	 * @return bool The permission of the tool.
	 */
	public function permission(): bool {
		{$permission_logic}
	}
}
";
	}

	/**
	 * Sanitize class name to ensure it's valid PHP class name.
	 *
	 * @param string $name The input name.
	 * @return string The sanitized class name.
	 */
	private function sanitize_class_name( $name ): string {
		// Remove non-alphanumeric characters and convert to PascalCase.
		$name = preg_replace( '/[^a-zA-Z0-9]/', ' ', $name );
		$name = ucwords( $name );
		$name = str_replace( ' ', '', $name );

		// Ensure it starts with a letter.
		if ( ! empty( $name ) && is_numeric( $name[0] ) ) {
			$name = 'Tool' . $name;
		}

		// Ensure it ends with 'Tool' if not already.
		if ( ! str_ends_with( $name, 'Tool' ) ) {
			$name .= 'Tool';
		}

		return $name;
	}

	/**
	 * Convert camelCase to kebab-case.
	 *
	 * @param string $input_string The camelCase string.
	 * @return string The kebab-case string.
	 */
	private function camel_to_kebab( $input_string ): string {
		return strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $input_string ) );
	}

	/**
	 * Convert array to formatted string representation.
	 *
	 * @param array $input_array The array to convert.
	 * @return string The formatted array string.
	 */
	private function array_to_string( $input_array ): string {
		return var_export( $input_array, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
	}

	/**
	 * Format array export to be more readable.
	 *
	 * @param string $export The var_export output.
	 * @return string The formatted export.
	 */
	private function format_array_export( $export ): string {
		// Replace 'array (' with 'array('.
		$export = str_replace( 'array (', 'array(', $export );

		// Add proper indentation.
		$lines           = explode( "\n", $export );
		$formatted_lines = array();
		$indent_level    = 0;

		foreach ( $lines as $line ) {
			$trimmed = trim( $line );

			if ( str_contains( $trimmed, '),' ) || str_contains( $trimmed, ')' ) ) {
				--$indent_level;
			}

			$formatted_lines[] = str_repeat( "\t", $indent_level + 2 ) . $trimmed;

			if ( str_contains( $trimmed, 'array(' ) && ! str_contains( $trimmed, ')' ) ) {
				++$indent_level;
			}
		}

		return implode( "\n", $formatted_lines );
	}

	/**
	 * Indent code properly.
	 *
	 * @param string $code The code to indent.
	 * @param int    $tabs Number of tabs to indent.
	 * @return string The indented code.
	 */
	private function indent_code( $code, $tabs = 2 ): string {
		$lines          = explode( "\n", $code );
		$indented_lines = array();

		foreach ( $lines as $line ) {
			if ( ! empty( trim( $line ) ) ) {
				$indented_lines[] = str_repeat( "\t", $tabs ) . $line;
			} else {
				$indented_lines[] = '';
			}
		}

		return implode( "\n", $indented_lines );
	}

	/**
	 * Get default input schema.
	 *
	 * @return array The default input schema.
	 */
	private function get_default_input_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'parameter' => array(
					'type'        => 'string',
					'description' => 'Example parameter',
				),
			),
			'required'   => array( 'parameter' ),
		);
	}

	/**
	 * Get default execute logic.
	 *
	 * @return string The default execute logic.
	 */
	private function get_default_execute_logic(): string {
		return "return array(\n\t\t\t'message' => 'Tool executed successfully',\n\t\t\t'data' => \$args,\n\t\t);";
	}

	/**
	 * Get default annotations.
	 *
	 * @return array The default annotations.
	 */
	private function get_default_annotations(): array {
		return array(
			'title'         => 'Custom Tool',
			'readOnlyHint'  => false,
			'openWorldHint' => false,
		);
	}

	/**
	 * Set the permission to access the tool.
	 *
	 * @return bool The permission of the tool.
	 */
	public function permission(): bool {
		// Only allow administrators to create MCP tools.
		return current_user_can( 'manage_options' );
	}
}
