<?php
/**
 * Plugin Name: WP MCP Boilerplate
 * Plugin URI:  https://aslamdoctor.com
 * Description: A boilerplate plugin to add custom MCP tools.
 * Version:     1.0.0
 * Author:      Aslam Doctor
 * Author URI:  https://aslamdoctor.com
 * License:     GPL v2 or later
 * Text Domain: wp-mcp-boilerplate
 *
 * @package WPMCPBoilerplate
 */

namespace WPMCPBoilerplate;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPMCPBoilerplate' ) ) {

	/**
	 * Main plugin class
	 *
	 * @since 1.0.0
	 */
	class WPMCPBoilerplate {

		/**
		 * Singleton instance
		 *
		 * @var WPMCPBoilerplate
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
		 * @return WPMCPBoilerplate
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Define constants
		 */
		private function define_constants() {
			define( 'WP_MCP_BOILERPLATE_VERSION', $this->version );
			define( 'WP_MCP_BOILERPLATE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'WP_MCP_BOILERPLATE_URL', plugin_dir_url( __FILE__ ) );
			define( 'WP_MCP_BOILERPLATE_BASENAME', plugin_basename( __FILE__ ) );
		}

		/**
		 * Include required files
		 */
		private function includes() {
			// include all the tools.
			include_once WP_MCP_BOILERPLATE_PATH . 'includes/class-get-version-info-tool.php';
		}

		/**
		 * Initialize hooks
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			// add admin notice if wordpress-mcp plugin is not active.
			if ( ! $this->is_wordpress_mcp_active() ) {
				add_action(
					'admin_notices',
					function () {
						echo '<div class="error"><p>Please activate the <a href="https://wordpress.org/plugins/wordpress-mcp/">WordPress MCP</a> plugin before activating this plugin.</p></div>';
					}
				);
				deactivate_plugins( WP_MCP_BOILERPLATE_BASENAME );
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
			load_plugin_textdomain( 'wp-mcp-boilerplate', false, dirname( WP_MCP_BOILERPLATE_BASENAME ) . '/languages' );

			new GetVersionInfoTool();
		}
	}
}

/**
 * Initialize plugin
 */
function wp_mcp_boilerplate_init() {
	return WPMCPBoilerplate::instance();
}

// Start plugin.
wp_mcp_boilerplate_init();
