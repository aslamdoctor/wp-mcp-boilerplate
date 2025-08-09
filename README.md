# WP MCP Boilerplate

A WordPress plugin boilerplate for creating custom MCP (Model Context Protocol) tools. This plugin provides a structured foundation for building and registering new MCP tools that can be used with the WordPress MCP plugin.

## Prerequisites

- WordPress site with the [WordPress MCP plugin](https://github.com/Automattic/wordpress-mcp/) installed and activated
- PHP 7.4 or higher

## Installation

1. Clone or download this plugin to your WordPress plugins directory
2. Rename the plugin folder from `wp-mcp-boilrtplate` to your desired plugin name
3. Activate the plugin through the WordPress admin panel

**Note:** The WordPress MCP plugin must be active before this boilerplate plugin can be activated.

## Plugin Structure

```
wp-mcp-boilerplate/
├── wp-mcp-boilerplate.php     # Main plugin file
├── includes/
│   ├── class-base-tool.php    # Abstract base class for all tools
│   └── class-get-version-info-tool.php # Example tool implementation
├── languages/                 # Translation files
├── uninstall.php             # Uninstall cleanup
└── README.md                 # This file
```

## Creating New MCP Tools

### Step 1: Create a New Tool Class

Create a new PHP file in the `includes/` directory for your tool. Follow this naming convention: `class-YOUR_TOOL_NAME-tool.php`

### Step 2: Extend the BaseTool Class

Your tool must extend the `BaseTool` abstract class and implement all required methods:

```php
<?php
/**
 * Your Custom Tool.
 */
declare(strict_types=1);

namespace WPMCPBoilerplate;

// Include the base tool
require_once WP_MCP_BOILERPLATE_PATH . 'includes/class-base-tool.php';
use WPMCPBoilerplate\BaseTool;

/**
 * Your Custom Tool Class.
 */
class YourCustomTool extends BaseTool {

    /**
     * Set the name of the tool.
     * This should be unique across all MCP tools.
     */
    protected function set_name(): string {
        return 'your_unique_tool_name';
    }

    /**
     * Set the description of the tool.
     * This helps users understand what the tool does.
     */
    protected function set_description(): string {
        return 'Description of what your tool does';
    }

    /**
     * Set the type of the tool.
     * Common types: 'read', 'create', 'update'
     */
    protected function set_type(): string {
        return 'read'; // or 'create', 'update', etc.
    }

    /**
     * Set the input schema for the tool.
     * Define the parameters your tool accepts using JSON Schema format.
     */
    protected function set_input_schema(): array {
        return array(
            'type' => 'object',
            'properties' => array(
                'parameter_name' => array(
                    'type' => 'string',
                    'description' => 'Description of this parameter',
                ),
                'optional_param' => array(
                    'type' => 'integer',
                    'description' => 'An optional parameter',
                    'default' => 10,
                ),
            ),
            'required' => array('parameter_name'), // List required parameters
        );
    }

    /**
     * Set annotations for the tool.
     * These provide additional metadata about the tool.
     */
    protected function set_annotations(): array {
        return array(
            'title' => 'Your Tool Title',
            'readOnlyHint' => true,  // Set to false if tool modifies data
            'openWorldHint' => false, // Set to true if tool can accept arbitrary inputs
        );
    }

    /**
     * Execute the tool.
     * This is where your tool's main logic goes.
     */
    public function execute(array $args): array {
        // Access parameters from $args
        $parameter_value = $args['parameter_name'] ?? '';
        $optional_value = $args['optional_param'] ?? 10;

        // Your tool logic here
        $result = $this->do_something($parameter_value, $optional_value);

        // Return the result as an array
        return array(
            'success' => true,
            'data' => $result,
            'message' => 'Tool executed successfully',
        );
    }

    /**
     * Set permissions for the tool.
     * Return true to allow access, false to deny.
     */
    public function permission(): bool {
        // You can add custom permission logic here
        // For example: return current_user_can('manage_options');
        return true;
    }

    /**
     * Your custom method(s)
     */
    private function do_something($param1, $param2) {
        // Implementation here
        return "Processed: $param1 with $param2";
    }
}
```

### Step 3: Register Your Tool

Add your tool to the main plugin file by including it in the `includes()` method and instantiating it in the `init()` method:

```php
// In wp-mcp-boilerplate.php

private function includes() {
    // Include existing tools
    include_once WP_MCP_BOILERPLATE_PATH . 'includes/class-get-version-info-tool.php';

    // Add your new tool
    include_once WP_MCP_BOILERPLATE_PATH . 'includes/class-your-custom-tool.php';
}

public function init() {
    load_plugin_textdomain('wp-mcp-boilerplate', false, dirname(WP_MCP_BOILERPLATE_BASENAME) . '/languages');

    // Initialize existing tools
    new GetVersionInfoTool();

    // Initialize your new tool
    new YourCustomTool();
}
```

## Example Tool Types

### Data Retrieval Tool (Read)
```php
protected function set_type(): string {
    return 'read';
}

public function execute(array $args): array {
    $posts = get_posts(array('numberposts' => $args['limit'] ?? 10));
    return array('posts' => $posts);
}
```

### Data Modification Tool (Create)
```php
protected function set_type(): string {
    return 'create';
}

public function execute(array $args): array {
    $post_id = wp_insert_post(array(
        'post_title' => $args['title'],
        'post_content' => $args['content'],
        'post_status' => 'publish'
    ));

    return array('post_id' => $post_id);
}
```

### Administrative Tool (Update)
```php
protected function set_type(): string {
    return 'update';
}

public function permission(): bool {
    return current_user_can('manage_options');
}

public function execute(array $args): array {
    update_option($args['option_name'], $args['option_value']);
    return array('updated' => true);
}
```

## Input Schema Examples

### Simple String Parameter
```php
protected function set_input_schema(): array {
    return array(
        'type' => 'object',
        'properties' => array(
            'message' => array(
                'type' => 'string',
                'description' => 'The message to process',
            ),
        ),
        'required' => array('message'),
    );
}
```

### Multiple Parameter Types
```php
protected function set_input_schema(): array {
    return array(
        'type' => 'object',
        'properties' => array(
            'post_id' => array(
                'type' => 'integer',
                'description' => 'The ID of the post',
            ),
            'tags' => array(
                'type' => 'array',
                'items' => array('type' => 'string'),
                'description' => 'Array of tag names',
            ),
            'is_featured' => array(
                'type' => 'boolean',
                'description' => 'Whether the post is featured',
                'default' => false,
            ),
        ),
        'required' => array('post_id'),
    );
}
```

### No Parameters Required
```php
protected function set_input_schema(): array {
    return array(
        'type' => 'object',
        'properties' => array(
            'no_parameters' => array(
                'type' => 'string',
                'description' => 'No parameters required',
            ),
        ),
    );
}
```

## Best Practices

1. **Tool Naming**: Use descriptive, unique names with a consistent prefix (e.g., `wmb_` for "WordPress MCP Boilerplate")

2. **Error Handling**: Always return meaningful error messages in your tool responses:
   ```php
   if (!$required_data) {
       return array(
           'success' => false,
           'error' => 'Required data not found',
           'code' => 'missing_data'
       );
   }
   ```

3. **Input Validation**: Validate all input parameters before processing:
   ```php
   if (!isset($args['post_id']) || !is_numeric($args['post_id'])) {
       return array('error' => 'Invalid post ID provided');
   }
   ```

4. **Permissions**: Implement proper permission checks for sensitive operations:
   ```php
   public function permission(): bool {
       return current_user_can('edit_posts'); // or appropriate capability
   }
   ```

5. **Documentation**: Always provide clear descriptions for your tools and parameters

## Customization

### Changing Plugin Details
Edit the header comment in `wp-mcp-boilerplate.php` to update:
- Plugin Name
- Description
- Version
- Author information
- Text Domain

### Adding Internationalization
1. Update text domain references throughout the code
2. Add translation files to the `languages/` directory
3. Use WordPress i18n functions: `__()`, `_e()`, `esc_html__()`

## Testing Your Tools

After creating and registering your tools:

1. Ensure the WordPress MCP plugin is active
2. Activate your boilerplate plugin
3. Your tools should now be available through the MCP interface
4. Test each tool with various input parameters
5. Verify error handling and edge cases

## Troubleshooting

### Tool Not Appearing
- Check that WordPress MCP plugin is active
- Verify your tool class extends `BaseTool`
- Ensure the tool is included and instantiated in the main plugin file
- Check for PHP errors in WordPress debug log

### Permission Errors
- Verify the `permission()` method returns `true` for your test user
- Check user capabilities if using WordPress capability checks

### Schema Validation Errors
- Ensure your input schema follows proper JSON Schema format
- Verify required parameters are listed in the `required` array
- Check parameter types match expected values

## Contributing

When contributing to this boilerplate:

1. Follow WordPress coding standards
2. Add PHPDoc comments to all methods
3. Include proper error handling
4. Test with different WordPress versions
5. Update this README if adding new features

## License

GPL v2 or later

## Support

For issues related to the WordPress MCP plugin functionality, refer to the [WordPress MCP plugin documentation](https://github.com/Automattic/wordpress-mcp/).

For issues specific to this boilerplate, please check the plugin's repository or contact the plugin author.
