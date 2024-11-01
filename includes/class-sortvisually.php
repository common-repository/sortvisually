<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sortvisually.com/
 * @since      1.0.0
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sortvisually
 * @subpackage Sortvisually/includes
 * @author     Optalenty Ltd. <ronen@optalenty.com>
 */
class Sortvisually {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sortvisually_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current API endpoint of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $endpoint    The current API endpoint of the plugin.
	 */
	protected $endpoint;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'SORTVISUALLY_VERSION' ) ) {
			$this->version = SORTVISUALLY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sortvisually';
		$this->endpoint = 'sortvisually';

		/**
		 * Check if WooCommerce is installed and active.
		 **/
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins')))){
			$this->init();
		}else{
			add_action('admin_notices',  array($this, 'sortvisually_admin_notices'));
		}

	}
	
	public function init(){
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->create_settings_page();
		$this->set_endpoints();
	}

	/**
	 * Notice when woocommerce isn't installed or unactive
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function sortvisually_admin_notices(){
		global $pagenow;
		if( $pagenow === 'plugins.php' ){
			$class = 'notice notice-error is-dismissible';
			$message = esc_html__('Sortvisually needs Woocommerce to be installed and active.', 'sortvisually');
			printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sortvisually_Loader. Orchestrates the hooks of the plugin.
	 * - Sortvisually_i18n. Defines internationalization functionality.
	 * - Sortvisually_Admin. Defines all hooks for the admin area.
	 * - Sortvisually_Settings_Page. Defines all hooks for the admin area.
	 * - Sortvisually_Endpoints. Defines all hooks for the admin area.
	 * - Sortvisually_getProductDetail. Defines all hooks for the admin area.
	 * - Sortvisually_getCategoryTree. Defines all hooks for the admin area.
	 * - Sortvisually_setProductPosition. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sortvisually-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sortvisually-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sortvisually-admin.php';

		$this->loader = new Sortvisually_Loader();

		/**
		 * The class responsible for defining settings functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sortvisually-settings.php';

		/**
		 * The class responsible for defining endpoints functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sortvisually-endpoints.php';

		/**
		 * The class responsible for defining getProductDetail endpoint functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/endpoints/class-getProductDetail.php';

		/**
		 * The class responsible for defining getCategoryTree endpoint functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/endpoints/class-getCategoryTree.php';

		/**
		 * The class responsible for defining setProductPosition endpoint functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/endpoints/class-setProductPosition.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sortvisually_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sortvisually_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Sortvisually_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Run class to execute all functionality of settings page
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function create_settings_page() {

		new Sortvisually_Settings_Page( $this->get_plugin_name(), $this->get_endpoint_url() );

	}

	/**
	 * Run class to execute all functionality of endpoints for API
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_endpoints() {

		new Sortvisually_Endpoints( $this->get_plugin_name(), $this->get_endpoint_url(), $this->get_version() );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		/**
		 * Check if WooCommerce is installed and active.
		 **/
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins')))){
			$this->loader->run();
		}else{
			add_action('admin_notices',  array($this, 'sortvisually_admin_notices'));
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * 
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * 
	 * @return    Sortvisually_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * 
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the url API endpoint of the plugin.
	 *
	 * @since     1.0.0
	 * 
	 * @return    string    The API endpoint of the plugin.
	 */
	public function get_endpoint_url() {
		return $this->endpoint;
	}

}
