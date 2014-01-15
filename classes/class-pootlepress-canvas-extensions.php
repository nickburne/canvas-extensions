<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Pootlepress_Sticky_Nav Class
 *
 * Base class for the Pootlepress Sticky Nav.
 *
 * @package WordPress
 * @subpackage Pootlepress_Sticky_Nav
 * @category Core
 * @author Pootlepress
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * public $token
 * public $version
 * private $_menu_style
 * 
 * - __construct()
 * - add_theme_options()
 * - load_localisation()
 * - check_plugins()
 * - load_plugin_textdomain()
 * - activation()
 * - register_plugin_version()
 * - load_sticky_nav()
 */
class Pootlepress_Canvas_Extensions {
	public $token = 'pootlepress-canvas-extensions';
	public $version;
	private $file;
	private $_menu_style;

	/**
	 * Constructor.
	 * @param string $file The base file of the plugin.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct ( $file ) {
		$this->file = $file;
		$this->load_plugin_textdomain();
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $file, array( &$this, 'activation' ) );

		// Add the custom theme options.
		add_filter( 'option_woo_template', array( &$this, 'add_theme_options' ) );

		// Lood for a method/function for the selected style and load it.
		add_action('init', array( &$this, 'load_sticky_nav' ) );
	} // End __construct()

	/**
	 * Add theme options to the WooFramework.
	 * @access public
	 * @since  1.0.0
	 * @param array $o The array of options, as stored in the database.
	 */
	public function add_theme_options ( $o ) {
		$o[] = array(
				'name' => __( 'Pootlepress Canvas Extensions', 'pootlepress-canvas-extensions' ), 
				'icon' => 'favorite', 
				'type' => 'heading'
				);
		if ($this->check_plugins()) {
			//There are plugins installed
			$o[] = array(
					'id' => 'pootlepress-no-canvas-extensions', 
					'name' => 'There are plugins installed!', 
					'type' => ''
					);
			
			//Sticky Nav
			if ($this->is_stickynav_activated()) {
				$o[] = array(
						'name' => 'Pootlepress Sticky Nav', 
						'type' => 'subheading'
						);
				$o[] = array(
						'id' => 'pootlepress-sticky-nav-option', 
						'name' => __( 'Enable or Disable Sticky Nav', 'pootlepress-sticky-nav' ), 
						'desc' => __( 'Click here to enable or disable the Pootlepress sticky nav', 'pootlepress-sticky-nav' ), 
						'std' => 'true',
						'type' => 'checkbox'
						);
				$o[] = array(
						'id' => 'pootlepress-sticky-nav-wpadminbar', 
						'name' => __( 'Enable or Disable the Wordpress Admin Bar', 'pootlepress-sticky-nav' ), 
						'desc' => __( 'Click here to enable or disable the Wordpress Admin Bar. The Wordpress admin bar can hide the sticky nav.', 'pootlepress-sticky-nav' ), 
						'std' => 'true',
						'type' => 'checkbox'
						);
			}

			//Menu Pack
			if ($this->is_menupack_activated()) {
				$styles = array();
				//$menupack = new test();
				
				foreach ( (array)$GLOBALS['pootlepress_menu_pack']->get_menu_styles() as $k => $v ) {
					if ( isset( $v['name'] ) ) {
						$styles[$k] = $v['name'];
					}
				}
				
				$o[] = array(
						'name' => __( 'Pootlepress Menus', 'pootlepress-menu-pack' ),
						'type' => 'subheading'
						);
				$o[] = array(
						'id' => 'pootlepress-menu-pack-menu-style', 
						'name' => __( 'Menu Style', 'pootlepress-menu-pack' ), 
						'desc' => __( 'Select your preferred menu look and feel.', 'pootlepress-menu-pack' ), 
						'type' => 'select2', 
						'options' => $styles
						);
			}

			
		} else {
			//There are no plugins installed
			$o[] = array(
					'name' => 'Welcome To Canvas',
					'desc' => '',
					'id' => 'pootlepress-no-canvas-extensions',
					'std' => sprintf(("Thanks for download the Canvas Extensions plugin. Now find some extensions at <a href=\"%s\" target=\"_blank\">http://www.pootlepress.com/shop</a>" ), "http://www.pootlepress.com/shop" ),
					'type' => 'info'
					);
		}

		return $o;
	} // End add_theme_options()
	
	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( $this->token, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()
	
	/**
	 * Check Plugins
	 * @access public
	 * @since 1.0.0
	 * @return boolean
	 */
	public function check_plugins () {	
		//Check if any plugins are activated
		if (($this->is_stickynav_activated())||($this->is_menupack_activated())) {
			return true;
		} else {
			return false;
		}
	}
	
	public function is_stickynav_activated() {
		if ( class_exists( 'Pootlepress_Sticky_Nav' ) ) { return true; } else { return false; }
	}	
	
	public function is_menupack_activated() {
		if ( class_exists( 'Pootlepress_Menu_Pack' ) ) { return true; } else { return false; }
	}	

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = $this->token;
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	 
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( $this->token . '-version', $this->version );
		}
	} // End register_plugin_version()
	
	/**
	 * Load the sticky nav files
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_sticky_nav () {
		$_stickyenabled  = get_option('pootlepress-sticky-nav-option');
		$_wpadminbarhide = get_option('pootlepress-sticky-nav-wpadminbar');

		if ($_stickyenabled == '') $enabled = 'true';
		# add_action('wp_head', 'issticky');
	} // End load_sticky_nav()
	

} // End Class


