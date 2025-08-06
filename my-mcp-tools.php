<?php
/**
 * Plugin Name: My MCP Tools
 * Plugin URI:  https://aslamdoctor.com
 * Description: A plugin to add custom MCP tools.
 * Version:     1.0.0
 * Author:      Aslam Doctor
 * Author URI:  https://aslamdoctor.com
 * License:     GPL v2 or later
 * Text Domain: my-mcp-tools
 *
 * @package MyMCPTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MyMCPTools' ) ) {

	/**
	 * Main plugin class
	 *
	 * @since 1.0.0
	 */
	class MyMCPTools {

		/**
		 * Singleton instance
		 *
		 * @var MyMCPTools
		 */
		private static $instance = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Constructor: Initialize hooks
		 */
		private function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();
		}

		/**
		 * Get singleton instance
		 *
		 * @return My
		 */
		public static function instance() {
			if ( self::$instance === null ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Define constants
		 */
		private function define_constants() {
			define( 'MY_PLUGIN_VERSION', $this->version );
			define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			define( 'MY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}

		/**
		 * Include required files
		 */
		private function includes() {
			include_once MY_PLUGIN_PATH . 'includes/class-mymcptools.php';
		}

		/**
		 * Initialize hooks
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			// add admin notice if wordpress-mcp plugin is not active.
			if ( ! $this->is_wordpress_mcp_active() ) {
				add_action( 'admin_notices', function() {
					echo '<div class="error"><p>Please activate the <a href="https://wordpress.org/plugins/wordpress-mcp/">WordPress MCP</a> plugin before activating this plugin.</p></div>';
				} );
				deactivate_plugins( MY_PLUGIN_BASENAME );
			}

			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * On plugin activation
		 */
		public function activate() {
			// activate only if wordpress-mcp plugin is active.
		}

		/**
		 * On plugin deactivation
		 */
		public function deactivate() {
			// Run deactivation logic.
		}

		/**
		 * Check if wordpress-mcp plugin is active
		 */
		public function is_wordpress_mcp_active() {
			return in_array( 'wordpress-mcp/wordpress-mcp.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
		}

		/**
		 * Init hook callback
		 */
		public function init() {
			load_plugin_textdomain( 'my-mcp-tools', false, dirname( MY_PLUGIN_BASENAME ) . '/languages' );

			// Your initialization code here.
		}
	}
}

/**
 * Initialize plugin
 */
function my_mcp_tools_init() {
	return MyMCPTools::instance();
}

// Start plugin.
my_mcp_tools_init();
