<?php
/**
 * Envato Theme Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their ThemeForest theme.

 *
 * Based off the WooThemes installer.
 *
 *
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



if ( ! class_exists( 'Envato_Theme_Setup_Wizard' ) ) {
	/**
	 * Envato_Theme_Setup_Wizard class
	 */
	class Envato_Theme_Setup_Wizard {

		/**
		 * The class version number.target
		 *
		 * @since 1.1.1
		 * @access private
		 *
		 * @var string
		 */
		protected $version = '4.0.8.5';

		/** @var string Current theme name, used as namespace in actions. */
		protected $theme_name = 'wplms';

		/** @var string Theme author username, used in check for oauth. */
		protected $envato_username = 'vibethemes';

		protected $oauth_script = '';

		/** @var string Current Step */
		protected $step = '';

		/** @var array Steps for the setup wizard */
		protected $steps = array();

		/**
		 * Relative plugin path
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_path = '';

		/**
		 * Relative plugin url for this plugin folder, used when enquing scripts
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_url = '';

		/**
		 * The slug name to refer to this menu
		 *
		 * @since 1.1.1
		 *
		 * @var string
		 */
		protected $page_slug;

		/**
		 * TGMPA instance storage
		 *
		 * @var object
		 */
		protected $tgmpa_instance;

		/**
		 * TGMPA Menu slug
		 *
		 * @var string
		 */
		public $tgmpa_menu_slug = 'tgmpa-install-plugins';

		/**
		 * TGMPA Menu url
		 *
		 * @var string
		 */
		public $tgmpa_url = 'themes.php?page=tgmpa-install-plugins';

		/**
		 * The slug name for the parent menu
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_parent;

		/**
		 * Complete URL to Setup Wizard
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_url;

		/**
		 * @since 1.1.8
		 *
		 */
		public $site_styles = array();
		/**
		 * @since 1.1.8
		 *
		 */
		public $debug = 0;
		/**
		 * @since 1.1.8
		 *
		 */
		public $features = array();

		/**
		 * Holds the current instance of the theme manager
		 *
		 * @since 1.1.3
		 * @var Envato_Theme_Setup_Wizard
		 */
		private static $instance = null;

		/**
		 * @since 1.1.3
		 *
		 * @return Envato_Theme_Setup_Wizard
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.1
		 * @access private
		 */
		public function __construct() {
			$this->init_globals();
			$this->init_actions();

			//Ajax Calls
			add_action('wp_ajax_wplms_exported_content_plupload',array($this,'wplms_exported_content_plupload'));
			add_action('wp_ajax_insert_export_content_final',array($this,'insert_export_content_final'));
		}

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.7
		 * @access public
		 */
		public function get_default_theme_style() {
			return 'learningcenter';
		}

		

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_logo_image() {
			$image_url = '';
			return apply_filters( 'envato_setup_logo_image', get_template_directory_uri().'/assets/images/logo.png' );
		}

		/**
		 * Setup the class globals.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_globals() {

			$current_theme         = wp_get_theme();
			$this->theme_name      = 'wplms';//strtolower( preg_replace( '#[^a-zA-Z]#', '', $current_theme->get( 'Name' ) ) );
			$this->envato_username = apply_filters( $this->theme_name . '_theme_setup_wizard_username', 'vibethemes' );
			
			$this->page_slug       = apply_filters( $this->theme_name . '_theme_setup_wizard_page_slug', $this->theme_name . '-setup' );
			$this->parent_slug     = apply_filters( $this->theme_name . '_theme_setup_wizard_parent_slug', '' );

			$this->features = array(
								
	                    		'courses'=>array(
	                    						'label'=>__('[ RECOMMENDED ] LMS'),
	                    						'default'=>1,
	                    						'icon'=>'<svg version="1.1" id="E-Learning" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 58" style="enable-background:new 0 0 60 58;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,3h-3V1c0-0.552-0.447-1-1-1H30H5C4.447,0,4,0.448,4,1v2H1C0.447,3,0,3.448,0,4v37c0,0.552,0.447,1,1,1h43v-2H2V5h2v32c0,0.552,0.447,1,1,1h25h14v-2H31V2h2v25.875c0,1.115,0.334,1.964,0.993,2.522c0.791,0.668,1.741,0.638,2.068,0.603h9.522c0.751,0,0.997,0.28,1.078,0.372c0.384,0.435,0.38,1.228,0.348,1.491C47.003,32.908,47,32.954,47,33v15.041c0,0.263-0.04,0.613-0.23,0.787c-0.195,0.178-0.552,0.187-0.646,0.18C46.083,49.002,46.041,49,46,49H35v2h10.954c0.054,0.004,0.129,0.008,0.222,0.008c0.436,0,1.248-0.09,1.911-0.676C48.504,49.965,49,49.269,49,48.041V33.059c0.042-0.392,0.129-1.9-0.825-2.996C47.753,29.578,46.961,29,45.584,29H36c-0.056,0-0.11,0.004-0.165,0.014c-0.003,0.001-0.344,0.032-0.549-0.143C35.102,28.714,35,28.361,35,27.875V2h19v2v32h-2v2h3c0.553,0,1-0.448,1-1V5h2v35h-6v2h7c0.553,0,1-0.448,1-1V4C60,3.448,59.553,3,59,3z M6,36V4V2h23v34H6z"/><path style="fill:#231F20;" d="M30.741,44H15.259C13.462,44,12,45.565,12,47.488v6.939C12,56.364,13.492,58,15.259,58h15.482C32.508,58,34,56.364,34,54.428v-6.939C34,45.565,32.538,44,30.741,44z M32,54.428C32,55.25,31.4,56,30.741,56H15.259C14.6,56,14,55.25,14,54.428v-6.939C14,46.695,14.588,46,15.259,46h15.482C31.412,46,32,46.695,32,47.488V54.428z"/><rect x="27" y="49" style="fill:#231F20;" width="3" height="2"/><path style="fill:#231F20;" d="M52,5c0-0.552-0.447-1-1-1H38v2h12v10H38v2h13c0.553,0,1-0.448,1-1V5z"/><rect x="38" y="20" style="fill:#231F20;" width="13" height="2"/><rect x="38" y="24" style="fill:#231F20;" width="13" height="2"/><rect x="9" y="20" style="fill:#231F20;" width="11" height="2"/><rect x="9" y="24" style="fill:#231F20;" width="16" height="2"/><rect x="9" y="28" style="fill:#231F20;" width="16" height="2"/><path style="fill:#231F20;" d="M25,4H10C9.447,4,9,4.448,9,5v11c0,0.552,0.447,1,1,1h15c0.553,0,1-0.448,1-1V5C26,4.448,25.553,4,25,4z M24,15H11V6h13V15z"/></g></svg>',
	                    						'description'=> __('The WPLMS Frameowrk.','vibe'),
	                    						'verify'=>array('wplms_plugin/loader.php','vibebp/loader.php','buddypress/bp-loader.php')
                    						),
	                    		'woocommerce'=>array(
	                    						'label'=>__('[ RECOMMENDED ] eCommerce','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Price" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,0H1C0.447,0,0,0.448,0,1v6c0,0.552,0.447,1,1,1h8v4H3c-0.553,0-1,0.448-1,1v30c0,0.552,0.447,1,1,1h54c0.553,0,1-0.448,1-1V13c0-0.552-0.447-1-1-1h-6V8h8c0.553,0,1-0.448,1-1V1C60,0.448,59.553,0,59,0z M11,8h8v4h-8V8z M31,12V8h8v4H31z M29,12h-8V8h8V12z M56,42H4V14h52V42z M49,12h-8V8h8V12z M58,6H2V2h56V6z"/><polygon style="fill:#231F20;" points="58,50 2,50 2,47 0,47 0,51 0,60 2,60 2,57 38,57 38,55 2,55 2,52 58,52 58,60 60,60 60,51 60,47 58,47 "/><rect x="42" y="55" style="fill:#231F20;" width="4" height="2"/><path style="fill:#231F20;" d="M21,35h-2c-1.654,0-3-1.346-3-3h-2c0,2.757,2.243,5,5,5v2h2v-2c2.757,0,5-2.243,5-5s-2.243-5-5-5h-2c-1.654,0-3-1.346-3-3s1.346-3,3-3h2c1.654,0,3,1.346,3,3h2c0-2.757-2.243-5-5-5v-2h-2v2c-2.757,0-5,2.243-5,5s2.243,5,5,5h2c1.654,0,3,1.346,3,3S22.654,35,21,35z"/><rect x="30" y="21" style="fill:#231F20;" width="9" height="2"/><rect x="30" y="27" style="fill:#231F20;" width="17" height="2"/><rect x="30" y="33" style="fill:#231F20;" width="17" height="2"/></g></svg>',
	                    						'default'=>1,
	                    						'description'=> __('Create and sell courses online.','vibe'),
	                    						'verify'=>array('woocommerce/woocommerce.php')
                    						),
	                    		'certificates'=>array(
	                    						'label'=>__('[ RECOMMENDED ] Certficates','vibe'),
	                    						'default'=>1,
	                    						'icon'=>'<svg version="1.1" id="Adventure_Quest" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 52 60" style="enable-background:new 0 0 52 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M40,58H2V6c0-0.163,0.045-4,4-4h35.532c-0.015,0.017-0.025,0.037-0.041,0.054c-0.205,0.234-0.392,0.484-0.559,0.748c-0.026,0.042-0.051,0.084-0.077,0.127c-0.162,0.27-0.304,0.552-0.423,0.847c-0.017,0.042-0.03,0.083-0.046,0.125c-0.115,0.306-0.209,0.621-0.273,0.948c-0.006,0.03-0.008,0.061-0.014,0.09C40.038,5.284,40,5.638,40,6v3h2V7h9c0.552,0,1-0.448,1-1c0-3.309-2.691-6-6-6H6C1.254,0,0,3.925,0,6v53c0,0.552,0.448,1,1,1h40c0.552,0,1-0.448,1-1v-7h-2V58z M49.873,5h-7.731c0.036-0.138,0.076-0.273,0.125-0.404c0.023-0.06,0.038-0.123,0.063-0.181c0.096-0.221,0.21-0.432,0.343-0.63c0.029-0.043,0.065-0.08,0.095-0.122c0.11-0.151,0.227-0.297,0.356-0.432c0.058-0.06,0.123-0.114,0.184-0.17c0.115-0.105,0.233-0.206,0.359-0.296c0.074-0.054,0.15-0.103,0.228-0.151c0.129-0.081,0.263-0.153,0.401-0.218c0.082-0.039,0.163-0.078,0.248-0.111c0.153-0.06,0.312-0.106,0.474-0.148c0.077-0.02,0.152-0.045,0.231-0.06C45.494,2.029,45.743,2,46,2C47.86,2,49.428,3.277,49.873,5z"/><rect x="5" y="6" style="fill:#231F20;" width="8" height="2"/><rect x="5" y="10" style="fill:#231F20;" width="14" height="2"/><rect x="5" y="16" style="fill:#231F20;" width="22" height="2"/><rect x="5" y="21" style="fill:#231F20;" width="22" height="2"/><rect x="5" y="26" style="fill:#231F20;" width="22" height="2"/><rect x="5" y="31" style="fill:#231F20;" width="24" height="2"/><rect x="5" y="36" style="fill:#231F20;" width="24" height="2"/><path style="fill:#231F20;" d="M41,11c-6.065,0-11,4.935-11,11c0,2.913,1.145,5.557,3,7.526V53c0,0.375,0.209,0.718,0.542,0.889C33.686,53.963,33.844,54,34,54c0.205,0,0.408-0.063,0.581-0.186L41,49.229l6.419,4.585C47.592,53.937,47.795,54,48,54c0.156,0,0.313-0.037,0.457-0.111C48.79,53.718,49,53.375,49,53V29.526c1.854-1.97,3-4.614,3-7.526C52,15.935,47.065,11,41,11z M41,13c4.962,0,9,4.038,9,9c0,4.962-4.038,9-9,9c-4.962,0-9-4.038-9-9C32,17.038,36.038,13,41,13z M35,31.208c1.464,0.957,3.166,1.576,5,1.742v14.536l-5,3.571V31.208z M42,47.485V32.949c1.833-0.166,3.536-0.784,5-1.742v19.849L42,47.485z"/><rect x="40" y="21" style="fill:#231F20;" width="2" height="2"/><rect x="44" y="21" style="fill:#231F20;" width="2" height="2"/><rect x="40" y="25" style="fill:#231F20;" width="2" height="2"/><rect x="36" y="21" style="fill:#231F20;" width="2" height="2"/><rect x="40" y="17" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="43" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="47" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="51" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="43" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="47" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="51" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="43" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="47" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="51" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="43" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="47" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="51" style="fill:#231F20;" width="2" height="2"/><rect x="21" y="43" style="fill:#231F20;" width="2" height="2"/><rect x="21" y="47" style="fill:#231F20;" width="2" height="2"/><rect x="21" y="51" style="fill:#231F20;" width="2" height="2"/><rect x="25" y="43" style="fill:#231F20;" width="2" height="2"/><rect x="25" y="47" style="fill:#231F20;" width="2" height="2"/><rect x="25" y="51" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> __('Award certificates. Drag Drop Certificate builder.','vibe'),
	                    						'verify'=>array('wplms-pdf-certificates/wplms-pdf-certificates.php')
                    						),
								'earnings'=>array(
	                    						'label'=>__('Earnings','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Investment-2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><rect x="1" y="36" style="fill:#231F20;" width="2" height="24"/><path style="fill:#231F20;" d="M24,17c-7.72,0-14,6.28-14,14s6.28,14,14,14s14-6.28,14-14S31.72,17,24,17z M24,43c-6.617,0-12-5.383-12-12s5.383-12,12-12s12,5.383,12,12S30.617,43,24,43z"/><path style="fill:#231F20;" d="M26,26h-1v-2h-2v2h-1c-1.103,0-2,0.897-2,2v2c0,1.103,0.897,2,2,2h4v2h-4v-1h-2v1c0,1.103,0.897,2,2,2h1v2h2v-2h1c1.103,0,2-0.897,2-2v-2c0-1.103-0.897-2-2-2h-4v-2h4v1h2v-1C28,26.897,27.103,26,26,26z"/><rect x="31" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="15" y="30" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M50.586,20H40c-0.265,0-0.52,0.105-0.707,0.293l-2,2l1.414,1.414L40.414,22H51c0.265,0,0.52-0.105,0.707-0.293l7-7l-1.414-1.414L50.586,20z"/><rect x="1" y="28" style="fill:#231F20;" width="7" height="2"/><rect x="1" y="32" style="fill:#231F20;" width="2" height="2"/><rect x="6" y="32" style="fill:#231F20;" width="2" height="2"/><rect x="6" y="36" style="fill:#231F20;" width="2" height="24"/><rect x="11" y="44" style="fill:#231F20;" width="2" height="16"/><rect x="16" y="48" style="fill:#231F20;" width="2" height="12"/><rect x="21" y="50" style="fill:#231F20;" width="2" height="10"/><rect x="26" y="50" style="fill:#231F20;" width="2" height="10"/><rect x="31" y="48" style="fill:#231F20;" width="2" height="12"/><rect x="36" y="44" style="fill:#231F20;" width="2" height="16"/><rect x="51" y="24" style="fill:#231F20;" width="2" height="2"/><rect x="46" y="24" style="fill:#231F20;" width="2" height="2"/><rect x="41" y="24" style="fill:#231F20;" width="2" height="2"/><rect x="51" y="28" style="fill:#231F20;" width="2" height="32"/><rect x="56" y="20" style="fill:#231F20;" width="2" height="2"/><rect x="56" y="24" style="fill:#231F20;" width="2" height="36"/><rect x="46" y="28" style="fill:#231F20;" width="2" height="32"/><rect x="41" y="28" style="fill:#231F20;" width="2" height="32"/><path style="fill:#231F20;" d="M59,0H1C0.448,0,0,0.447,0,1v25h2V10h56v2h2V1C60,0.447,59.552,0,59,0z M2,8V2h56v6H2z"/><rect x="4" y="4" style="fill:#231F20;" width="4" height="2"/><rect x="10" y="4" style="fill:#231F20;" width="4" height="2"/><rect x="16" y="4" style="fill:#231F20;" width="4" height="2"/><rect x="54" y="4" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> __('Instructor Earnings & Commissions. Integration with third party affiliate & earning programes.','vibe'),
	                    						'verify'=>array('vibe-earnings/loader.php'),
                    						),
								'calendar'=>array(
	                    						'label'=>__('Calendar','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Event_Analysis" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,4h-7V1c0-0.553-0.448-1-1-1h-4c-0.552,0-1,0.447-1,1v3H33V1c0-0.553-0.448-1-1-1h-4c-0.552,0-1,0.447-1,1v3H14V1c0-0.553-0.448-1-1-1H9C8.448,0,8,0.447,8,1v3H1C0.448,4,0,4.447,0,5v16v38c0,0.553,0.448,1,1,1h58c0.552,0,1-0.447,1-1V21V5C60,4.447,59.552,4,59,4z M2,17h56v3H2V17z M48,2h2v8h-2V2z M29,2h2v8h-2V2z M10,2h2v8h-2V2z M8,6v5c0,0.553,0.448,1,1,1h4c0.552,0,1-0.447,1-1V6h13v5c0,0.553,0.448,1,1,1h4c0.552,0,1-0.447,1-1V6h13v5c0,0.553,0.448,1,1,1h4c0.552,0,1-0.447,1-1V6h6v9H2V6H8z M58,58H2V22h56V58z"/><path style="fill:#231F20;" d="M7,55h20c0.552,0,1-0.447,1-1V26c0-0.553-0.448-1-1-1H7c-0.552,0-1,0.447-1,1v28C6,54.553,6.448,55,7,55z M8,27h18v26H8V27z"/><path style="fill:#231F20;" d="M33,35h8c0.552,0,1-0.447,1-1v-8c0-0.553-0.448-1-1-1h-8c-0.552,0-1,0.447-1,1v8C32,34.553,32.448,35,33,35z M35.414,33L40,28.414V33H35.414z M38.586,27L34,31.586V27H38.586z"/><path style="fill:#231F20;" d="M45,35h8c0.552,0,1-0.447,1-1v-8c0-0.553-0.448-1-1-1h-8c-0.552,0-1,0.447-1,1v8C44,34.553,44.448,35,45,35z M47.414,33L52,28.414V33H47.414z M50.586,27L46,31.586V27H50.586z"/><rect x="32" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="36" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="44" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="48" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="10" y="47" style="fill:#231F20;" width="2" height="4"/><rect x="14" y="43" style="fill:#231F20;" width="2" height="8"/><rect x="18" y="37" style="fill:#231F20;" width="2" height="14"/><rect x="22" y="45" style="fill:#231F20;" width="2" height="6"/><rect x="10" y="33" style="fill:#231F20;" width="6" height="2"/><rect x="10" y="29" style="fill:#231F20;" width="2" height="2"/><rect x="14" y="29" style="fill:#231F20;" width="2" height="2"/><rect x="18" y="29" style="fill:#231F20;" width="2" height="2"/><rect x="32" y="42" style="fill:#231F20;" width="14" height="2"/><rect x="32" y="47" style="fill:#231F20;" width="22" height="2"/><rect x="32" y="52" style="fill:#231F20;" width="22" height="2"/></g></svg>',
	                    						'description'=> __('One stop personal calendar for Events, Appointments, Confrencing and sync with Google Calendar.','vibe'),
	                    						'verify'=>array('vibe-calendar/vibe-calendar.php')
                    						),
	                    		'drive'=>array(
	                    						'label'=>__('Drive','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Upload_and_Download" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,14h-3v-4c0-0.552-0.448-1-1-1h-6v2h5v3h-5v2h6h3v42H2V2h24v8v4H8v2h19h5v-2h-4v-3h4V9h-4V1c0-0.552-0.448-1-1-1H1C0.448,0,0,0.448,0,1v58c0,0.552,0.448,1,1,1h58c0.552,0,1-0.448,1-1V15C60,14.448,59.552,14,59,14z"/><path style="fill:#231F20;" d="M35,3.414V42h2V3.414l1.293,1.293l1.414-1.414l-3-3c-0.391-0.391-1.023-0.391-1.414,0l-3,3l1.414,1.414L35,3.414z"/><path style="fill:#231F20;" d="M45,42c0.256,0,0.512-0.098,0.707-0.293l3-3l-1.414-1.414L46,38.586V0h-2v38.586l-1.293-1.293l-1.414,1.414l3,3C44.488,41.902,44.744,42,45,42z"/><rect x="8" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="16" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="24" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="32" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="40" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="48" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="8" y="20" style="fill:#231F20;" width="4" height="2"/><rect x="16" y="20" style="fill:#231F20;" width="4" height="2"/><rect x="24" y="20" style="fill:#231F20;" width="4" height="2"/><rect x="48" y="20" style="fill:#231F20;" width="4" height="2"/></g></svg>',
	                    						'description'=> __('Upload and share attachments via drive with restricted access.','vibe'),
	                    						'verify'=>array('vibedrive/vibedrive.php'),
                    						),
	                    		'kb'=>array(
	                    						'label'=>__('Knowledge Base','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Content_Sharing" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M42,58H2V37h41.586l-1.293,1.293l1.414,1.414l3-3c0.391-0.391,0.391-1.023,0-1.414l-3-3l-1.414,1.414L43.586,35H2V2h30v9c0,0.552,0.448,1,1,1h9v4h2v-5c0-0.022-0.011-0.041-0.013-0.063c-0.005-0.088-0.022-0.173-0.051-0.257c-0.011-0.032-0.02-0.063-0.034-0.094c-0.049-0.106-0.11-0.207-0.196-0.293l-10-10c-0.086-0.086-0.188-0.148-0.294-0.197c-0.029-0.013-0.059-0.021-0.089-0.032c-0.086-0.03-0.173-0.047-0.264-0.053C33.039,0.011,33.021,0,33,0H1C0.448,0,0,0.448,0,1v58c0,0.552,0.448,1,1,1h42c0.552,0,1-0.448,1-1v-5h-2V58z M40.586,10H34V3.414L40.586,10z"/><path style="fill:#231F20;" d="M59.987,28.937c-0.005-0.088-0.022-0.173-0.051-0.257c-0.011-0.032-0.02-0.063-0.034-0.094c-0.049-0.106-0.11-0.207-0.196-0.293l-8-8c-0.086-0.086-0.187-0.147-0.293-0.196c-0.031-0.014-0.062-0.023-0.094-0.034c-0.084-0.028-0.169-0.045-0.257-0.051C51.041,20.011,51.021,20,51,20H37c-0.552,0-1,0.448-1,1v11h2V22h12v7c0,0.552,0.448,1,1,1h7v18H38v-8h-2v9c0,0.552,0.448,1,1,1h22c0.552,0,1-0.448,1-1V29C60,28.978,59.989,28.959,59.987,28.937z M52,28v-4.586L56.586,28H52z"/><rect x="6" y="6" style="fill:#231F20;" width="6" height="2"/><rect x="6" y="11" style="fill:#231F20;" width="17" height="2"/><rect x="6" y="20" style="fill:#231F20;" width="18" height="2"/><rect x="6" y="26" style="fill:#231F20;" width="26" height="2"/><rect x="6" y="43" style="fill:#231F20;" width="26" height="2"/><rect x="6" y="49" style="fill:#231F20;" width="26" height="2"/></g></svg>',
	                    						'description'=> __('Upload and share attachments via drive with restricted access.','vibe'),
	                    						'verify'=>array('vibe-kb/loader.php'),
                    						),
	                    		'forums'=>array(
	                    						'label'=>__('Discussion forums','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Web_Community" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M57,0H3C1.346,0,0,1.346,0,3v18h2V10h56v2h2V3C60,1.346,58.654,0,57,0z M2,8V3c0-0.551,0.449-1,1-1h54c0.551,0,1,0.449,1,1v5H2z"/><path style="fill:#231F20;" d="M58,58H2v-8H0v9c0,0.552,0.448,1,1,1h58c0.552,0,1-0.448,1-1V40h-2V58z"/><rect x="5" y="4" style="fill:#231F20;" width="4" height="2"/><rect x="12" y="4" style="fill:#231F20;" width="4" height="2"/><path style="fill:#231F20;" d="M59,14H30c-0.552,0-1,0.448-1,1v6h2v-5h27v18h-6c-0.286,0-0.558,0.122-0.748,0.335L45,41.37V35c0-0.552-0.448-1-1-1h-9v2h8v8c0,0.415,0.256,0.787,0.645,0.935C43.76,44.979,43.881,45,44,45c0.28,0,0.554-0.118,0.748-0.335L52.449,36H59c0.552,0,1-0.448,1-1V15C60,14.448,59.552,14,59,14z"/><rect x="35" y="19" style="fill:#231F20;" width="8" height="2"/><rect x="35" y="24" style="fill:#231F20;" width="19" height="2"/><rect x="35" y="29" style="fill:#231F20;" width="19" height="2"/><rect x="46" y="19" style="fill:#231F20;" width="8" height="2"/><path style="fill:#231F20;" d="M1,46h6.551l7.702,8.665C15.446,54.882,15.72,55,16,55c0.119,0,0.24-0.021,0.355-0.065C16.744,54.787,17,54.415,17,54v-8h13c0.552,0,1-0.448,1-1V25c0-0.552-0.448-1-1-1H1c-0.552,0-1,0.448-1,1v20C0,45.552,0.448,46,1,46z M2,26h27v18H16c-0.552,0-1,0.448-1,1v6.37l-6.252-7.034C8.558,44.122,8.286,44,8,44H2V26z"/><rect x="17" y="29" style="fill:#231F20;" width="8" height="2"/><rect x="6" y="34" style="fill:#231F20;" width="19" height="2"/><rect x="6" y="39" style="fill:#231F20;" width="19" height="2"/><rect x="6" y="29" style="fill:#231F20;" width="8" height="2"/><rect x="25" y="49" style="fill:#231F20;" width="5" height="2"/><rect x="33" y="49" style="fill:#231F20;" width="5" height="2"/><rect x="41" y="49" style="fill:#231F20;" width="5" height="2"/><rect x="49" y="49" style="fill:#231F20;" width="5" height="2"/><rect x="25" y="53" style="fill:#231F20;" width="5" height="2"/><rect x="33" y="53" style="fill:#231F20;" width="5" height="2"/><rect x="41" y="53" style="fill:#231F20;" width="5" height="2"/><rect x="49" y="53" style="fill:#231F20;" width="5" height="2"/><rect x="5" y="13" style="fill:#231F20;" width="5" height="2"/><rect x="13" y="13" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="13" style="fill:#231F20;" width="5" height="2"/><rect x="5" y="17" style="fill:#231F20;" width="5" height="2"/><rect x="13" y="17" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="17" style="fill:#231F20;" width="5" height="2"/></g></svg>',
	                    						'description'=> __('Discussion Forums with BBPress. Course specific forums.','vibe'),
	                    						'verify'=>array('bbpress/bbpress.php','vibe-helpdesk/loader.php'),
                    						),
	                    		'slider_layer'=>array(
	                    						'label'=>__('LayerSlider','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Responsive" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M57,0H3C1.346,0,0,1.346,0,3v37v9c0,0.552,0.448,1,1,1h20v8h-5v2h5h2h9h16c1.103,0,2-0.897,2-2v-8h9c0.552,0,1-0.448,1-1v-9V3C60,1.346,58.654,0,57,0z M2,3c0-0.551,0.449-1,1-1h54c0.551,0,1,0.449,1,1v36h-3V6c0-0.552-0.448-1-1-1H6C5.448,5,5,5.448,5,6v33H2V3z M32,36h16l0,16H32V36z M32,34v-6h16l0,6H32z M48,26H32c-1.103,0-2,0.897-2,2v11h-3H7V7h46v32h-3V28C50,26.897,49.103,26,48,26z M2,41h25h3v7H2V41z M23,58v-8h7v8H23z M32,58v-4h16.001v4H32z M50,48v-7h8v7H50z"/><rect x="38" y="30" style="fill:#231F20;" width="4" height="2"/><rect x="38" y="55" style="fill:#231F20;" width="4" height="2"/></g></svg>',
	                    						'default'=>0,
	                    						'description'=> __('[ PREMIUM ] Create unlimited slideshows in your site. Supports appealing popups.','vibe'),
	                    						'verify'=>array('layerslider/layerslider.php')
                    						),
	                    		'slider_rev'=>array(
	                    						'label'=>__('Revolution Slider','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Web_login" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M58,0H2C0.897,0,0,0.897,0,2v56c0,1.103,0.897,2,2,2h56c1.103,0,2-0.897,2-2V2C60,0.897,59.103,0,58,0z M2,58V2h56l0.001,56H2z"/><path style="fill:#231F20;" d="M53,6H7C6.448,6,6,6.448,6,7v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V7C54,6.448,53.552,6,53,6z M52,18H8V8h44V18z"/><path style="fill:#231F20;" d="M53,24H7c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V25C54,24.448,53.552,24,53,24z M52,36H8V26h44V36z"/><path style="fill:#231F20;" d="M27,42H8c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C28,42.448,27.552,42,27,42z M26,52H9v-8h17V52z"/><path style="fill:#231F20;" d="M52,42H33c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C53,42.448,52.552,42,52,42z M51,52H34v-8h17V52z"/><rect x="38" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="13" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="12" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="12" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="30" style="fill:#231F20;" width="5" height="2"/></g></svg>',
	                    						'default'=>0,
	                    						'description'=> __('[ PREMIUM ] Create unlimited slideshows in your site. Entire site builder & slideshows inside course units.','vibe'),
	                    						'verify'=>array('revslider/revslider.php')
                    						),
	                    		'vc'=>array(
	                    						'label'=>__('Visual Composer','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Web_login" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M58,0H2C0.897,0,0,0.897,0,2v56c0,1.103,0.897,2,2,2h56c1.103,0,2-0.897,2-2V2C60,0.897,59.103,0,58,0z M2,58V2h56l0.001,56H2z"/><path style="fill:#231F20;" d="M53,6H7C6.448,6,6,6.448,6,7v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V7C54,6.448,53.552,6,53,6z M52,18H8V8h44V18z"/><path style="fill:#231F20;" d="M53,24H7c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V25C54,24.448,53.552,24,53,24z M52,36H8V26h44V36z"/><path style="fill:#231F20;" d="M27,42H8c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C28,42.448,27.552,42,27,42z M26,52H9v-8h17V52z"/><path style="fill:#231F20;" d="M52,42H33c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C53,42.448,52.552,42,52,42z M51,52H34v-8h17V52z"/><rect x="38" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="13" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="12" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="12" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="30" style="fill:#231F20;" width="5" height="2"/></g></svg>',
	                    						'description'=> __('[ PREMIUM ] The top selling premium page builder for WordPress.','vibe'),
	                    						'verify'=>array('js_composer/js_composer.php')
                    						),
	                    		'elementor'=>array(
	                    						'label'=>__('Elementor','vibe'),
	                    						'default'=>1,
	                    						'icon'=>'<svg version="1.1" id="Web_login" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M58,0H2C0.897,0,0,0.897,0,2v56c0,1.103,0.897,2,2,2h56c1.103,0,2-0.897,2-2V2C60,0.897,59.103,0,58,0z M2,58V2h56l0.001,56H2z"/><path style="fill:#231F20;" d="M53,6H7C6.448,6,6,6.448,6,7v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V7C54,6.448,53.552,6,53,6z M52,18H8V8h44V18z"/><path style="fill:#231F20;" d="M53,24H7c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V25C54,24.448,53.552,24,53,24z M52,36H8V26h44V36z"/><path style="fill:#231F20;" d="M27,42H8c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C28,42.448,27.552,42,27,42z M26,52H9v-8h17V52z"/><path style="fill:#231F20;" d="M52,42H33c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C53,42.448,52.552,42,52,42z M51,52H34v-8h17V52z"/><rect x="38" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="13" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="12" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="12" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="30" style="fill:#231F20;" width="5" height="2"/></g></svg>',
	                    						'description'=> __('[ FREE ] Best modern page builder for WordPress.','vibe'),
	                    						'verify'=>array('elementor/elementor.php')
                    						),
	                    		'events'=>array(
	                    						'label'=>__('EventON','vibe'),
	                    						'icon'=>'<svg version="1.1" id="TrainingSeminar" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M8.307,20l2.757,7.352C11.21,27.741,11.583,28,12,28s0.79-0.259,0.936-0.648L15.693,20H33v3H22v2h12c0.553,0,1-0.447,1-1v-5c0-0.553-0.447-1-1-1H7c-3.859,0-7,3.141-7,7v14h2V25c0-2.757,2.243-5,5-5H8.307z M12,24.152L10.443,20h3.114L12,24.152z"/><path style="fill:#231F20;" d="M17,58h-4V40h-2v18H7V26H5v33c0,0.553,0.447,1,1,1h6h6c0.553,0,1-0.447,1-1V26h-2V58z"/><rect x="11" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="11" y="34" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M11,17h2c2.757,0,5-2.243,5-5V9c0-2.757-2.243-5-5-5h-2C8.243,4,6,6.243,6,9v3C6,14.757,8.243,17,11,17z M8,9c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V9z"/><path style="fill:#231F20;" d="M53,45H43H29c-3.859,0-7,3.141-7,7v8h2v-8c0-2.757,2.243-5,5-5h9.111C36.81,48.272,36,50.042,36,52v8h2v-8c0-2.757,2.243-5,5-5h10c2.757,0,5,2.243,5,5v8h2v-8C60,48.141,56.859,45,53,45z"/><path style="fill:#231F20;" d="M47,31c-2.757,0-5,2.243-5,5v3c0,2.757,2.243,5,5,5h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5H47z M52,36v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3v-3c0-1.654,1.346-3,3-3h2C50.654,33,52,34.346,52,36z"/><rect x="41" y="55" style="fill:#231F20;" width="2" height="5"/><rect x="53" y="55" style="fill:#231F20;" width="2" height="5"/><path style="fill:#231F20;" d="M33,31c-2.757,0-5,2.243-5,5v3c0,2.757,2.243,5,5,5h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5H33z M38,36v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3v-3c0-1.654,1.346-3,3-3h2C36.654,33,38,34.346,38,36z"/><rect x="27" y="55" style="fill:#231F20;" width="2" height="5"/><path style="fill:#231F20;" d="M59,0H1C0.447,0,0,0.447,0,1v16h2V2h56v36h-2v2h3c0.553,0,1-0.447,1-1V1C60,0.447,59.553,0,59,0z"/><rect x="21" y="38" style="fill:#231F20;" width="5" height="2"/><rect x="29" y="5" style="fill:#231F20;" width="26" height="2"/><rect x="29" y="10" style="fill:#231F20;" width="26" height="2"/><rect x="43" y="15" style="fill:#231F20;" width="12" height="2"/><rect x="43" y="20" style="fill:#231F20;" width="12" height="2"/><rect x="53" y="25" style="fill:#231F20;" width="2" height="2"/><rect x="49" y="25" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> __('[ Premium ] Physical events with Google maps. Uses EventOn events management.','vibe'),
	                    						'verify'=>array('eventON/eventon.php')
                    						),
	                    		
	                    		'points'=>array(
	                    						'label'=>__('Gamification & Points','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Game_Design" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 58" style="enable-background:new 0 0 60 58;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,4H1C0.448,4,0,4.448,0,5v42c0,0.552,0.448,1,1,1h21v8h-3v2h4h14h4v-2h-3v-8h21c0.552,0,1-0.448,1-1V5C60,4.448,59.552,4,59,4z M36,56H24v-8h12V56z M58,46H37H23H2V6h56V46z"/><path style="fill:#231F20;" d="M5,44h50c0.552,0,1-0.448,1-1V9c0-0.552-0.448-1-1-1H5C4.448,8,4,8.448,4,9v34C4,43.552,4.448,44,5,44z M6,10h48v32H6V10z"/><path style="fill:#231F20;" d="M20.8,13h-3.6C12.679,13,9,16.679,9,21.2V39h2V21.2c0-3.419,2.781-6.2,6.2-6.2h3.6c3.419,0,6.2,2.781,6.2,6.2V39h2V21.2C29,16.679,25.321,13,20.8,13z"/><rect x="13" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="16" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="20" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="23" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="21" style="fill:#231F20;" width="4" height="2"/><rect x="21" y="21" style="fill:#231F20;" width="4" height="2"/><path style="fill:#231F20;" d="M42.8,13h-3.6c-4.521,0-8.2,3.679-8.2,8.2V25h2v-3.8c0-3.419,2.781-6.2,6.2-6.2h3.6c3.419,0,6.2,2.781,6.2,6.2V28h-9c-0.552,0-1,0.448-1,1v3h2v-2h8v9h2V21.2C51,16.679,47.321,13,42.8,13z"/><rect x="31" y="35" style="fill:#231F20;" width="2" height="4"/><rect x="35" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="38" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="42" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="45" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="35" y="21" style="fill:#231F20;" width="4" height="2"/><rect x="43" y="21" style="fill:#231F20;" width="4" height="2"/><path style="fill:#231F20;" d="M23,31h-2v1h-3v2h4c0.552,0,1-0.448,1-1V31z"/><path style="fill:#231F20;" d="M31,29v3h2v-2h4v-2h-5C31.448,28,31,28.448,31,29z"/><rect x="1" style="fill:#231F20;" width="2" height="2"/><rect x="5" style="fill:#231F20;" width="2" height="2"/><rect x="9" style="fill:#231F20;" width="2" height="2"/><rect x="13" style="fill:#231F20;" width="2" height="2"/><rect x="17" style="fill:#231F20;" width="2" height="2"/><rect x="21" style="fill:#231F20;" width="2" height="2"/><rect x="25" style="fill:#231F20;" width="2" height="2"/><rect x="29" style="fill:#231F20;" width="2" height="2"/><rect x="33" style="fill:#231F20;" width="2" height="2"/><rect x="37" style="fill:#231F20;" width="2" height="2"/><rect x="41" style="fill:#231F20;" width="2" height="2"/><rect x="45" style="fill:#231F20;" width="2" height="2"/><rect x="49" style="fill:#231F20;" width="2" height="2"/><rect x="53" style="fill:#231F20;" width="2" height="2"/><rect x="57" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> __('Points system for the site. Gamify your site.','vibe'),
	                    						'verify'=>array('mycred/mycred.php','wplms-mycred-addon/wplms-mycred-addon.php'),
                    						),
	                    		'memberships'=>array(
	                    						'label'=>__('Memberships','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Multiplayer" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M7,16h4v4h2v-4h4c2.757,0,5,2.243,5,5v4h2v-4c0-3.86-3.14-7-7-7H7c-3.86,0-7,3.14-7,7v14h2v-5h3v5h2V24H5v4H2v-7C2,18.243,4.243,16,7,16z"/><path style="fill:#231F20;" d="M11,13h2c2.757,0,5-2.243,5-5V5c0-2.757-2.243-5-5-5h-2C8.243,0,6,2.243,6,5v3C6,10.757,8.243,13,11,13z M13,11h-2c-1.654,0-3-1.346-3-3V6h8v2C16,9.654,14.654,11,13,11z M11,2h2c1.302,0,2.401,0.838,2.816,2H8.184C8.599,2.838,9.698,2,11,2z"/><rect x="17" y="24" style="fill:#231F20;" width="2" height="11"/><rect x="9" y="25" style="fill:#231F20;" width="6" height="2"/><rect x="9" y="29" style="fill:#231F20;" width="6" height="2"/><rect x="19" y="5" style="fill:#231F20;" width="2" height="4"/><rect x="3" y="5" style="fill:#231F20;" width="2" height="4"/><path style="fill:#231F20;" d="M35,39H25c-3.86,0-7,3.14-7,7v14h2v-5h3v5h2V49h-2v4h-3v-7c0-2.757,2.243-5,5-5h4v4h2v-4h4c2.757,0,5,2.243,5,5v7h-3v-4h-2v11h2v-5h3v5h2V46C42,42.14,38.86,39,35,39z"/><path style="fill:#231F20;" d="M24,33c0,2.757,2.243,5,5,5h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5V33z M31,36h-2c-1.654,0-3-1.346-3-3v-2h8v2C34,34.654,32.654,36,31,36z M29,27h2c1.302,0,2.401,0.838,2.816,2h-7.632C26.599,27.838,27.698,27,29,27z"/><rect x="27" y="50" style="fill:#231F20;" width="6" height="2"/><rect x="27" y="54" style="fill:#231F20;" width="6" height="2"/><path style="fill:#231F20;" d="M53,14H43c-3.86,0-7,3.14-7,7v4h2v-4c0-2.757,2.243-5,5-5h4v4h2v-4h4c2.757,0,5,2.243,5,5v7h-3v-4h-2v11h2v-5h3v5h2V21C60,17.14,56.86,14,53,14z"/><path style="fill:#231F20;" d="M47,13h2c2.757,0,5-2.243,5-5V5c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5v3C42,10.757,44.243,13,47,13z M49,11h-2c-1.654,0-3-1.346-3-3V6h8v2C52,9.654,50.654,11,49,11z M47,2h2c1.302,0,2.401,0.838,2.816,2h-7.632C44.599,2.838,45.698,2,47,2z"/><rect x="41" y="24" style="fill:#231F20;" width="2" height="11"/><rect x="45" y="25" style="fill:#231F20;" width="6" height="2"/><rect x="45" y="29" style="fill:#231F20;" width="6" height="2"/><rect x="55" y="5" style="fill:#231F20;" width="2" height="4"/><rect x="39" y="5" style="fill:#231F20;" width="2" height="4"/></g></svg>',
	                    						'description'=> __('Sell courses via memberships. Paid Memberships Pro.','vibe'),
	                    						'verify'=>array('paid-memberships-pro/paid-memberships-pro.php')
                    						),
	                    		'multiinstructor'=>array(
	                    						'label'=>__('Multiple Instructors per course','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Online_Game_Play" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M17,39H7c-3.86,0-7,3.14-7,7v14h2v-5h3v5h2V49H5v4H2v-7c0-2.757,2.243-5,5-5h4v4h2v-4h4c2.757,0,5,2.243,5,5v7h-3v-4h-2v11h2v-5h3v5h2V46C24,42.14,20.86,39,17,39z"/><path style="fill:#231F20;" d="M13,25h-2c-2.757,0-5,2.243-5,5v3c0,2.757,2.243,5,5,5h2c2.757,0,5-2.243,5-5v-3C18,27.243,15.757,25,13,25z M11,27h2c1.302,0,2.401,0.838,2.816,2H8.184C8.599,27.838,9.698,27,11,27z M13,36h-2c-1.654,0-3-1.346-3-3v-2h8v2C16,34.654,14.654,36,13,36z"/><rect x="9" y="50" style="fill:#231F20;" width="6" height="2"/><rect x="9" y="54" style="fill:#231F20;" width="6" height="2"/><rect x="3" y="30" style="fill:#231F20;" width="2" height="4"/><path style="fill:#231F20;" d="M53,39H43c-3.86,0-7,3.14-7,7v14h2v-5h3v5h2V49h-2v4h-3v-7c0-2.757,2.243-5,5-5h4v4h2v-4h4c2.757,0,5,2.243,5,5v7h-3v-4h-2v11h2v-5h3v5h2V46C60,42.14,56.86,39,53,39z"/><path style="fill:#231F20;" d="M42,33c0,2.757,2.243,5,5,5h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5V33z M49,36h-2c-1.654,0-3-1.346-3-3v-2h8v2C52,34.654,50.654,36,49,36z M47,27h2c1.302,0,2.401,0.838,2.816,2h-7.632C44.599,27.838,45.698,27,47,27z"/><rect x="45" y="50" style="fill:#231F20;" width="6" height="2"/><rect x="45" y="54" style="fill:#231F20;" width="6" height="2"/><rect x="55" y="30" style="fill:#231F20;" width="2" height="4"/><path style="fill:#231F20;" d="M35,16c2.757,0,5,2.243,5,5v4h2v-4c0-3.86-3.14-7-7-7H25c-3.86,0-7,3.14-7,7v4h2v-4c0-2.757,2.243-5,5-5h4v4h2v-4H35z"/><path style="fill:#231F20;" d="M29,13h2c2.757,0,5-2.243,5-5V5c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5v3C24,10.757,26.243,13,29,13z M31,11h-2c-1.654,0-3-1.346-3-3V6h8v2C34,9.654,32.654,11,31,11z M29,2h2c1.302,0,2.401,0.838,2.816,2h-7.632C26.599,2.838,27.698,2,29,2z"/><rect x="23" y="22" style="fill:#231F20;" width="2" height="3"/><rect x="35" y="22" style="fill:#231F20;" width="2" height="3"/><rect x="37" y="5" style="fill:#231F20;" width="2" height="4"/><rect x="21" y="5" style="fill:#231F20;" width="2" height="4"/><rect x="29" y="31" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="35" style="fill:#231F20;" width="2" height="2"/><rect x="25" y="35" style="fill:#231F20;" width="2" height="2"/><rect x="21" y="35" style="fill:#231F20;" width="2" height="2"/><rect x="33" y="35" style="fill:#231F20;" width="2" height="2"/><rect x="37" y="35" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="39" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="43" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="47" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="51" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="55" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="27" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="23" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M59,2H43c-0.552,0-1,0.448-1,1v11c0,0.552,0.448,1,1,1h11.254l3.987,4.651C58.435,19.877,58.714,20,59,20c0.116,0,0.234-0.02,0.347-0.062C59.739,19.793,60,19.419,60,19V3C60,2.448,59.552,2,59,2z M58,16.297l-2.527-2.948C55.283,13.127,55.006,13,54.714,13H44V4h14V16.297z"/><path style="fill:#231F20;" d="M0.653,19.938C0.766,19.98,0.884,20,1,20c0.286,0,0.565-0.123,0.759-0.349L5.746,15H17c0.552,0,1-0.448,1-1V3c0-0.552-0.448-1-1-1H1C0.448,2,0,2.448,0,3v16C0,19.419,0.261,19.793,0.653,19.938z M2,4h14v9H5.286c-0.292,0-0.569,0.127-0.759,0.349L2,16.297V4z"/><rect x="46" y="6" style="fill:#231F20;" width="10" height="2"/><rect x="46" y="9" style="fill:#231F20;" width="10" height="2"/><rect x="4" y="6" style="fill:#231F20;" width="10" height="2"/><rect x="4" y="9" style="fill:#231F20;" width="10" height="2"/></g></svg>',
	                    						'description'=> __('Set more than one instructor per course.  ','vibe'),
	                    						'verify'=>array('co-authors-plus/co-authors-plus.php','WPLMS-Coauthors-Plus/wplms-coauthor-plus.php')
                    						),
	                    		'zoom'=>array(
	                    						'label'=>__('Zoom Conferencing','vibe'),
	                    						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" height="60" width="90" viewBox="-12.7143 -4.762 110.1906 28.572"><path fill-rule="evenodd" fill="#231F20" d="M69.012 5.712c.324.559.43 1.195.465 1.91l.046.953v6.664l.047.954c.094 1.558 1.243 2.71 2.813 2.808l.949.047V8.575l.047-.953c.039-.707.144-1.355.473-1.918a3.806 3.806 0 016.59.012c.324.559.425 1.207.464 1.906l.047.95v6.667l.047.954c.098 1.566 1.238 2.718 2.813 2.808l.949.047V7.622a7.62 7.62 0 00-7.617-7.62 7.6 7.6 0 00-5.715 2.581A7.61 7.61 0 0065.715.001c-1.582 0-3.05.48-4.266 1.309C60.707.482 59.047.001 58.094.001v19.047l.953-.047c1.594-.105 2.746-1.226 2.808-2.808l.051-.954V8.575l.047-.953c.04-.719.14-1.351.465-1.914a3.816 3.816 0 013.297-1.898 3.81 3.81 0 013.297 1.902zM3.809 19.002l.953.046h14.285l-.047-.95c-.129-1.566-1.238-2.71-2.809-2.812l-.953-.047h-8.57l11.426-11.43-.047-.949c-.074-1.582-1.23-2.725-2.809-2.812l-.953-.043L0 .001l.047.953c.125 1.551 1.25 2.719 2.808 2.809l.954.047h8.57L.953 15.24l.047.953c.094 1.57 1.227 2.707 2.809 2.808zM54.355 2.789a9.523 9.523 0 010 13.469 9.53 9.53 0 01-13.472 0c-3.719-3.719-3.719-9.75 0-13.469A9.518 9.518 0 0147.613 0a9.525 9.525 0 016.742 2.79zM51.66 5.486a5.717 5.717 0 010 8.082 5.717 5.717 0 01-8.082 0 5.717 5.717 0 010-8.082 5.717 5.717 0 018.082 0zM27.625 0a9.518 9.518 0 016.73 2.79c3.72 3.718 3.72 9.75 0 13.468a9.53 9.53 0 01-13.472 0c-3.719-3.719-3.719-9.75 0-13.469A9.518 9.518 0 0127.613 0zm4.035 5.484a5.717 5.717 0 010 8.083 5.717 5.717 0 01-8.082 0 5.717 5.717 0 010-8.082 5.717 5.717 0 018.082 0z"/></svg>',
	                    						'description'=> __('Enable Video conferencing with Zoom.','vibe'),
	                    						'link'=>'https://www.youtube.com/watch?v=UPCNJwAG2JI&t=8s',
	                    						'verify'=>array('vibe-zoom/vibe-zoom.php')
                    						),
	                    		'bbb'=>array(
	                    						'label'=>__('Video Conferencing','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Pc_Game" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,0H1C0.448,0,0,0.448,0,1v36c0,0.552,0.448,1,1,1h22v4h-2v2h3h12h3v-2h-2v-4h22c0.552,0,1-0.448,1-1V1C60,0.448,59.552,0,59,0z M35,42H25v-4h10V42z M58,36H36H24H2V2h56V36z"/><path style="fill:#231F20;" d="M5,34h4h42h4c0.552,0,1-0.448,1-1V5c0-0.552-0.448-1-1-1H5C4.448,4,4,4.448,4,5v28C4,33.552,4.448,34,5,34z M39,30v2h-8v-2H39z M29,32h-8v-2h8V32z M19,32h-9v-2h9V32z M41,32v-2h9v2H41z M6,6h48v26h-2v-3c0-0.552-0.448-1-1-1H9c-0.552,0-1,0.448-1,1v3H6V6z"/><path style="fill:#231F20;" d="M57,47h-8c-0.552,0-1,0.448-1,1v11c0,0.552,0.448,1,1,1h8c0.552,0,1-0.448,1-1V48C58,47.448,57.552,47,57,47z M56,58h-6v-9h2v3h2v-3h2V58z"/><path style="fill:#231F20;" d="M42,46H4c-1.103,0-2,0.897-2,2v10c0,1.103,0.897,2,2,2h38c1.103,0,2-0.897,2-2V48C44,46.897,43.103,46,42,46z M4,58V48h38l0.001,10H4z"/><rect x="6" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="10" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="14" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="18" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="22" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="26" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="30" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="34" y="50" style="fill:#231F20;" width="2" height="2"/><polygon style="fill:#231F20;" points="38,54 34,54 34,56 38,56 40,56 40,54 40,50 38,50 "/><rect x="6" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="10" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="14" y="54" style="fill:#231F20;" width="14" height="2"/><rect x="30" y="54" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M54,46v-3c0-1.206-0.799-3-3-3h-5v2h5c0.805,0,0.988,0.55,1,1v3H54z"/><rect x="8" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="12" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="16" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="8" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="12" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="16" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="20" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="42" y="8" style="fill:#231F20;" width="10" height="2"/><path style="fill:#231F20;" d="M22,24c2.206,0,4-1.794,4-4c0-2.206-1.794-4-4-4c-2.206,0-4,1.794-4,4C18,22.206,19.794,24,22,24z M22,18c1.103,0,2,0.897,2,2s-0.897,2-2,2s-2-0.897-2-2S20.897,18,22,18z"/><path style="fill:#231F20;" d="M38,25c3.309,0,6-2.691,6-6c0-3.309-2.691-6-6-6c-3.309,0-6,2.691-6,6C32,22.309,34.691,25,38,25z M38,15c2.206,0,4,1.794,4,4c0,2.206-1.794,4-4,4c-1.858,0-3.411-1.28-3.858-3H36v-2h-1.858C34.589,16.28,36.142,15,38,15z"/><rect x="38" y="19" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> __('Enable Video conferencing with BigBlueButton.','vibe'),
	                    						'link'=>'https://www.youtube.com/watch?v=kmIa8kfTxjA&t=15s',
	                    						'verify'=>array('bigbluebutton/bigbluebutton.php','vibe-bbb/vibe-bbb.php')
                    						),
	                    		'h5p'=>array(
	                    						'label'=>__('H5P Interactive Learning','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Graphic_Design" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M57,0H3C1.346,0,0,1.346,0,3v37v9c0,0.552,0.448,1,1,1h20v10h2V50h14v10h2V50h20c0.553,0,1-0.448,1-1v-9V3C60,1.346,58.654,0,57,0z M2,3c0-0.551,0.449-1,1-1h54c0.552,0,1,0.449,1,1v36H2V3z M58,48H38H22H2v-7h56V48z"/><path style="fill:#231F20;" d="M52,8h-6c-0.553,0-1,0.448-1,1v2H34V9c0-0.552-0.448-1-1-1h-6c-0.552,0-1,0.448-1,1v2H15V9c0-0.552-0.448-1-1-1H8C7.448,8,7,8.448,7,9v6c0,0.552,0.448,1,1,1h6c0.552,0,1-0.448,1-1v-2h6.356C19.312,14.651,18,17.174,18,20v6h-2c-0.552,0-1,0.448-1,1v6c0,0.552,0.448,1,1,1h6c0.552,0,1-0.448,1-1v-6c0-0.552-0.448-1-1-1h-2v-6c0-3.52,2.613-6.433,6-6.92V15c0,0.552,0.448,1,1,1h6c0.552,0,1-0.448,1-1v-1.92c3.387,0.487,6,3.4,6,6.92v6h-2c-0.552,0-1,0.448-1,1v6c0,0.552,0.448,1,1,1h6c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1h-2v-6c0-2.826-1.312-5.349-3.356-7H45v2c0,0.552,0.447,1,1,1h6c0.553,0,1-0.448,1-1V9C53,8.448,52.553,8,52,8z M13,14H9v-4h4V14z M21,32h-4v-4h4V32z M32,14h-4v-4h4V14z M43,32h-4v-4h4V32z M51,14h-4v-4h4V14z"/><rect x="29" y="11" style="fill:#231F20;" width="2" height="2"/><rect x="48" y="11" style="fill:#231F20;" width="2" height="2"/><rect x="10" y="11" style="fill:#231F20;" width="2" height="2"/><rect x="18" y="29" style="fill:#231F20;" width="2" height="2"/><rect x="40" y="29" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> 'Enable Interactive learning with H5P.',
	                    						'link'=>'https://www.youtube.com/watch?v=Wbby9lIi3AQ',
	                    						'verify'=>array('h5p/h5p.php','wplms-h5p/wplms-h5p.php')
                    						),
	                    		'wishlists'=>array(
	                    						'label'=>__('WishLists & Collections','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Gallery" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,0H1C0.448,0,0,0.448,0,1v58c0,0.552,0.448,1,1,1h58c0.552,0,1-0.448,1-1V1C60,0.448,59.552,0,59,0z M58,2v6H2V2H58z M2,58V10h56v48H2z"/><rect x="4" y="4" style="fill:#231F20;" width="4" height="2"/><rect x="10" y="4" style="fill:#231F20;" width="4" height="2"/><rect x="54" y="4" style="fill:#231F20;" width="2" height="2"/><rect x="50" y="4" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M54,13H42c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h12c0.552,0,1-0.448,1-1V14C55,13.448,54.552,13,54,13z M53,25H43V15h10V25z"/><path style="fill:#231F20;" d="M54,36H42c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h12c0.552,0,1-0.448,1-1V37C55,36.448,54.552,36,54,36z M53,48H43V38h10V48z"/><path style="fill:#231F20;" d="M36,36H24c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h12c0.552,0,1-0.448,1-1V37C37,36.448,36.552,36,36,36z M35,48H25V38h10V48z"/><path style="fill:#231F20;" d="M36,13H24c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h12c0.552,0,1-0.448,1-1V14C37,13.448,36.552,13,36,13z M35,25H25V15h10V25z"/><rect x="23" y="30" style="fill:#231F20;" width="4" height="2"/><rect x="29" y="30" style="fill:#231F20;" width="8" height="2"/><rect x="41" y="30" style="fill:#231F20;" width="4" height="2"/><rect x="47" y="30" style="fill:#231F20;" width="8" height="2"/><rect x="41" y="53" style="fill:#231F20;" width="4" height="2"/><rect x="47" y="53" style="fill:#231F20;" width="8" height="2"/><rect x="23" y="53" style="fill:#231F20;" width="4" height="2"/><rect x="29" y="53" style="fill:#231F20;" width="8" height="2"/><rect x="5" y="13" style="fill:#231F20;" width="6" height="2"/><rect x="5" y="17" style="fill:#231F20;" width="14" height="2"/><rect x="5" y="40" style="fill:#231F20;" width="6" height="2"/><rect x="5" y="44" style="fill:#231F20;" width="14" height="2"/><rect x="5" y="22" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="22" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="22" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="22" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="34" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="34" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="34" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="34" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="49" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="49" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="49" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="49" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="53" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="53" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="53" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="53" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> 'Add to Wishlist and add to public collection for courses. Price <del>$19</del> <strong style="color:#57d657;">FREE</strong>',
	                    						'link'=>'https://www.youtube.com/watch?v=Wbby9lIi3AQ',
	                    						'verify'=>array('wplms-batches/loader.php')
                    						),
	                    		'batches'=>array(
	                    						'label'=>__('Classes/Batches *','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Team_Skills" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M17,43H7c-3.859,0-7,3.141-7,7v10h2V50c0-2.757,2.243-5,5-5h1.307l2.757,7.352C11.21,52.741,11.583,53,12,53s0.79-0.259,0.936-0.648L15.693,45H17c2.757,0,5,2.243,5,5v10h2V50C24,46.141,20.859,43,17,43z M12,49.152L10.443,45h3.114L12,49.152z"/><rect x="11" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="11" y="58" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M6,34v3c0,2.757,2.243,5,5,5h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5h-2C8.243,29,6,31.243,6,34z M8,34c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V34z"/><rect x="5" y="53" style="fill:#231F20;" width="2" height="7"/><rect x="17" y="53" style="fill:#231F20;" width="2" height="7"/><path style="fill:#231F20;" d="M35,43H25v2h1.307l2.757,7.352C29.21,52.741,29.583,53,30,53s0.79-0.259,0.937-0.648L33.693,45H35c2.757,0,5,2.243,5,5v10h2V50C42,46.141,38.859,43,35,43z M30,49.152L28.443,45h3.114L30,49.152z"/><rect x="29" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="58" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M29,42h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5v3C24,39.757,26.243,42,29,42z M26,34c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V34z"/><rect x="35" y="53" style="fill:#231F20;" width="2" height="7"/><path style="fill:#231F20;" d="M53,43H43v2h1.307l2.757,7.352C47.21,52.741,47.583,53,48,53s0.79-0.259,0.937-0.648L51.693,45H53c2.757,0,5,2.243,5,5v10h2V50C60,46.141,56.859,43,53,43z M48,49.152L46.443,45h3.114L48,49.152z"/><rect x="47" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="47" y="58" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M47,42h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5v3C42,39.757,44.243,42,47,42z M44,34c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V34z"/><rect x="53" y="53" style="fill:#231F20;" width="2" height="7"/><path style="fill:#231F20;" d="M30,2c13.785,0,25,11.215,25,25h2C57,12.112,44.888,0,30,0S3,12.112,3,27h2C5,13.215,16.215,2,30,2z"/><path style="fill:#231F20;" d="M37.538,15.201l-1.078,1.684C39.929,19.105,42,22.887,42,27h2C44,22.201,41.584,17.79,37.538,15.201z"/><path style="fill:#231F20;" d="M23.539,16.886l-1.078-1.684C18.415,17.792,16,22.203,16,27h2C18,22.888,20.07,19.107,23.539,16.886z"/><path style="fill:#231F20;" d="M27.707,22.707l7-7c0.286-0.286,0.372-0.716,0.217-1.09C34.77,14.243,34.404,14,34,14h-5.586l5.293-5.293l-1.414-1.414l-7,7c-0.286,0.286-0.372,0.716-0.217,1.09C25.23,15.757,25.596,16,26,16h5.586l-5.293,5.293L27.707,22.707z"/><path style="fill:#231F20;" d="M51,27c0-6.668-3.061-12.791-8.397-16.8l-1.201,1.6C46.23,15.427,49,20.967,49,27H51z"/><path style="fill:#231F20;" d="M18.601,11.798l-1.201-1.6C12.061,14.208,9,20.332,9,27h2C11,20.967,13.771,15.426,18.601,11.798z"/></g></svg>',
	                    						'description'=> 'Classes Addon. Custom Start dates, Seats, Courses, Timetable for WPLMS. <strong style="color:#57d657">Price $29</strong> | 30 Day Free Trial',
	                    						'link'=>'https://wplms.io/support/article-categories/wplms-batches/',
	                    						'verify'=>array('wplms-batches/loader.php')
                    						),
	                    		'attendance'=>array(
	                    						'label'=>__('Attendance *','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Class_Timetable" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><rect x="18" y="1" style="fill:#231F20;" width="7" height="2"/><rect x="36" y="1" style="fill:#231F20;" width="6" height="2"/><path style="fill:#231F20;" d="M59,1h-6v2h5v15H2V3h5V1H1C0.447,1,0,1.448,0,2v17v40c0,0.552,0.447,1,1,1h58c0.553,0,1-0.448,1-1V19V2C60,1.448,59.553,1,59,1z M58,58H2V20h56V58z"/><path style="fill:#231F20;" d="M30,14h1c1.654,0,3-1.346,3-3V3c0-1.654-1.346-3-3-3h-1c-1.654,0-3,1.346-3,3v8C27,12.654,28.346,14,30,14z M29,3c0-0.551,0.448-1,1-1h1c0.552,0,1,0.449,1,1v8c0,0.551-0.448,1-1,1h-1c-0.552,0-1-0.449-1-1V3z"/><path style="fill:#231F20;" d="M47,14h1c1.654,0,3-1.346,3-3V3c0-1.654-1.346-3-3-3h-1c-1.654,0-3,1.346-3,3v8C44,12.654,45.346,14,47,14z M46,3c0-0.551,0.448-1,1-1h1c0.552,0,1,0.449,1,1v8c0,0.551-0.448,1-1,1h-1c-0.552,0-1-0.449-1-1V3z"/><path style="fill:#231F20;" d="M12,14h1c1.654,0,3-1.346,3-3V3c0-1.654-1.346-3-3-3h-1c-1.654,0-3,1.346-3,3v8C9,12.654,10.346,14,12,14z M11,3c0-0.551,0.448-1,1-1h1c0.552,0,1,0.449,1,1v8c0,0.551-0.448,1-1,1h-1c-0.552,0-1-0.449-1-1V3z"/><path style="fill:#231F20;" d="M7,32h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1H7c-0.553,0-1,0.448-1,1v6C6,31.552,6.447,32,7,32z M8,26h5v4H8V26z"/><path style="fill:#231F20;" d="M20,32h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1h-7c-0.553,0-1,0.448-1,1v6C19,31.552,19.447,32,20,32z M21,26h5v4h-5V26z"/><path style="fill:#231F20;" d="M39,31h2v-6c0-0.552-0.447-1-1-1h-7v2h6V31z"/><path style="fill:#231F20;" d="M46,32h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1h-7c-0.553,0-1,0.448-1,1v6C45,31.552,45.447,32,46,32z M47,26h5v4h-5V26z"/><path style="fill:#231F20;" d="M14,35H7v2h6v5h2v-6C15,35.448,14.553,35,14,35z"/><path style="fill:#231F20;" d="M20,43h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1h-7c-0.553,0-1,0.448-1,1v6C19,42.552,19.447,43,20,43z M21,37h5v4h-5V37z"/><path style="fill:#231F20;" d="M33,43h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1h-7c-0.553,0-1,0.448-1,1v6C32,42.552,32.447,43,33,43z M34,37h5v4h-5V37z"/><path style="fill:#231F20;" d="M46,43h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1h-7c-0.553,0-1,0.448-1,1v6C45,42.552,45.447,43,46,43z M47,37h5v4h-5V37z"/><path style="fill:#231F20;" d="M7,54h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1H7c-0.553,0-1,0.448-1,1v6C6,53.552,6.447,54,7,54z M8,48h5v4H8V48z"/><path style="fill:#231F20;" d="M27,46h-7v2h6v5h2v-6C28,46.448,27.553,46,27,46z"/><path style="fill:#231F20;" d="M33,54h7c0.553,0,1-0.448,1-1v-6c0-0.552-0.447-1-1-1h-7c-0.553,0-1,0.448-1,1v6C32,53.552,32.447,54,33,54z M34,48h5v4h-5V48z"/><path style="fill:#231F20;" d="M53,46h-7v2h6v5h2v-6C54,46.448,53.553,46,53,46z"/></g></svg>',
	                    						'description'=> 'Student Attendance and tracking for WPLMS. <strong style="color:#57d657">Price $29</strong> | 30 Day Free Trial',
	                    						'link'=>'https://wplms.io/downloads/wplms-attendance/',
	                    						'verify'=>array('wplms-attendance/loader.php'),
	                    					),
	                    		'appointments'=>array(
	                    						'label'=>__('Instructor Booking *','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Event_Process" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,4h-7V1c0-0.553-0.448-1-1-1h-4c-0.552,0-1,0.447-1,1v3H33V1c0-0.553-0.448-1-1-1h-4c-0.552,0-1,0.447-1,1v3H14V1c0-0.553-0.448-1-1-1H9C8.448,0,8,0.447,8,1v3H1C0.448,4,0,4.447,0,5v16v38c0,0.553,0.448,1,1,1h32v-2H2V22h56v11h2V21V5C60,4.447,59.552,4,59,4z M48,2h2v8h-2V2z M29,2h2v8h-2V2z M10,2h2v8h-2V2z M2,20V6h6v5c0,0.553,0.448,1,1,1h4c0.552,0,1-0.447,1-1V6h13v5c0,0.553,0.448,1,1,1h4c0.552,0,1-0.447,1-1V6h13v5c0,0.553,0.448,1,1,1h4c0.552,0,1-0.447,1-1V6h6v14H2z"/><path style="fill:#231F20;" d="M59,42h-2.785c-0.054-0.13-0.11-0.266-0.164-0.396l1.969-1.969c0.391-0.391,0.391-1.023,0-1.414l-4.242-4.242c-0.391-0.391-1.023-0.391-1.414,0l-1.969,1.969c-0.13-0.054-0.265-0.11-0.395-0.163V33c0-0.553-0.448-1-1-1h-6c-0.552,0-1,0.447-1,1v2.785c-0.13,0.053-0.265,0.109-0.395,0.163l-1.969-1.969c-0.391-0.391-1.023-0.391-1.414,0l-4.242,4.242c-0.391,0.391-0.391,1.023,0,1.414l1.969,1.969c-0.054,0.13-0.11,0.266-0.164,0.396H33c-0.552,0-1,0.447-1,1v6c0,0.553,0.448,1,1,1h2.785c0.053,0.13,0.109,0.266,0.163,0.396l-1.969,1.969c-0.391,0.391-0.391,1.023,0,1.414l4.242,4.242c0.391,0.391,1.023,0.391,1.414,0l1.969-1.969c0.13,0.054,0.265,0.11,0.395,0.163V59c0,0.553,0.448,1,1,1h6c0.552,0,1-0.447,1-1v-2.785c0.13-0.053,0.265-0.109,0.395-0.163l1.969,1.969c0.391,0.391,1.023,0.391,1.414,0l4.242-4.242c0.391-0.391,0.391-1.023,0-1.414l-1.969-1.969c0.054-0.13,0.11-0.266,0.163-0.396H59c0.552,0,1-0.447,1-1v-6C60,42.447,59.552,42,59,42z M58,48h-2.459c-0.417,0-0.796,0.274-0.942,0.665c-0.059,0.161-0.554,1.357-0.613,1.487c-0.208,0.389-0.137,0.868,0.174,1.18l1.739,1.739l-2.828,2.828l-1.739-1.739c-0.297-0.296-0.762-0.37-1.142-0.193c-0.165,0.076-1.364,0.572-1.497,0.622C48.28,54.723,48,55.106,48,55.541V58h-4v-2.459c0-0.418-0.274-0.797-0.666-0.942c-0.161-0.059-1.36-0.556-1.492-0.615c-0.387-0.206-0.865-0.134-1.175,0.177l-1.739,1.739l-2.828-2.828l1.739-1.739c0.296-0.296,0.369-0.763,0.193-1.144c-0.077-0.166-0.572-1.362-0.622-1.495C37.278,48.28,36.893,48,36.459,48H34v-4h2.459c0.417,0,0.796-0.274,0.942-0.665c0.06-0.161,0.558-1.364,0.618-1.496c0.203-0.388,0.13-0.862-0.179-1.171l-1.739-1.739l2.828-2.828l1.739,1.739c0.296,0.297,0.763,0.369,1.142,0.193c0.165-0.076,1.364-0.572,1.497-0.622C43.72,37.277,44,36.894,44,36.459V34h4v2.459c0,0.418,0.274,0.797,0.666,0.942c0.161,0.059,1.36,0.556,1.492,0.615c0.388,0.205,0.864,0.134,1.175-0.177l1.739-1.739l2.828,2.828l-1.739,1.739c-0.296,0.296-0.369,0.76-0.194,1.141c0.075,0.162,0.572,1.365,0.623,1.498C54.722,43.72,55.107,44,55.541,44H58V48z"/><path style="fill:#231F20;" d="M46,42c-2.206,0-4,1.794-4,4s1.794,4,4,4s4-1.794,4-4S48.206,42,46,42z M46,48c-1.103,0-2-0.897-2-2s0.897-2,2-2s2,0.897,2,2S47.103,48,46,48z"/><path style="fill:#231F20;" d="M17,26c0-0.553-0.448-1-1-1H8c-0.552,0-1,0.447-1,1v8c0,0.553,0.448,1,1,1h8c0.552,0,1-0.447,1-1V26z M15,33H9v-6h6V33z"/><path style="fill:#231F20;" d="M29,26c0-0.553-0.448-1-1-1h-8c-0.552,0-1,0.447-1,1v8c0,0.553,0.448,1,1,1h8c0.552,0,1-0.447,1-1V26z M27,33h-6v-6h6V33z"/><path style="fill:#231F20;" d="M17,50v-8c0-0.553-0.448-1-1-1H8c-0.552,0-1,0.447-1,1v8c0,0.553,0.448,1,1,1h8C16.552,51,17,50.553,17,50z M15,49H9v-6h6V49z"/><path style="fill:#231F20;" d="M29,50v-8c0-0.553-0.448-1-1-1h-8c-0.552,0-1,0.447-1,1v8c0,0.553,0.448,1,1,1h8C28.552,51,29,50.553,29,50z M27,49h-6v-6h6V49z"/><path style="fill:#231F20;" d="M41,30v-4c0-0.553-0.448-1-1-1h-8c-0.552,0-1,0.447-1,1v8c0,0.553,0.448,1,1,1h3v-2h-2v-6h6v3H41z"/><path style="fill:#231F20;" d="M53,32v-6c0-0.553-0.448-1-1-1h-8c-0.552,0-1,0.447-1,1v4h2v-3h6v5H53z"/><rect x="7" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="11" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="19" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="23" y="37" style="fill:#231F20;" width="2" height="2"/><rect x="7" y="53" style="fill:#231F20;" width="2" height="2"/><rect x="11" y="53" style="fill:#231F20;" width="2" height="2"/><rect x="19" y="53" style="fill:#231F20;" width="2" height="2"/><rect x="23" y="53" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> 'Calendar Booking Appointments for WPLMS. <strong style="color:#57d657">Price $39</strong> | 30 Day Free Trial',
	                    						'link'=>'https://wplms.io/downloads/vibe-appointments/',
	                    						'verify'=>array('vibe-appointments/loader.php'),
                    						),
										'parents'=>array(
	                    						'label'=>__('Parents *','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Focus_Group" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M18,23h2v-2c0-2.757,2.243-5,5-5h1.307l2.757,7.351C29.21,23.741,29.583,24,30,24s0.79-0.259,0.937-0.649L33.693,16H35c2.757,0,5,2.243,5,5v2h2v-2c0-3.86-3.141-7-7-7H25c-3.859,0-7,3.14-7,7V23z M30,20.152L28.443,16h3.113L30,20.152z"/><rect x="29" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="30" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M29,13h2c2.757,0,5-2.243,5-5V5c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5v3C24,10.757,26.243,13,29,13z M26,5c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V5z"/><path style="fill:#231F20;" d="M49,39H39c-3.859,0-7,3.14-7,7v14h2V46c0-2.757,2.243-5,5-5h10c2.757,0,5,2.243,5,5v14h2V46C56,42.14,52.859,39,49,39z"/><rect x="37" y="49" style="fill:#231F20;" width="2" height="11"/><rect x="49" y="49" style="fill:#231F20;" width="2" height="11"/><path style="fill:#231F20;" d="M21,39H11c-3.859,0-7,3.14-7,7v14h2V46c0-2.757,2.243-5,5-5h10c2.757,0,5,2.243,5,5v14h2V46C28,42.14,24.859,39,21,39z"/><path style="fill:#231F20;" d="M10,30v3c0,2.757,2.243,5,5,5h2c1.642,0,3.088-0.806,4-2.031V36h18v-0.031C39.912,37.194,41.358,38,43,38h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5v3c0,0.342,0.035,0.677,0.102,1H37V24h-2v10H25V24h-2v10h-1.102C21.965,33.677,22,33.342,22,33v-3c0-2.757-2.243-5-5-5h-2C12.243,25,10,27.243,10,30z M40,30c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V30z M12,30c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V30z"/><rect x="9" y="49" style="fill:#231F20;" width="2" height="11"/><rect x="21" y="49" style="fill:#231F20;" width="2" height="11"/><path style="fill:#231F20;" d="M2,36h5v-2H1c-0.553,0-1,0.448-1,1v6c0,0.552,0.447,1,1,1h2v-2H2V36z"/><path style="fill:#231F20;" d="M59,34h-7v2h6v4h-2v2h3c0.553,0,1-0.448,1-1v-6C60,34.448,59.553,34,59,34z"/><rect x="28" y="40" style="fill:#231F20;" width="4" height="2"/><rect x="48" y="4" style="fill:#231F20;" width="7" height="2"/><rect x="48" y="8" style="fill:#231F20;" width="10" height="2"/><rect x="48" y="12" style="fill:#231F20;" width="10" height="2"/><rect x="5" y="4" style="fill:#231F20;" width="7" height="2"/><rect x="2" y="8" style="fill:#231F20;" width="10" height="2"/><rect x="2" y="12" style="fill:#231F20;" width="10" height="2"/><rect x="48" y="16" style="fill:#231F20;" width="2" height="2"/><rect x="52" y="16" style="fill:#231F20;" width="2" height="2"/><rect x="56" y="16" style="fill:#231F20;" width="2" height="2"/><rect x="2" y="16" style="fill:#231F20;" width="2" height="2"/><rect x="6" y="16" style="fill:#231F20;" width="2" height="2"/><rect x="10" y="16" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M42,6h3V4h-4c-0.553,0-1,0.448-1,1v7h2V6z"/><path style="fill:#231F20;" d="M18,12h2V5c0-0.552-0.447-1-1-1h-4v2h3V12z"/></g></svg>',
	                    						'description'=> 'Parents Addon for WPLMS. <strong style="color:#57d657">Price $29</strong> | 30 Day Free Trial',
	                    						'link'=>'https://wplms.io/downloads/wplms-parent-user/',
	                    						'verify'=>array('wplms-parent-user/wplms-parent-user.php'),
                    						),
										'clp'=>array(
	                    						'label'=>__('Custom Learning Paths','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Process_Consulting" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,16c0.553,0,1-0.448,1-1V1c0-0.552-0.447-1-1-1H37c-0.553,0-1,0.448-1,1v6H24V1c0-0.552-0.447-1-1-1H1C0.447,0,0,0.448,0,1v14c0,0.552,0.447,1,1,1h9v5H1c-0.553,0-1,0.448-1,1v14c0,0.552,0.447,1,1,1h18v-2H2V23h21v-2H12v-5h11c0.553,0,1-0.448,1-1V9h12v6c0,0.552,0.447,1,1,1h10v5H37v2h21v12H41v2h18c0.553,0,1-0.448,1-1V22c0-0.552-0.447-1-1-1H49v-5H59z M22,14H2V2h20V14z M38,2h20v12H38V2z"/><path style="fill:#231F20;" d="M35,39H25c-3.859,0-7,3.14-7,7v14h2V46c0-2.757,2.243-5,5-5h1.307l2.757,7.351C29.21,48.741,29.583,49,30,49s0.79-0.259,0.937-0.649L33.693,41H35c2.757,0,5,2.243,5,5v14h2V46C42,42.14,38.859,39,35,39z M30,45.152L28.443,41h3.113L30,45.152z"/><rect x="29" y="51" style="fill:#231F20;" width="2" height="2"/><rect x="29" y="55" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M29,38h2c2.757,0,5-2.243,5-5v-3c0-2.757-2.243-5-5-5h-2c-2.757,0-5,2.243-5,5v3C24,35.757,26.243,38,29,38z M26,30c0-1.654,1.346-3,3-3h2c1.654,0,3,1.346,3,3v3c0,1.654-1.346,3-3,3h-2c-1.654,0-3-1.346-3-3V30z"/><rect x="23" y="49" style="fill:#231F20;" width="2" height="11"/><rect x="35" y="49" style="fill:#231F20;" width="2" height="11"/><rect x="5" y="5" style="fill:#231F20;" width="10" height="2"/><rect x="5" y="9" style="fill:#231F20;" width="14" height="2"/><rect x="41" y="26" style="fill:#231F20;" width="10" height="2"/><rect x="41" y="30" style="fill:#231F20;" width="14" height="2"/><rect x="41" y="5" style="fill:#231F20;" width="2" height="2"/><rect x="45" y="5" style="fill:#231F20;" width="2" height="2"/><rect x="49" y="5" style="fill:#231F20;" width="2" height="2"/><rect x="53" y="5" style="fill:#231F20;" width="2" height="2"/><rect x="41" y="9" style="fill:#231F20;" width="2" height="2"/><rect x="45" y="9" style="fill:#231F20;" width="2" height="2"/><rect x="49" y="9" style="fill:#231F20;" width="2" height="2"/><rect x="53" y="9" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="26" style="fill:#231F20;" width="2" height="2"/><rect x="5" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="9" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="13" y="30" style="fill:#231F20;" width="2" height="2"/><rect x="17" y="30" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> 'Learning Paths for WPLMS. <strong style="color:#57d657">Price $29</strong> | 30 Day Free Trial',
	                    						'link'=>'https://wplms.io/downloads/custom-learning-paths/',
	                    						'verify'=>array('wplms-custom-learning-paths/wplms-custom-learning-paths.php'),
                    						),
										'phone'=>array(
	                    						'label'=>__('Phone Authentication','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Online_Service" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M2,10h39V8H2V2h39V0H1C0.447,0,0,0.448,0,1v58c0,0.552,0.447,1,1,1h40v-2H2V10z"/><rect x="4" y="4" style="fill:#231F20;" width="4" height="2"/><rect x="10" y="4" style="fill:#231F20;" width="4" height="2"/><path style="fill:#231F20;" d="M55,0H45c-0.553,0-1,0.448-1,1v16c0,0.552,0.447,1,1,1h7v24h-7c-0.553,0-1,0.448-1,1v16c0,0.552,0.447,1,1,1h10c2.757,0,5-2.243,5-5V5C60,2.243,57.757,0,55,0z M46,2h2v14h-2V2z M46,44h2v14h-2V44z M58,55c0,1.654-1.346,3-3,3h-5V44h3c0.553,0,1-0.448,1-1V17c0-0.552-0.447-1-1-1h-3V2h5c1.654,0,3,1.346,3,3V55z"/><rect x="4" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="8" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="12" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="16" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="20" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="24" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="28" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="32" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="36" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="40" y="12" style="fill:#231F20;" width="2" height="2"/><rect x="40" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="4" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="8" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="12" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="16" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="20" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="24" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="28" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="32" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="36" y="54" style="fill:#231F20;" width="2" height="2"/><rect x="30" y="21" style="fill:#231F20;" width="10" height="2"/><rect x="18" y="27" style="fill:#231F20;" width="22" height="2"/><rect x="6" y="45" style="fill:#231F20;" width="34" height="2"/><rect x="6" y="39" style="fill:#231F20;" width="34" height="2"/><rect x="6" y="33" style="fill:#231F20;" width="34" height="2"/><rect x="14" y="27" style="fill:#231F20;" width="2" height="2"/><rect x="10" y="27" style="fill:#231F20;" width="2" height="2"/><rect x="6" y="27" style="fill:#231F20;" width="2" height="2"/><rect x="26" y="21" style="fill:#231F20;" width="2" height="2"/><rect x="22" y="21" style="fill:#231F20;" width="2" height="2"/><rect x="18" y="21" style="fill:#231F20;" width="2" height="2"/><rect x="14" y="21" style="fill:#231F20;" width="2" height="2"/><rect x="6" y="21" style="fill:#231F20;" width="6" height="2"/></g></svg>',
	                    						'description'=> 'OTP Registration and Login for WPLMS. <strong style="color:#57d657">Price $19</strong> | 30 Day Free Trial',
	                    						'link'=>'https://wplms.io/downloads/wplms-phone-auth/',
	                    						'verify'=>array('wplms-phone-auth/wplms-phone-auth.php'),
                    						),
										'unit'=>array(
	                    						'label'=>__('Unit Timings','vibe'),
	                    						'icon'=>'<svg version="1.1" id="Mobile_Phone_Video" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><rect x="27" y="4" style="fill:#231F20;" width="6" height="2"/><rect x="27" y="54" style="fill:#231F20;" width="6" height="2"/><path style="fill:#231F20;" d="M59,12H49V2c0-1.103-0.897-2-2-2H13c-1.103,0-2,0.897-2,2v10H1c-0.552,0-1,0.448-1,1v34c0,0.552,0.448,1,1,1h10v10c0,1.103,0.897,2,2,2h34c1.103,0,2-0.897,2-2V48h10c0.552,0,1-0.448,1-1V13C60,12.448,59.552,12,59,12z M47,2v6H13V2H47z M13,10h34v2H13V10z M13,58v-6h34v6H13z M47,50H13v-2h34V50z M58,46H2V14h56V46z"/><rect x="54" y="40" style="fill:#231F20;" width="2" height="2"/><rect x="50" y="40" style="fill:#231F20;" width="2" height="2"/><rect x="46" y="40" style="fill:#231F20;" width="2" height="2"/><polygon style="fill:#231F20;" points="14,44 16,44 16,42 44,42 44,40 16,40 16,38 14,38 14,40 4,40 4,42 14,42 "/><path style="fill:#231F20;" d="M26.474,34.851C26.635,34.95,26.817,35,27,35c0.153,0,0.306-0.035,0.447-0.105l8-4C35.786,30.725,36,30.379,36,30c0-0.379-0.214-0.725-0.553-0.894l-8-4c-0.31-0.154-0.678-0.138-0.973,0.044C26.18,25.332,26,25.653,26,26v8C26,34.347,26.18,34.669,26.474,34.851z M28,27.618L32.764,30L28,32.382V27.618z"/><rect x="4" y="16" style="fill:#231F20;" width="2" height="2"/><rect x="4" y="20" style="fill:#231F20;" width="10" height="2"/><rect x="8" y="16" style="fill:#231F20;" width="2" height="2"/><path style="fill:#231F20;" d="M49,24h6c0.552,0,1-0.448,1-1v-6c0-0.552-0.448-1-1-1h-6c-0.552,0-1,0.448-1,1v6C48,23.552,48.448,24,49,24z M50,18h4v4h-4V18z"/><rect x="51" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="55" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="3" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="7" y="8" style="fill:#231F20;" width="2" height="2"/><rect x="3" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="7" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="51" y="50" style="fill:#231F20;" width="2" height="2"/><rect x="55" y="50" style="fill:#231F20;" width="2" height="2"/></g></svg>',
	                    						'description'=> 'Track time spent in Units WPLMS. <strong style="color:#57d657">Price $29</strong> | 30 Day Free Trial',
	                    						'link'=>'https://wplms.io/downloads/wplms-unit-timings/',
	                    						'verify'=>array('wplms_unit_timings/wplms_unit_timings.php'),
                    						),
	                    		
	                    	);

			// create an images/styleX/ folder for each style here.
			$this->site_styles = array(
				'learningcenter' => array(
					'label'=>'Learning Center',
					'src' => 'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/learningcenter.png',
					'installation_type'=>array('instructor','mooc','academy','university'),
					'link'=>'https://demos.wplms.io/learningcenter/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor')
				),
				'quizmaster' => array(
					'label'=>'Quiz Master',
					'src' => 'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/quizmaster.png',
					'installation_type'=>array('instructor','mooc','academy','university'),
					'link'=>'https://demos.wplms.io/quizmaster/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor')
				),

				'demo_4_academy' => array(
					'label'=>'Demo Academy',
					'src' => 'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo4_academy.png',
					'installation_type'=>array('instructor','mooc','academy','university'),
					'link'=>'https://demos.wplms.io/academy/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor')
				),
				
				
				
				'demo_4' => array(
					'label'=>'Demo 4',
					'src' => 'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/02_preview1.webp',
					'installation_type'=>array('mooc','academy','university','instructor'),
					'link'=>'https://demos.wplms.io/playground/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor')
				),
				'demo10' => array(
					'label'=>'Demo 10',
					'link'=>'https://demos.wplms.io/demos/demo10/',
					'src' => 'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo10.jpg',
					'installation_type'=>array('mooc','academy','university'),
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','vc','eventon','layerslider')
				),
				'demo6' => array(
					'label'=>'Demo 6',
					'link'=>'https://demos.wplms.io/demos/demo6/',
					'src' => 'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo6.jpg',
					'installation_type'=>array('mooc','instructor','academy','university'),
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor')
				),
				'demo17' => array(
					'label'=>'Demo 17',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo17.jpg',
					'installation_type'=>array('instructor','academy'),
					'link'=>'https://demos.wplms.io/demos/demo17/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','eventon','layerslider')
				),
				'demo14' => array(
					'label'=>'Demo 14',
					'link'=>'https://demos.wplms.io/demos/demo14/',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo14.jpg',
					'installation_type'=>array('academy','mooc','university'),
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','eventon')
				),
               'demo1' => array(
               		'label'=>'Demo 1',
               		'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo1.jpg',
               		'link'=>'https://demos.wplms.io/demos/demo1/',
               		'installation_type'=>array('academy','university'),
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','eventon')
               	),
               'demo2' => array(
	               	'label'=>'Demo 2',
	               	'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo2.jpg',
	               	'installation_type'=>array('instructor','mooc','academy'),
	               	'link'=>'https://demos.wplms.io/demos/demo2/',
	               	'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
               ),
               'demo16' => array(
					'label'=>'Demo 16',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo16.jpg',
					'installation_type'=>array('instructor'),
					'link'=>'https://demos.wplms.io/demos/demo16/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor')
				),
               'demo3' => array(
					'label'=>'Demo 3',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo3.jpg',
					'installation_type'=>array('academy','university'),
					'link'=>'https://demos.wplms.io/demos/demo3/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
				),
               'demo4' => array(
					'label'=>'Demo 4',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo4.jpg',
					'installation_type'=>array('academy','mooc'),
					'link'=>'https://demos.wplms.io/demos/demo4/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
				),
               'demo5' => array(
					'label'=>'Demo 5',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo5.jpg',
					'installation_type'=>array('academy','university'),
					'link'=>'https://demos.wplms.io/demos/demo5/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
				),
               'demo11' => array(
					'label'=>'Demo 11',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo11.jpg',
					'installation_type'=>array('academy','instructor'),
					'link'=>'https://demos.wplms.io/demos/demo11/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','vc','revslider','eventon')
				),
               'demo7' => array(
					'label'=>'Demo 7',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo7.jpg',
					'installation_type'=>array('academy','instructor'),
					'link'=>'https://demos.wplms.io/demos/demo7/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
				),
               'demo8' => array(
					'label'=>'Demo 8',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo8.jpg',
					'installation_type'=>array('academy','instructor'),
					'link'=>'https://demos.wplms.io/demos/demo8/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','vc','revslider')
				),
               'demo9' => array(
					'label'=>'Demo 9',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo9.jpg',
					'installation_type'=>array('academy','instructor'),
					'link'=>'https://demos.wplms.io/demos/demo9/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','vc','revslider')
				),
               'demo12' => array(
					'label'=>'Demo 12',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo12.jpg',
					'installation_type'=>array('academy','instructor'),
					'link'=>'https://demos.wplms.io/demos/demo12/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
				),
               'demo13' => array(
					'label'=>'Demo 13',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo13.jpg',
					'installation_type'=>array('academy','instructor'),
					'link'=>'https://demos.wplms.io/demos/demo13/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
				),
               'demo15' => array(
					'label'=>'Demo 15',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/demo15.jpg',
					'installation_type'=>array('academy','instructor'),
					'link'=>'https://demos.wplms.io/demos/demo15/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','revslider')
				),
               'default' => array(
					'label'=>'Default',
					'src'=>'https://wplmsupdates.s3.amazonaws.com/demodata/demo_preview_images/default.jpg',
					'installation_type'=>array('academy','instructor','university'),
					'link'=>'https://demos.wplms.io/demos/default/',
					'plugins'=>array('vibebp','wplms_plugin','buddypress','bbpress','vibe-helpdesk','vibedrive','elementor','layerslider')
				),
           );

			//If we have parent slug - set correct url
			if ( $this->parent_slug !== '' ) {
				$this->page_url = 'admin.php?page=' . $this->page_slug;
			} else {
				$this->page_url = 'themes.php?page=' . $this->page_slug;
			}
			$this->page_url = apply_filters( $this->theme_name . '_theme_setup_wizard_page_url', $this->page_url );

			//set relative plugin path url
			$this->plugin_path = trailingslashit( $this->cleanFilePath( dirname( __FILE__ ) ) );
			$relative_url      = str_replace( $this->cleanFilePath( get_template_directory() ), '', $this->plugin_path );
			$this->plugin_url  = trailingslashit( get_template_directory_uri() . $relative_url );
		}

		/**
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_actions() {

			if ( apply_filters( $this->theme_name . '_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
				add_action( 'after_switch_theme', array( $this, 'switch_theme' ) );

				if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
					add_action( 'init', array( $this, 'get_tgmpa_instanse' ), 30 );
					add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
				}

				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'admin_init', array( $this, 'admin_redirects' ), 30 );
				add_action( 'admin_init', array( $this, 'init_wizard_steps' ), 30 );
				add_action( 'admin_init', array( $this, 'setup_wizard' ), 30 );
				add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );
				add_action( 'wp_ajax_envato_setup_plugins', array( $this, 'ajax_plugins' ) );
				add_action( 'wp_ajax_envato_setup_content', array( $this, 'ajax_content' ) );

				//add_action('wp_ajax_save_item_purchase_code',array($this,'save_item_purchase_code'));
				add_filter('wplms_required_plugins',array($this,'setup_wizard_plugins'));

				add_filter('wplms_import_post_type_content',array($this,'check_post_type'),10,2);
				add_filter('wplms_import_post_type_content_disable',array($this,'check_post_type'),10,2);

				add_action('wp_ajax_clear_imported_posts',array($this,'clear_imported_posts'));
			}
			if ( function_exists( 'envato_market' ) ) {
				add_action( 'admin_init', array( $this, 'envato_market_admin_init' ), 20 );
				add_filter( 'http_request_args', array( $this, 'envato_market_http_request_args' ), 10, 2 );
			}
			add_action('widgets_init',array($this,'wplms_register_sidebars'));
			add_action( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 2 );
			add_filter('woocommerce_enable_setup_wizard',function($x){return false;});
		}


		function clear_imported_posts(){
			
			if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_clear_imported_posts') || !current_user_can('manage_options')){
	         	_e('Security check Failed. Contact Administrator.','vibe');
	         	die();
	      	}
	      	delete_transient( 'importpostids');
	      	delete_transient( 'importtermids');
	      	die();
		}



		function wplms_register_sidebars(){

			$style = vibe_get_site_style();//get_option('wplms_site_style');
			if(empty($style)){$style = $this->get_default_theme_style();}
			switch($style){
				case 'demo1':
				case 'demo5':
					register_sidebar( array(
			          	'name' => 'MegaMenu',
			          	'id' => 'MegaMenu',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			              'after_widget' => '</div></div>',
			              'before_title' => '<h4 class="widgettitle"><span>',
			              'after_title' => '</span></h4>',
			            'description'   => __('This is the MegaMenu sidebar','vibe')
			        ));
			      	register_sidebar( array(
			    	  	'name' => 'MegaMenu2',
			          	'id' => 'MegaMenu2',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			            'after_widget' => '</div></div>',
			            'before_title' => '<h4 class="widgettitle"><span>',
			            'after_title' => '</span></h4>',
			          	'description'   => __('This is the MegaMenu2 sidebar','vibe')
			      	));
				break;
				case 'demo2':
					register_sidebar( array(
			          	'name' => 'MegaMenu',
			          	'id' => 'MegaMenu',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			              'after_widget' => '</div></div>',
			              'before_title' => '<h4 class="widgettitle"><span>',
			              'after_title' => '</span></h4>',
			            'description'   => __('This is the MegaMenu sidebar','vibe')
			        ));
				break;
				case 'demo3':
				case 'demo6':
					register_sidebar( array(
			          	'name' => 'MegaMenu',
			          	'id' => 'MegaMenu',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			              'after_widget' => '</div></div>',
			              'before_title' => '<h4 class="widgettitle"><span>',
			              'after_title' => '</span></h4>',
			            'description'   => __('This is the MegaMenu sidebar','vibe')
			        ));
				break;
				case 'demo4':
				break;
			}
		}
		function check_post_type($check,$post_type){
			
			if(empty($this->check_wplms_plugins)){
				$this->check_wplms_plugins = get_option('wplms_plugins');	

			}
			
			if(is_array($this->check_wplms_plugins)){
				if(!in_array('bbpress/bbpress.php',$this->check_wplms_plugins)){
					if(in_array($post_type,array('forum','topic','reply'))){
						$check = 0;
					}
				}

				if(!in_array('eventON/eventon.php',$this->check_wplms_plugins)){
					if(in_array($post_type,array('ajde_events'))){
						$check = 0;
					}
				}

				if(!in_array('woocommerce/woocommerce.php',$this->check_wplms_plugins)){
					if(in_array($post_type,array('product'))){
						$check = 0;
					}
				}

				if(!in_array('buddydrive/buddydrive.php',$this->check_wplms_plugins)){
					if(in_array($post_type,array('buddydrive-file'))){
						$check = 0;
					}
				}

				if(!in_array('badgeos/badgeos.php',$this->check_wplms_plugins)){
					if(in_array($post_type,array('achievement-type','badgeos-log-entry'))){
						$check = 0;
					}
				}
			}

			return $check;	
		}
		function setup_wizard_plugins($plugins){

			// SETUP WIZARD PLUGINS
			$wplms_plugins = get_option( 'wplms_plugins');
			if(isset($wplms_plugins) && is_array($wplms_plugins)){
				$plugins[] = array(
	            'name'                  => 'MyCred',
	            'slug'                  => 'mycred', 
	            'file'					=> 'mycred/mycred.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS MyCred Addon',
	            'slug'                  => 'wplms-mycred-addon', 
	            'file'					=> 'wplms-mycred-addon/wplms-mycred-addon.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'CoAuthors plus',
	            'slug'                  => 'co-authors-plus', 
	            'file'					=> 'co-authors-plus/co-authors-plus.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS CoAuthors plus',
	            'slug'                  => 'wplms-coauthors-plus', 
	            'file'					=> 'WPLMS-Coauthors-Plus/wplms-coauthor-plus.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'BadgeOS',
	            'slug'                  => 'badgeos', 
	            'file'					=> 'badgeos/badgeos.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS BadgeOS',
	            'slug'                  => 'wplms-badgeos', 
	            'file'					=> 'WPLMS-BadgeOS/badgeos-wplms.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'BadgeOS Community Addon',
	            'slug'                  => 'badgeos-community-add-on', 
	            'file'					=> 'badgeos-community-add-on/badgeos-community.php'
	        	);
	        	
	        	$plugins[] = array(
        		'name'                  => 'PMPRO', // The plugin name
            	'slug'                  => 'paid-memberships-pro',
            	'file'					=> 'paid-memberships-pro/paid-memberships-pro.php',
        		);

	        	$plugins[] = array(
	            'name'                  => 'BigBlueButton',
	            'slug'                  => 'bigbluebutton', 
	            'file'					=> 'bigbluebutton/bigbluebutton.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'Vibe BigBluebutton',
	            'slug'                  => 'vibe-bbb', 
	            'file'					=> 'vibe-bbb/vibe-bbb.php',
	        	);

	        	$plugins[] = array(
	            'name'                  => 'Vibe Earnings',
	            'slug'                  => 'vibe-earnings', 
	            'file'					=> 'vibe-earnings/loader.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'H5P',
	            'slug'                  => 'h5p', 
	            'file'					=> 'h5p/h5p.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS H5P',
	            'slug'                  => 'wplms-h5p-plugin', 
	            'file'					=> 'wplms-h5p/wplms-h5p.php',
	        	);
				foreach($plugins as $k=>$plugin){
					if(empty($plugin['required']) && isset($plugin['file']) && !in_array($plugin['file'],$wplms_plugins)){
						unset($plugins[$k]);
					}
				}
			}

			return $plugins;
		}
		/**
		 * After a theme update we clear the setup_complete option. This prompts the user to visit the update page again.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function upgrader_post_install( $return, $theme ) {
			if ( is_wp_error( $return ) ) {
				return $return;
			}
			if ( $theme != get_stylesheet() ) {
				return $return;
			}
			update_option( 'envato_setup_complete', false );

			return $return;
		}

		/**
		 * We determine if the user already has theme content installed. This can happen if swapping from a previous theme or updated the current theme. We change the UI a bit when updating / swapping to a new theme.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function is_possible_upgrade() {
			return false;
		}

		public function enqueue_scripts() {
		}

		public function tgmpa_load( $status ) {
			return is_admin() || current_user_can( 'install_themes' );
		}

		public function switch_theme() {
			set_transient( '_' . $this->theme_name . '_activation_redirect', 1 );
		}

		public function admin_redirects() {

			ob_start();
			if ( ! get_transient( '_' . $this->theme_name . '_activation_redirect' ) || get_option( 'envato_setup_complete', false ) ) {
				return;
			}
			delete_transient( '_' . $this->theme_name . '_activation_redirect' );
			wp_safe_redirect( admin_url( $this->page_url ) );
			exit;
		}

		/**
		 * Get configured TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function get_tgmpa_instanse() {
			$this->tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		}

		/**
		 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function set_tgmpa_url() {

			$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
			$this->tgmpa_menu_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );

			$tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';

			$this->tgmpa_url = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );

		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {

			if ( $this->is_submenu_page() ) {
				//prevent Theme Check warning about "themes should use add_theme_page for adding admin pages"
				$add_subpage_function = 'add_submenu' . '_page';
				$add_subpage_function( $this->parent_slug, esc_html__( 'Setup Wizard','vibe' ), esc_html__( 'Setup Wizard','vibe' ), 'manage_options', $this->page_slug, array(
					$this,
					'setup_wizard',
				) );
			} else {
				add_theme_page( esc_html__( 'Setup Wizard' ,'vibe'), esc_html__( 'Setup Wizard','vibe' ), 'manage_options', $this->page_slug, array(
					$this,
					'setup_wizard',
				) );
			}

			add_theme_page( esc_html__( 'Export Wizard','vibe' ), esc_html__( 'Export Wizard' ,'vibe'), 'manage_options', $this->page_slug.'&export', array(
					$this,
					'export_wizard',
				) );

		}

		/**
		 * Setup steps.
		 *
		 * @since 1.1.1
		 * @access public
		 * @return array
		 */
		public function init_wizard_steps() {

			$this->steps = array(
				'introduction' => array(
					'name'    => esc_html__( 'Introduction','vibe' ),
					'view'    => array( $this, 'envato_setup_introduction' ),
					'handler' => array( $this, 'envato_setup_introduction_save' ),
				),
			);
			
			if( count($this->site_styles) > 1 ) {
				$this->steps['style'] = array(
					'name'    => esc_html__( 'Select a Demo Style','vibe' ),
					'view'    => array( $this, 'envato_setup_demo_style' ),
					'handler' => array( $this, 'envato_setup_demo_style_save' ),
				);
			}
			$this->steps['start']         = array(
				'name'    => esc_html__( 'Select features you want in your site.','vibe' ),
				'view'    => array( $this, 'envato_start_setup' ),
				'handler' => array( $this, 'envato_start_setup_save' ),
			);

			$this->steps['updates']         = array(
				'name'    => esc_html__( 'Authenticate and Setup Updates' ),
				'view'    => array( $this, 'envato_setup_updates' ),
				'handler' => array( $this, 'envato_setup_updates_save' ),
			);

			if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
				$this->steps['default_plugins'] = array(
					'name'    => esc_html__( 'Activate plugins required for features.','vibe' ),
					'view'    => array( $this, 'envato_setup_default_plugins' ),
					'handler' => '',
				);
			}

			$this->steps['pagesetup']         = array(
				'name'    => esc_html__( 'Setup necessary settings' ,'vibe'),
				'view'    => array( $this, 'envato_page_setup' ),
				'handler' => array( $this, 'envato_page_setup_save' ),
			);
			
			$this->steps['default_content'] = array(
				'name'    => esc_html__( 'Import Content from Theme','vibe' ),
				'view'    => array( $this, 'envato_setup_default_content' ),
				'handler' => '',
			);
			$this->steps['design']          = array(
				'name'    => esc_html__( 'Change design elements','vibe' ),
				'view'    => array( $this, 'envato_setup_design' ),
				'handler' => array( $this, 'envato_setup_design_save' ),
			);
			$this->steps['next_steps']      = array(
				'name'    => esc_html__( 'Are you ready to Rumble ?','vibe' ),
				'view'    => array( $this, 'envato_setup_ready' ),
				'handler' => '',
			);

			$this->steps = apply_filters( $this->theme_name . '_theme_setup_wizard_steps', $this->steps );

		}

		function envato_start_setup(){
			
			$wplms_style = get_option('wplms_style');
			print($wplms_style);
			?><h1><?php esc_html_e( 'Select features for this site','vibe' ); ?></h1>
            <form method="post">
                <p><?php echo esc_html_e( 'Select the features you need for your site. The features here are pre-configured, so the next steps would be based on the selection you make here. These features can be added/removed later on from theme settings as well.','vibe' ); ?></p>
                <hr>
                <div id="purpose_description"></div>
                <div class="theme-features">
                    <ul>
	                    <?php

	                    $demo_style =vibe_get_site_style();
                    	switch($demo_style){
                    		case 'demo10':
                    		case 'demo11':
                    			foreach ( $this->features as $feature => $data ) {
                    				if($feature=='vc'){
                    					$this->features[$feature]['default']=1;
                    				}
                    			}
                    		break;
                    	}
                    	

	                    foreach ( $this->features as $feature => $data ) {
	                    	$class='';$flag=0;
	                    	
	                    	if(isset($data['verify'])){
	                    		$flag = 1;
	                    		foreach($data['verify'] as $plugin){

	                    			if(!vibe_check_plugin_installed($plugin)){
	                    				$flag=0;break;
	                    			}
	                    		}
	                    	}

	                    	if(isset($data['default'])){$flag = 1;}
	                    	if(empty($flag)){$class='';}else{$class='selected';}

		                    ?>
                            <li class="<?php echo vibe_sanitizer($class,'text'); ?>">
                                <a href="#" data-style="<?php echo esc_attr( $feature ); ?>" ><?php echo $data['icon'];?>
                                    <h4><?php echo vibe_sanitizer($data['label']); ?></h4>
                                    <p><?php echo vibe_sanitizer($data['description']); ?></p>
                                    <?php
                                    if(isset($data['link'])){
                                    	echo '<a href="'.$data['link'].'" target="_blank">Learn More &rsaquo;</a>';
                                    }
                                    if(isset($data['verify'])){
                                    	foreach($data['verify'] as $plugin){
                                    		echo '<input type="hidden" '.(empty($flag)?'':'name="plugins[]"').' value="'.$plugin.'" />';
                                    	}
                                    }
                                    ?>
                                </a>
                            </li>
	                    <?php } ?>
                    </ul>
                </div>

                <hr><p><em>Have a suggestion for us. Share it with us <a href="https://wplms.io/support/forums/forum/general/feature-request/" target="_blank">here</a>  !</em></p>

                <div class="envato-setup-actions step">
                	<div>
                    <input type="submit" class="large_next_button button-next"
                           value="<?php _e( 'Continue', 'vibe' ); ?>" name="save_step"/>
                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
                       ><?php esc_html_e( 'Skip this step','vibe' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>"
                       class="previous_step"><?php esc_html_e( 'Previous step','vibe' ); ?></a>
                </div>
            </form>
            <?php
		}

		function envato_start_setup_save(){
			check_admin_referer( 'envato-setup' );
			if ( ! empty( $_REQUEST['save_step'] )){
			
				$deactivate_plugins = array();
				if(isset($_POST['plugins'])){
					foreach($this->features as $key=>$feature){
						if($key !='course' && isset($feature['verify'])){
							foreach($feature['verify'] as $plugin){
								if(vibe_check_plugin_installed($plugin) && !in_array($plugin,$_POST['plugins'])){
									deactivate_plugins($plugin);
								}
							}					
						}
					}	
				}
				update_option( 'wplms_plugins', $_POST['plugins'] );
			}
			wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}

		public function onboarding_step(){

			echo '<div class="onboarding '.$this->step.'">';
			echo '<div class="onboarding_header">
			<span><img id="wplms_logo" class="site-logo" src="'.(($this->step == 'introduction')?get_template_directory_uri().'/assets/images/logo.png':get_template_directory_uri().'/assets/images/logo_black.png').'" alt="'.get_bloginfo( 'name' ).'" />
				<span>The WordPress LMS</span>
			</span></div>';
			if($this->step =='introduction'){
				?>
				<div class="onboarding_introduction">
					<h2>You are now few steps away from creating your own education site.</h2>
					<span>Start your own education site in minutes</span>
				</div>
				<a href="https://wplms.io/support/knowledge-base/installing-wplms-version-4" target="_blank">Setup Wizard Video for Reference &rsaquo;</a>
				<?php
			}else{
				$this->setup_wizard_steps();
			}
			
			echo '</div>';
		}
		/**
		 * Show the setup wizard
		 */
		public function setup_wizard() {
			if ( empty( $_GET['page'] ) || $this->page_slug !== $_GET['page'] ) {
				return;
			}
			ob_end_clean();

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_register_script( 'jquery-blockui', $this->plugin_url . 'js/jquery.blockUI.js', array( 'jquery' ), '2.70', true );
			wp_enqueue_script( 'envato-color', $this->plugin_url . 'js/jscolor.js',array(), $this->version );
			wp_register_script( 'envato-setup', $this->plugin_url . 'js/envato-setup.js', array(
				'jquery',
				'jquery-blockui',
			), $this->version );
			wp_localize_script( 'envato-setup', 'envato_setup_params', array(
				'tgm_plugin_nonce' => array(
					'update'  => wp_create_nonce( 'tgmpa-update' ),
					'install' => wp_create_nonce( 'tgmpa-install' ),
				),
				'tgm_bulk_url'     => admin_url( $this->tgmpa_url ),
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'wpnonce'          => wp_create_nonce( 'envato_setup_nonce' ),
				'verify_text'      => esc_html__( '...verifying','vibe' ),
			) );

			//wp_enqueue_style( 'envato_wizard_admin_styles', $this->plugin_url . '/css/admin.css', array(), $this->version );
			wp_enqueue_style( 'envato-setup', $this->plugin_url . 'css/envato-setup.css', array(
				'wp-admin',
				'dashicons',
				'install',
			),rand(0,999) );

			//enqueue style for admin notices
			wp_enqueue_style( 'wp-admin');

			wp_enqueue_media();
			wp_enqueue_script( 'media');
			ob_start();

			$this->setup_wizard_header();
			echo '<div class="setup_wizard_wrapper">';
			$this->onboarding_step();
			echo '<div class="setup_wizard_main">';
			

			
			$show_content = true;
			echo '<div class="setup_wizard_main_header">
			<span></span>
			<span>Having Troubles ? <a href="https://wplms.io/support">Get Help</a></span>
			</div>';
			echo '<div class="envato-setup-content">';
			if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				$show_content = call_user_func( $this->steps[ $this->step ]['handler'] );
			}
			if ( $show_content ) {
				$this->setup_wizard_content();
			}
			echo '</div></div></div>';
			$this->setup_wizard_footer();
			exit;
		}

		public function get_step_link( $step ) {
			return add_query_arg( 'step', $step, admin_url( 'admin.php?page=' . $this->page_slug ) );
		}

		public function get_next_step_link($info = null) {
			$keys = array_keys( $this->steps );

			$link = add_query_arg( array(
				'step'=> $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ],'installation_type'=>$info
			), remove_query_arg( 'translation_updated' ) );
			

			return $link;
		}

		public function get_previous_step_link($info = null) {
			

			$link =add_query_arg( array(
				'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) - 1 ],
				'installation_type'=>$info
			), remove_query_arg( 'translation_updated' ) );
			

			return $link;
		}

		public function envato_setup_updates_save(){
			$purchase_code = esc_attr($_POST['purchase_code']);
			if(!empty($purchase_code)){
				$response = wp_remote_get('https://wplms.io/verify-purchase?purchase_code='.$purchase_code,array('timeout'     => 120));
				$body = json_decode(wp_remote_retrieve_body($response),true);
				if($body['verify-purchase']['item_id'] == 6780226){
					update_option('wplms_purchase_code',$purchase_code);
					return true;
				}
			}
			return false;
		}

		public function envato_setup_updates(){

			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

			//Support the most advanced project in WordPress. do not kill the WPLMS Project.
			$verified = 0;
			$purchase_code = get_option('wplms_purchase_code');
			
			if(!empty($purchase_code)){
				$verified =1;
			}else{
				if(empty($_GET['security'])){
					$security = wp_generate_password(6,false,false);	
					set_transient('security',$security,300);
				}else{
					$check = get_transient('security');
					if($_GET['security'] == $check){
						$verified=1;
					}
				}
			}
			
			
			?><h1><?php esc_html_e( 'Authenticate and Setup Updates','vibe' ); ?></h1>
			<p><?php echo esc_html_e( 'Required to setup theme and plugin updates.','vibe' ); ?></p>
                <hr>
                <?php
                if($verified ){

                	if(!empty($_GET['purchase_code'])){
	            		update_option('wplms_purchase_code',esc_attr($_GET['purchase_code']));
	            		update_option('envato_token',array(
							'refresh_token'=>esc_attr($_GET['refresh_token']),
							'access_token'=>esc_attr($_GET['access_token']),
							'expires'=>esc_attr($_GET['expires']),
						));
					}
        		?>
        		<div class="envato-setup-actions step">
        			<?php
        			if(!empty($_GET['referrer']) && $_GET['referrer'] == 'about'){
        				?>
        				<a href="<?php echo admin_url( 'index.php?page=wplms-about'); ?>"
	                       class="large_next_button button-next"><?php esc_html_e( 'Updates Active. Back to About page.','vibe' ); ?></a>
        				<?php
        			}else{
        				?>
        				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
	                       class="large_next_button button-next"><?php esc_html_e( 'Updates Active. Proceed to next.','vibe' ); ?></a>
        				<?php
        			}
        			?>
	               <a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous Step.','vibe' ); ?></a>
               	</div>
        		<?php
                }else{
                ?>
                <div class="envato_authentication_wrapper">
	                <div class="envato_authentication_block">
	                	<p><strong>[ Recommended ]</strong> Authenticate from Envato. Setup Automatic Updates.</p>
		                <form method="get" action="https://wplms.io/envato/">
		                	<input type="hidden" name="callback_url" value="<?php echo $actual_link; ?>" />
		                	<input type="text" name="username" placeholder="Enter Envato username">
		                	<input type="hidden" name="security" value="<?php echo $security; ?>">
		                	<input type="submit" class="large_next_button button-next" value="Authenticate" /> 
		                </form>
		            </div>
		            <div class="envato_authentication_block">
	                	<p>Skip Authentication, move forward. Authenticate later from WPLMS Options panel</p>
		                <form method="post">
		                    <input type="text" name="purchase_code" placeholder="Enter Item Purchase Code">
							<input type="submit" class="large_next_button button-next"
		                           value="<?php _e( 'Continue', 'vibe' ); ?>" name="save_step"/>
		                </form>
		            </div>
	            </div>
            <?php
            	}
		}

		public function envato_page_setup(){

			global $wpdb;
			$layout_count = $wpdb->get_results("SELECT post_type,count(*) as count FROM {$wpdb->posts} where post_type IN ('member-profile','member-card','group-layout','group-card','course-layout','course-card') GROUP BY post_type ",ARRAY_A);
			?><h1><?php esc_html_e( 'Setup Required Pages/Layouts','vibe' ); ?></h1>
			<p><?php echo esc_html_e( 'Automatically configure and set required pages for WPLMS. There are important pages required for LMS to work properly. We recommend everyone using the LMS to setup these pages.','vibe' ); ?></p>
                <hr>
                <table class="wplms-setup-pages" cellspacing="0">
					<thead>
						<tr>
							<th class="page-name">Layout/Page Name</th>
							<th class="page-description">Description</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$layouts = array(
							'member-profile'=>array(
								'label'=>__('Member Profile','vibe'),
								'description'=>__('Member profile layout for displaying member profiles')
							),
							'member-card'=>array(
								'label'=>__('Member Card','vibe'),
								'description'=>__('Member cards displaying member in directory')
							),
							'group-layout'=>array(
								'label'=>__('Group Layout','vibe'),
								'description'=>__('Group Layouts for displaying individual groups')
							),
							'group-card'=>array(
								'label'=>__('Group Card','vibe'),
								'description'=>__('Group cards displaying group in directory')
							),
							'course-layout'=>array(
								'label'=>__('Course Layout','vibe'),
								'description'=>__('Course Layout displaying courses')
							),
							'course-card'=>array(
								'label'=>__('Course Card','vibe'),
								'description'=>__('Course cards for displaying courses in directory')
							)
						);

						foreach($layouts as $key=>$layout){
							$check=0;
							foreach($layout_count as $count){
								if($count['post_type'] == $key){
									if(!empty($count['count'])){
										$check=1;
									}
									break;
								}
							}
							?>
							<tr <?php echo (empty($check)?'':'class="done"');?>>
							<td class="page-name"><?php echo $layout['label']; ?></td>
							<td><?php echo $layout['description']; ?></td>
							</tr>
						<?php
						}
						?>
						
						<?php if(function_exists('vibe_get_option')){$page_id = vibe_get_option('certificate');}  ?>
						<tr <?php echo (empty($page_id)?'':'class="done"');?>>
							<td class="page-name">Fallback certificate</td>
							<td>Fallback certificate page.</td>
						</tr>
						<?php if(function_exists('get_option')){$page_ids = get_option('bp-pages');} ?>
						<tr <?php echo (empty($page_ids['course'])?'':'class="done"');?>>
							<td class="page-name">Directory Pages</td>
							<td>
								The Directory pages for Members, Courses, Activity will be created to browse various items in site. 					</td>
						</tr>
						<?php if(function_exists('get_option') && empty($page_ids)){$page_ids = get_option('bp-pages');}?>
						<tr <?php echo (empty($page_ids['register'])?'':'class="done"');?>>
							<td class="page-name">Registration</td>
							<td>
								Set a default registration form for users to register on your site. You can disable it from settings. 						</td>
						</tr>
					</tbody>
				</table>
				<br><p><em>You can deactivate registration and directories features from settings provided in the theme. In case you have a suggestion for us. Share it with us <a href="" target="_blank">here</a>  !</em></p>
				<form method="post">
                <div class="envato-setup-actions step">
                	<div>
	                    <input type="submit" class="large_next_button button-next"
	                           value="<?php _e( 'Continue', 'vibe' ); ?>" name="save_step"/>
	                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
	                       ><?php esc_html_e( 'Skip this step','vibe' ); ?></a>
						<?php wp_nonce_field( 'envato-setup' ); ?>
					</div>
				 	<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>"
                       class="previous_step"><?php esc_html_e( 'Previous step','vibe' ); ?></a>
                </div>
                </form>
			<?php                

		}

		public function envato_page_setup_save($go=null){
			if(empty($go)){
				check_admin_referer( 'envato-setup' );
			}
			update_option('elementor_unfiltered_files_upload',1);

			if(class_exists('VibeBP_SetupWizard')){

				$wizrd = VibeBP_SetupWizard::init();

				$wizrd->import_default_menu();
				
				$wizrd->import_members_directory();
				$wizrd->import_groups_directory();
				$wizrd->import_default_xprofile();
				$wizrd->import_group_layout();
			}

			if(class_exists('WPLMS_4_Setup_Wizard')){
				$init = WPLMS_4_Setup_Wizard::init();
				$init->import_course_layout();
				$init->import_course_directory_layout();
			}

			if ( ! empty( $_REQUEST['save_step'] ) || !empty($go)){
				
				$user_id = get_current_user_id();
				$pages = array(
			        array(
			            'post_title'     => 'All Courses',
			            'post_type'      => 'page',
			            'post_name'      => 'all-courses',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			            'meta_input'	=>json_decode('{"vibe_title":"S","vibe_subtitle":" ","vibe_breadcrumbs":"S","vibe_sidebar":"vibebp-dashboard","vibe_title_bg":" ","_elementor_edit_mode":"builder","_elementor_template_type":"wp-page","_elementor_version":"2.9.13","_wp_page_template":"default","_elementor_data":"[{\"id\":\"ce77f46\",\"elType\":\"section\",\"settings\":[],\"elements\":[{\"id\":\"2722c74\",\"elType\":\"column\",\"settings\":{\"_column_size\":100,\"_inline_size\":null},\"elements\":[{\"id\":\"5cf59f6\",\"elType\":\"widget\",\"settings\":{\"courses_per_page\":{\"unit\":\"px\",\"size\":6,\"sizes\":[]},\"per_row\":{\"unit\":\"px\",\"size\":760,\"sizes\":[]},\"show_filters\":\"1\",\"instructor\":\"1\",\"price\":\"1\",\"meta__vibe_students\":\"0\",\"meta__vibe_start_date\":\"1\",\"meta__vibe_course_certificate\":\"0\",\"meta__vibe_course_passing_percentage\":\"0\",\"card_style\":\"course_card\",\"pagination\":\"1\",\"search_courses\":\"1\",\"sort_options\":\"1\",\"meta__vibe_duration\":\"0\",\"meta__vibe_max_students\":\"1\",\"meta__vibe_course_badge_percentage\":\"0\",\"meta__vibe_course_drip_duration\":\"0\",\"meta__vibe_course_retakes\":\"0\"},\"elements\":[],\"widgetType\":\"course_directory\"}],\"isInner\":false}],\"isInner\":false}]","_elementor_controls_usage":{"course_directory":{"count":1,"control_percent":9,"controls":{"content":{"content_section":{"courses_per_page":1,"per_row":1,"show_filters":1,"instructor":1,"price":1,"meta__vibe_students":1,"meta__vibe_start_date":1,"meta__vibe_course_certificate":1,"meta__vibe_course_passing_percentage":1,"card_style":1,"pagination":1,"search_courses":1,"sort_options":1,"meta__vibe_duration":1,"meta__vibe_max_students":1,"meta__vibe_course_badge_percentage":1,"meta__vibe_course_drip_duration":1,"meta__vibe_course_retakes":1}}}},"column":{"count":1,"control_percent":0,"controls":{"layout":{"layout":{"_inline_size":1}}}},"section":{"count":1,"control_percent":0,"controls":[]}},"_elementor_css":{"0":"","time":1594490551,"fonts":[],"icons":[],"status":"file"}}')
			        ),
			        array(
			            'post_title'     => 'Certificate',
			            'post_type'      => 'page',
			            'post_name'      => 'default-certificate',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ),
			        array(
			            'post_title'     => 'Register',
			            'post_type'      => 'page',
			            'post_name'      => 'register',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ),
			        array(
			            'post_title'     => 'Activate',
			            'post_type'      => 'page',
			            'post_name'      => 'activate',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ), 
				);

				$bp_pages = get_option('bp-pages');
				foreach($pages as $key => $page){
						
					if($page['post_name'] == 'all-courses'){
						
						if(empty($bp_pages['course'])){
							$page_id = wp_insert_post($page);	
							$bp_pages['course'] = $page_id;
							
						}
					}

					if($page['post_name'] == 'certificate'){
						$page_id = wp_insert_post($page);	
						vibe_update_option('certificate_page',$page_id);
						update_post_meta($page_id,'_wp_page_template','certificate.php');
					}


					if($page['post_name'] == 'register'){
						if(empty($bp_pages)){$bp_pages = get_option('bp-pages');}
						if(empty($bp_pages['register'])){
							$page_id = wp_insert_post($page);	
							$bp_pages['register'] = $page_id;
							update_option('users_can_register',1);
						}
					}
					if($page['post_name'] == 'activate'){
						if(empty($bp_pages)){$bp_pages = get_option('bp-pages');}
						if(empty($bp_pages['activate'])){
							$page_id = wp_insert_post($page);	
							$bp_pages['activate'] = $page_id;
							
						}
					}
				}
				update_option('bp-pages',$bp_pages);
			}

			update_option('permalink_structure','/%postname%/');
			update_option('membership_active','yes');
			update_option('require_name_email','');
			update_option('comment_moderation','');
			update_option('comment_whitelist','');
			update_option('posts_per_page',6);
			update_option('comments_per_page',5);
			update_option('users_can_register',1);
			update_option('default_role','student');



			$bp_active_components = apply_filters('wplms_setup_bp_components',array(
				'xprofile' => 1,
				'settings' => 1,
				'friends' => 1,
				'messages' => 1,
				'activity' => 1,
				'notifications' => 1,
				'members' => 1 
				));

			global $bp;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			require_once( $bp->plugin_dir . '/bp-core/admin/bp-core-admin-schema.php' );

			bp_update_option( 'bp-active-components', $bp_active_components);
			bp_core_install( $bp_active_components );
			bp_core_add_page_mappings( $bp_active_components);
			

			flush_rewrite_rules();


			//VibeBp sample layouts

			

			if(empty($go)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}
		}
		/**
		 * Setup Wizard Header
		 */
	public function setup_wizard_header() {

		if( is_null ( get_current_screen() )) {
			set_current_screen('wplms_setup_wizard');
		}

		?>
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<?php
			// avoid theme check issues.
			echo '<title>' . esc_html__( 'Theme &rsaquo; Setup Wizard' ,'vibe') . '</title>'; ?>
			<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@200;300;400;700;900&display=swap" rel="stylesheet">
			<?php wp_print_scripts( 'envato-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="envato-setup wp-core-ui">
		<?php
		}

		/**
		 * Setup Wizard Footer
		 */
		public function setup_wizard_footer() {
		?>
		<?php if ( 'next_steps' === $this->step ) : ?>
			<a class="wc-return-to-dashboard"
			   href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard' ,'vibe'); ?></a>
		<?php endif; ?>
		</body>
		<?php
		@do_action( 'admin_footer' ); // this was spitting out some errors in some admin templates. quick @ fix until I have time to find out what's causing errors.
		do_action( 'admin_print_footer_scripts' );
		?>
		</html>
		<?php
	}

		/**
		 * Output the steps
		 */
		public function setup_wizard_steps() {
			$ouput_steps = $this->steps;
			array_shift( $ouput_steps );
			?>
			<ol class="envato-setup-steps">
				<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
					<li class="<?php
					$show_link = false;
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
						$show_link = true;
					}
					?>"><?php
						if ( $show_link ) {
							?>
							<a href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
							<?php
						} else {
							echo esc_html( $step['name'] );
						}
						?></li>
				<?php endforeach; ?>
			</ol>
			<span></span>
			<?php
		}

		/**
		 * Output the content for the current step
		 */
		public function setup_wizard_content() {
			isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
		}

		/**
		 * Introduction step
		 */
		public function envato_setup_introduction() {

			if ( isset( $_REQUEST['debug'] ) ) {
				echo '<pre>';
				// debug inserting a particular post so we can see what's going on
				$post_type = 'nav_menu_item';
				$post_id   = 239; // debug this particular import post id.
				$all_data  = $this->_get_json( 'default.json' );
				if ( ! $post_type || ! isset( $all_data[ $post_type ] ) ) {
					echo "Post type $post_type not found.";
				} else {
					echo "Looking for post id $post_id \n";
					foreach ( $all_data[ $post_type ] as $post_data ) {

						if ( $post_data['post_id'] == $post_id ) {
							$this->_process_post_data( $post_type, $post_data, 0, true );
						}
					}
				}
				$this->_handle_delayed_posts();
				
				echo '</pre>';
			} else if ( isset( $_REQUEST['export'] ) ) {

				@include('envato-setup-export.php');

			} else if ( $this->is_possible_upgrade() ) {

				?>
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s.' ,'vibe'), 'WPLMS' ); ?></h1>
				<p><?php esc_html_e( 'It looks like you may have recently upgraded to this theme. Great! This setup wizard will help ensure all the default settings are correct. It will also show some information about your new website and support options.','vibe' ); ?></p>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s Go!','vibe' ); ?></a>
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="button button-large"><?php esc_html_e( 'Not right now','vibe' ); ?></a>
				</p>
				<?php
			} else if ( get_option( 'envato_setup_complete', false )) {

				if(!empty($setup_options)){
					echo vibe_sanitizer($setup_options);	
				}
				
				?>
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s Theme.' ,'vibe'), 'WPLMS'); ?></h1>
				<p><?php esc_html_e( 'It looks like you have already run the setup wizard. Below are some options: ','vibe' ); ?></p>
				<ul>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="button-next large_next_button"><?php esc_html_e( 'Run Setup Wizard Again','vibe' ); ?></a>
					</li>
					<li>
						<form method="post">
							<input type="hidden" name="reset-font-defaults" value="yes">
							<!--input type="submit" class="button-primary button button-large button-next"
							       value="<?php //_e( 'Reset font style and colors', 'vibe' ); ?>" name="save_step"/ -->
							<?php wp_nonce_field( 'envato-setup' ); ?>
						</form>
					</li>
				</ul>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"><?php esc_html_e( 'Cancel','vibe' ); ?></a>
				</p>
				<?php
			} else {

				if(!empty($setup_options)){
					echo vibe_sanitizer($setup_options);	
				}
				
				?>

				<h1>Chose type of Installation</h1>
				<p>Welcome to WPLMS Setup wizard. This setup Wizard will guide you through the setup process. The purpose of this wizard is to make the setup process simpler. You can always enable or disable features and designs after the setup as well.</p>
				<ul class="wplms_installation_types">
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link('instructor') ); ?>"
					   class="invisible">
						<svg width="100%" height="100%" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
					    <g transform="matrix(4.16667,0,0,4.84981,-4.21885e-15,-7.65727)">
					        <path d="M24,17.99L16.269,17.989L19,21.989L17.689,21.989L14.953,17.989L13,17.989L10.264,21.989L9,21.989L11.732,17.989L8.782,17.989L8.782,16.989L17,16.989L17,15.989L20,15.989L20,16.989L23,16.989L23,2.989L6,2.989L6,3.436L5,3.436L5,1.989L24,1.989L24,17.99ZM6.759,8.99C7.408,8.99 8.052,8.777 8.451,8.554C9.206,8.134 11.146,6.911 11.936,6.43C12.151,6.3 12.432,6.348 12.59,6.544L12.599,6.554C12.763,6.759 12.744,7.054 12.556,7.234L9.185,10.448C8.664,10.946 8.363,11.631 8.332,12.35C8.237,14.557 8.071,19.262 8,21.184C7.984,21.634 7.614,21.99 7.164,21.99L7.163,21.99C6.719,21.99 6.377,21.642 6.327,21.202C6.216,20.22 5.998,17.923 5.9,16.99C5.86,16.606 5.621,16.377 5.316,16.376C5.012,16.374 4.793,16.602 4.768,16.984C4.706,17.905 4.502,20.233 4.426,21.205C4.392,21.646 4.029,21.99 3.586,21.99L3.585,21.99C3.133,21.99 2.762,21.634 2.743,21.181C2.646,18.841 2.374,12.218 2.374,12.218L1.087,14.549C0.947,14.803 0.642,14.913 0.372,14.809L0.371,14.808C0.143,14.72 0,14.503 0,14.268L0.022,14.111L1.266,9.718C1.388,9.288 1.781,8.991 2.229,8.991L6.759,8.991L6.759,8.99ZM14,10.99L19,10.99L19,9.99L14,9.99L14,10.99ZM14,8.99L21,8.99L21,7.99L14,7.99L14,8.99ZM5.374,3.99C6.615,3.99 7.624,4.998 7.624,6.24C7.624,7.482 6.615,8.49 5.374,8.49C4.132,8.49 3.124,7.482 3.124,6.24C3.124,4.998 4.132,3.99 5.374,3.99ZM14,6.99L21,6.99L21,5.99L14,5.99L14,6.99Z"/>
					    </g>
					</svg>
						<span>Instructor</span></a>
					</li>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link('academy') ); ?>"
					   class="invisible">
						<svg width="100%" height="100%" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
						    <g transform="matrix(4.16667,0,0,4.16667,0,0)">
						        <path d="M22,22L23,22L23,24L1,24L1,22L2,22L2,14L22,14L22,22ZM15,17L9,17L9,23L11,23L11,18L13,18L13,23L15,23L15,17ZM6,20L4,20L4,22L6,22L6,20ZM20,20L18,20L18,22L20,22L20,20ZM6,16L4,16L4,18L6,18L6,16ZM20,16L18,16L18,18L20,18L20,16ZM24,13L0,13L3,6L6.943,6L4.342,8.229L5.002,9L11,3.857L11,0L16,0L15,1.491L16,3L12,3L18.999,9L19.66,8.229L17.058,6L21,6L24,13ZM12,6.5C13.38,6.5 14.5,7.62 14.5,9C14.5,10.38 13.38,11.5 12,11.5C10.62,11.5 9.5,10.38 9.5,9C9.5,7.62 10.62,6.5 12,6.5ZM12,9L13,9L13,9.8L11.237,9.8L11.237,8L12,8L12,9Z"/>
						    </g>
						</svg>
						<span>Academy</span></a>
					</li>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link('university') ); ?>"
					   class="invisible">
						<svg width="100%" height="100%" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
						    <g transform="matrix(4.16653,0,0,4.16667,0,-5.62513e-15)">
						        <path d="M24,24L0,24L0,22L24,22L24,24ZM23,21L1,21L1,20L23,20L23,21ZM6,19.001L2,19.001L2,13C1.448,13 1,12.552 1,12C1,11.448 1.448,11 2,11L6,11C6.552,11 7,11.448 7,12C7,12.552 6.552,13 6,13L6,19.001ZM14,19.001L10,19.001L10,13C9.448,13 9,12.552 9,12C9,11.448 9.448,11 10,11L14,11C14.552,11 15,11.448 15,12C15,12.552 14.552,13 14,13L14,19.001ZM22,19.001L18,19.001L18,13C17.448,13 17,12.552 17,12C17,11.448 17.448,11 18,11L22,11C22.552,11 23,11.448 23,12C23,12.552 22.552,13 22,13L22,19.001ZM24.001,10L0,10L12,0L24.001,10ZM5.524,8L18.477,8L12,2.603L5.524,8ZM11.995,4C12.961,4 13.745,4.784 13.745,5.75C13.745,6.716 12.961,7.5 11.995,7.5C11.029,7.5 10.245,6.716 10.245,5.75C10.245,4.784 11.029,4 11.995,4Z"/>
						    </g>
						</svg>

						<span>University</span></a>
					</li>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link('mooc') ); ?>"
					   class="invisible">
						<svg width="100%" height="100%" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
					    <g transform="matrix(5.82058,0,0,5.81382,-11.1412,-0.164716)">
					        <path d="M2,8.6C2,3.905 5.805,0.1 10.5,0.1C15.188,0.1 19,3.899 19,8.6C19,13.295 15.194,17.099 10.5,17.099C5.805,17.099 2,13.293 2,8.6M10.883,1.536C10.72,1.541 10.605,1.723 10.479,1.827C10.227,2.036 9.582,2.446 9.222,2.321C8.854,2.195 8.067,2.789 7.941,2.794C7.894,2.795 7.943,2.344 8.194,2.311C8.085,2.327 9.077,1.81 9.05,1.702C9.018,1.574 7.068,2.284 7.155,2.427C7.196,2.492 7.366,2.492 7.143,2.635C7.016,2.712 6.879,3.202 6.759,3.202C6.402,3.358 6.379,2.895 5.982,3.492L5.348,3.748C4.408,4.746 3.757,6.012 3.521,7.418C3.512,7.475 3.758,7.578 3.789,7.617C3.869,7.712 3.869,8.122 3.908,8.255C4.006,8.594 4.247,8.782 4.432,9.09C4.541,9.274 4.722,9.738 4.665,9.93C4.742,9.804 5.424,10.508 5.548,10.654C5.841,11 6.068,11.417 5.591,11.758C5.437,11.869 5.824,12.558 5.625,12.728L5.368,12.793C5.116,12.948 5.23,13.329 5.384,13.489C6.672,14.836 8.487,15.675 10.498,15.675C14.405,15.675 17.573,12.507 17.573,8.6C17.573,7.866 17.448,7.126 17.32,6.736C17.28,6.613 17.186,6.514 17.066,6.468C16.884,6.398 16.118,6.89 16.003,6.648L15.598,6.653C15.513,6.607 15.277,6.289 15.169,6.327C14.95,6.407 15.505,7.01 15.656,7.09C15.798,6.982 16.259,6.761 16.359,7.063C16.549,7.632 15.837,8.256 15.473,8.586C14.929,9.077 15.03,8.267 14.66,7.982C14.465,7.833 14.467,7.514 14.271,7.404C14.187,7.357 13.819,6.936 13.785,6.84L13.783,6.828L13.785,6.84L13.772,6.945C13.705,6.997 13.564,6.756 13.548,6.718C13.548,6.927 13.889,7.26 14.001,7.427C14.192,7.714 14.295,8.132 14.53,8.367C14.656,8.493 15.139,9.014 15.264,9.003L15.909,8.696C16.365,8.804 14.836,10.967 14.691,11.234C14.571,11.46 14.787,12.014 14.771,12.28C14.75,12.586 14.509,12.686 14.28,12.852C14.034,13.032 14.092,13.38 13.886,13.507C13.52,13.733 13.256,14.466 12.737,14.462C12.583,14.462 11.929,14.717 11.843,14.467C11.777,14.286 11.687,14.148 11.593,13.969C11.502,13.794 11.583,13.612 11.471,13.457C11.393,13.349 11.135,13.105 11.112,12.977C11.11,12.868 11.195,12.534 11.31,12.475C11.473,12.393 11.341,12.151 11.321,12.011C11.287,11.76 11.132,11.553 10.945,11.408C10.67,11.196 10.813,11.028 10.877,10.725C10.877,10.581 10.789,10.392 10.594,10.448C10.195,10.565 10.317,10.136 10.026,10.156C9.816,10.17 9.644,10.304 9.449,10.362C9.204,10.436 8.953,10.305 8.711,10.274C7.714,10.148 7.389,9.009 7.649,8.187L7.615,7.699C7.727,7.449 7.956,7.17 8.155,6.981C8.267,6.874 8.41,6.901 8.542,6.819C8.746,6.691 8.749,6.427 8.947,6.266C9.231,6.035 9.617,6.04 9.988,5.991C10.184,5.964 10.934,5.802 11.052,5.948C11.052,5.975 11.18,6.356 11.05,6.355L11.038,6.354L11.05,6.355C11.356,6.382 11.786,6.883 12.073,6.764C12.223,6.702 12.168,6.242 12.476,6.465C12.66,6.598 13.492,6.657 13.665,6.514C13.772,6.426 13.832,5.855 13.701,5.791C13.783,5.873 13.27,5.879 13.221,5.86C13.14,5.83 13.065,5.931 12.939,5.886L12.926,5.881C12.965,5.896 12.474,5.619 12.766,5.406L12.384,5.482L12.294,5.675C12.08,5.785 11.918,5.304 11.838,5.25C11.756,5.196 11.12,4.75 11.293,5.041L11.852,5.597C11.824,5.615 11.705,5.395 11.705,5.556C11.742,5.461 11.718,5.966 11.631,5.801L11.635,5.612C11.635,5.551 11.474,5.493 11.442,5.452C11.354,5.342 11.118,5.1 10.991,5.042C10.956,5.025 10.45,5.103 10.407,5.12L10.28,5.34C10.175,5.379 10.077,5.429 9.984,5.491L9.873,5.741C9.824,5.784 9.331,5.947 9.328,5.953C9.349,5.9 8.983,5.832 9.007,5.726C9.034,5.609 9.158,5.244 9.126,5.111C9.092,4.971 9.887,5.312 9.939,4.944C9.959,4.785 9.97,4.599 9.716,4.572C9.764,4.578 10.208,4.398 10.282,4.317C10.385,4.198 10.623,4.003 10.795,4.003C10.996,4.003 10.952,3.711 11.045,3.567C11.138,3.605 10.995,3.834 11.107,3.927C11.099,3.854 11.422,3.966 11.453,3.95C11.528,3.91 11.938,3.934 11.874,3.74C11.803,3.544 11.91,3.602 12.003,3.561C11.987,3.567 12.243,3.123 12.287,3.269C12.257,3.119 11.989,3.321 11.896,3.314C11.68,3.296 11.771,2.946 11.853,2.842C11.916,2.761 11.681,2.662 11.678,2.817C11.674,3.05 11.457,3.261 11.507,3.571C11.584,4.038 10.986,3.459 10.934,3.49C10.736,3.61 10.574,3.339 10.676,3.175L11.138,2.838C11.212,2.712 11.297,2.566 11.411,2.47C11.79,2.152 11.894,2.407 12.272,2.441C12.641,2.475 12.396,2.529 12.346,2.671C12.297,2.806 12.549,2.854 12.636,2.741C12.685,2.676 12.799,2.512 12.847,2.391C12.91,2.234 13.485,2.252 13.084,2.011C12.819,1.854 11.664,1.535 10.89,1.535L10.883,1.536ZM11.31,5.887C11.285,5.84 11.552,5.816 11.599,5.816C11.652,5.822 11.462,6.135 11.31,5.887M13.113,4.795C13.11,4.691 12.979,4.594 12.843,4.773C12.748,4.897 12.765,5.085 12.714,5.168C12.639,5.292 13.115,5.408 13.115,5.291C13.133,5.094 13.634,5.246 13.732,5.273C13.907,5.321 14.187,5.113 13.881,5.003C13.628,4.911 13.496,4.814 13.474,4.633C13.474,4.633 13.608,4.509 13.549,4.516C13.394,4.534 13.113,5.072 13.113,4.795M9.786,3.711L9.863,3.69L9.826,3.786C9.887,3.896 9.842,3.961 9.832,4.021L9.727,4.086C9.689,4.133 9.909,4.14 9.912,4.146C9.92,4.17 9.646,4.209 9.697,4.267C9.764,4.364 10.28,4.129 10.198,4.143C10.357,4.063 10.219,4.054 10.13,4.008C10.099,3.856 10.074,3.621 9.979,3.526L10.041,3.455C9.895,3.243 9.786,3.711 9.786,3.711M9.421,4.097C9.361,4.122 9.301,4.084 9.36,4.024L9.422,3.949L9.416,3.915L9.465,3.853L9.516,3.84L9.621,3.767C9.652,3.773 9.725,3.833 9.7,3.871L9.643,3.941C9.631,4.059 9.511,4.058 9.423,4.097L9.419,4.097L9.421,4.097"/>
					    </g>
					</svg>
						<span>MOOC</span></a>
					</li>
				</ul>
				<ul class="wplms_configuration_checks">
				<?php
				$check =1;
				ob_start();

				?>
					<ul class="config">
					<?php
					$memory = $this->wplms_let_to_num( WP_MEMORY_LIMIT );
					$class='no';
					 if ( $memory >= 134217728 ) {$class='yes'; }
					?>
					<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php echo esc_html__( 'PHP Memory allocation','vibe'); ?></label>
					<?php
					if ( $memory < 134217728 ) { $check=0;
						echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 128MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'vibe' ), size_format( $memory ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
					} else {
						echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
					}
					?>
					</li>
					<?php 
					$class='no';
					$x = wp_max_upload_size();
					 
					 ?>
					<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e( 'WP Max Upload Size', 'vibe' ); ?></label>
					<?php echo size_format( $x ); ?></li>
					<?php if ( function_exists( 'ini_get' ) ) : ?>
							<?php $class='no'; $x = $this->wplms_let_to_num( ini_get('post_max_size') ) ; if($x >= 33554432){$class = 'yes';}else{if($check){$check=0;}} ?>
							<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e('PHP Post Max Size', 'vibe' ); ?></label>
							<?php echo size_format($x); ?></li>
							<?php
							$class='no'; $x = ini_get('max_execution_time') ; if($x >= 120){$class = 'yes';}
							?>					
							<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e('PHP Time Limit', 'vibe' ); ?></label>
							<?php echo vibe_sanitizer($x,'text').' s '; if($x < 200){printf( '<mark> - We recommend increasing this value to 200. See <a href="%s">Increasing PHP Time limit</a></mark>','https://premium.wpmudev.org/blog/increase-memory-limit/');} ?></li>
							<?php $class='yes';?>
							<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e( 'PHP Max Input Vars', 'vibe' ); ?></label>
								<?php echo ini_get('max_input_vars'); ?>
							</li>
					<?php endif; ?>
					</ul>
				<?php
				$configuration_checks = ob_get_clean();
				?>
				<li><strong><?php echo esc_html__( 'Configuration Check','vibe'); ?> <span class="
					<?php if($check){echo 'yes';}else{echo 'no';}?>"
					><?php if($check){echo __('Passed','vibe');}else{echo __('Failed','vibe');} ?></span></strong>
					<?php echo $configuration_checks; ?>
				</li>
				<li>
				<?php
					$wp_content = WP_CONTENT_DIR;
					
					$files_to_check = array(
										'' => '0755',
										'themes/wplms/plugins' => '0755',
										'themes/wplms/assets' => '0755',);
					
					$root = WP_CONTENT_DIR;
					
					ob_start();
					?>
					<ul class="config">
						<?php
						$check = 1;
					foreach($files_to_check as $k => $v){
						
						$path = $root.'/'.$k;

						$stat = @stat($path);
						$suggested = $v;
						$actual = substr(sprintf('%o', $stat['mode']), -4);

						if($check && version_compare($actual, $suggested) < 0 ){
							$check =0;
						}
						echo '<li class="'.((version_compare($actual, $suggested) < 0 ) ? 'no' : 'yes').'"><label>'.$k.'</label>
						'.$actual.''.((version_compare($actual, $suggested) < 0 ) ? '- '._x('[Recommended]','recommended label','vibe').'<mark> '.$suggested.'</mark>' : '').'
						</li>';
					}
					?>
					</ul>
					<?php
					$configuration_checks = ob_get_clean();
					?>
					<li><strong><?php _ex('File Permissions Check','installer label','vibe'); ?><span class="
					<?php if($check){echo 'yes';}else{echo 'no';}?>"
					><?php if($check){echo __('Passed','vibe');}else{echo __('Failed','vibe');} ?></span></strong>
					<?php echo $configuration_checks; ?>
					</li>
				</ul>

				<p style="font-size:80%;opacity:0.8"><?php echo sprintf( '<a href="'.esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ).'">No time right now?</a> If you don\'t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind! You can re-start setup wizard from WP Admin - Appearance - setup wizard %sImage Reference%s','[ <a href="'.esc_url(get_template_directory_uri() .'/assets/images/help_doc.png').'" target="_blank">','</a> ]'); ?>
				</p>
				<?php
			}
		}

		function wplms_let_to_num( $size ) {
			$l   = substr( $size, -1 );
			$ret = substr( $size, 0, -1 );
			switch ( strtoupper( $l ) ) {
				case 'P':
					$ret *= 1024;
				case 'T':
					$ret *= 1024;
				case 'G':
					$ret *= 1024;
				case 'M':
					$ret *= 1024;
				case 'K':
					$ret *= 1024;
			}
			return $ret;
		}

		public function filter_options( $options ) {
			return $options;
		}

		/**
		 *
		 * Handles save button from welcome page. This is to perform tasks when the setup wizard has already been run. E.g. reset defaults
		 *
		 * @since 1.2.5
		 */
		public function envato_setup_introduction_save() {

			check_admin_referer( 'envato-setup' );

			if ( ! empty( $_POST['reset-font-defaults'] ) && $_POST['reset-font-defaults'] == 'yes' ) {


				$file_name = get_template_directory() . '/style.custom.css';
				if ( file_exists( $file_name ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $file_name, '' );
				}
				?>
				<p>
					<strong><?php esc_html_e( 'Options have been reset. Please go to Appearance > Customize in the WordPress backend.','vibe' ); ?></strong>
				</p>
				<?php
				return true;
			}

			return false;
		}

		private function _get_plugins() {
			$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
			$plugins  = array(
				'all'      => array(), // Meaning: all plugins which still have open actions.
				'install'  => array(),
				'update'   => array(),
				'activate' => array(),
			);

			foreach ( $instance->plugins as $slug => $plugin ) {
				if ( $instance->is_plugin_active( $slug ) && false === $instance->does_plugin_have_update( $slug ) ) {
					// No need to display plugins if they are installed, up-to-date and active.
					continue;
				} else {
					$plugins['all'][ $slug ] = $plugin;

					if ( ! $instance->is_plugin_installed( $slug ) ) {
						$plugins['install'][ $slug ] = $plugin;
					} else {
						if ( false !== $instance->does_plugin_have_update( $slug ) ) {
							$plugins['update'][ $slug ] = $plugin;
						}

						if ( $instance->can_plugin_activate( $slug ) ) {
							$plugins['activate'][ $slug ] = $plugin;
						}
					}
				}
			}

			return $plugins;
		}

		/**
		 * Page setup
		 */
		public function envato_setup_default_plugins() {

			tgmpa_load_bulk_installer();
			// install plugins with TGM.
			if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
				die( 'Failed to find TGM' );
			}
			$url     = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'envato-setup' );
			$plugins = $this->_get_plugins();

			// copied from TGM

			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
			$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.

			if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
				return true; // Stop the normal page form from displaying, credential request form will be shown.
			}

			// Now we have some credentials, setup WP_Filesystem.
			if ( ! WP_Filesystem( $creds ) ) {
				// Our credentials were no good, ask the user for them again.
				request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );

				return true;
			}

			/* If we arrive here, we have the filesystem */

			?>
			<h1><?php esc_html_e( 'Required Plugins for Installation','vibe' ); ?></h1>
			<form method="post">

				<?php
				$plugins = $this->_get_plugins();
				if ( count( $plugins['all'] ) ) {
					?>
					<p><?php esc_html_e( 'Your website needs a few essential plugins. The following plugins will be installed or updated:','vibe' ); ?></p>
					<ul class="envato-wizard-plugins">
						<?php foreach ( $plugins['all'] as $slug => $plugin ) { ?>
							<li data-slug="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $plugin['name'] ); ?>
								<span>
    								<?php
								    $keys = array();
								    if ( isset( $plugins['install'][ $slug ] ) ) {
									    $keys[] = 'Installation';
								    }
								    if ( isset( $plugins['update'][ $slug ] ) ) {
									    $keys[] = 'Update';
								    }
								    if ( isset( $plugins['activate'][ $slug ] ) ) {
									    $keys[] = 'Activation';
								    }
								    echo implode( ' and ', $keys ) . ' required';
								    ?>
    							</span>
								<div class="spinner"></div>
							</li>
						<?php } ?>
					</ul>
					<?php
				} else {
					echo '<p><strong>' . esc_html_e( 'Good news! All plugins are already installed and up to date. Please continue.','vibe' ) . '</strong></p>';
				} ?>

				<p><?php esc_html_e( 'You can add and remove plugins later on from within WordPress.','vibe' ); ?></p>
				<p><strong>Note</strong> : If you see a "failed" message for a plugin then pelase get in touch with us at <a>facebook.com/vibethemes</a>, "ajax-error" messages are safe to ignore.</p>

				<div class="envato-setup-actions step">
					<div>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="large_next_button button-next"
					   data-callback="install_plugins"><?php esc_html_e( 'Continue','vibe' ); ?></a>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step' ,'vibe'); ?></a>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous step' ,'vibe'); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}

		public function ajax_plugins() {
			if ( ! check_ajax_referer( 'envato_setup_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found','vibe' ) ) );
			}
			$json = array();
			// send back some json we use to hit up TGM
			$plugins = $this->_get_plugins();
			// what are we doing with this plugin?
			foreach ( $plugins['activate'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {

					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-activate',
						'action2'       => - 1,
						'message'       => esc_html__( 'Activating Plugin','vibe' ),
					);
					break;
				}
			}
			foreach ( $plugins['update'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-update',
						'action2'       => - 1,
						'message'       => esc_html__( 'Updating Plugin','vibe' ),
					);
					break;
				}
			}
			foreach ( $plugins['install'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {

					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-install',
						'action2'       => - 1,
						'message'       => esc_html__( 'Installing Plugin','vibe' ),
					);
					break;
				}
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );
			} else {
				wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success' ,'vibe') ) );
			}
			exit;

		}

		private function _content_default_get() {

			$content = array();

			// find out what content is in our default json file.
			$available_content = $this->_get_json( 'default.json' );
			if(empty($available_content)){
				echo '<div class="message">';
				_ex('Unable to load file from server, reload this page. If issue persists, consult webhost,as your server is unable to load sample data from Amazon server.','wplms');
				echo '</div>';
			}else{
				foreach ( $available_content as $post_type => $post_data ) {
					if ( count( $post_data ) ) {
						$first           = current( $post_data );
						$post_type_title = ! empty( $first['type_title'] ) ? $first['type_title'] : ucwords( $post_type ) . 's';
						if ( $post_type_title == 'Navigation Menu Items' ) {
							$post_type_title = 'Navigation';
						}

						$check = apply_filters('wplms_import_post_type_content',1,$post_type);
						
						$content[ $post_type ] = array(
							'title'            => $post_type_title,
							'description'      => sprintf( esc_html__( 'This will create default %s as seen in the demo.','vibe' ), $post_type_title ),
							'pending'          => esc_html__( 'Pending.','vibe' ),
							'installing'       => esc_html__( 'Installing.','vibe' ),
							'success'          => esc_html__( 'Success.' ,'vibe'),
							'install_callback' => array( $this, '_content_install_type' ),
							'checked'          => $this->is_possible_upgrade()?0:$check,
							'disabled'		   => !$check,
							// dont check if already have content installed.
						);
					}
				}
			}

			$content['widgets'] = array(
				'title'            => esc_html__( 'Widgets' ,'vibe'),
				'description'      => esc_html__( 'Insert default sidebar widgets as seen in the demo.' ,'vibe'),
				'pending'          => esc_html__( 'Pending.','vibe' ),
				'installing'       => esc_html__( 'Installing Default Widgets.' ,'vibe'),
				'success'          => esc_html__( 'Success.','vibe' ),
				'install_callback' => array( $this, '_content_install_widgets' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			

			$content['options_panel'] = array(
				'title'            => esc_html__( 'Vibe Options Panel','vibe' ),
				'description'      => esc_html__( 'Configure options panel.','vibe' ),
				'pending'          => esc_html__( 'Pending.','vibe' ),
				'installing'       => esc_html__( 'Installing options panel settings.','vibe' ),
				'success'          => esc_html__( 'Success.','vibe' ),
				'install_callback' => array( $this, '_content_options_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			$content['customizer'] = array(
				'title'            => esc_html__( 'Customizer','vibe' ),
				'description'      => esc_html__( 'Configure customizer settings.' ,'vibe'),
				'pending'          => esc_html__( 'Pending.' ,'vibe'),
				'installing'       => esc_html__( 'Installing customiser settings.','vibe' ),
				'success'          => esc_html__( 'Success.','vibe' ),
				'install_callback' => array( $this, '_content_install_customizer' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			
			$content['slider'] = array(
				'title'            => esc_html__( 'Slider' ,'vibe'),
				'description'      => esc_html__( 'Import sliders used in the demo' ,'vibe'),
				'pending'          => esc_html__( 'Pending.' ,'vibe'),
				'installing'       => esc_html__( 'Downloading Slider.','vibe' ),
				'success'          => esc_html__( 'Success.','vibe' ),
				'install_callback' => array( $this, '_content_install_slider' ),
				'checked'          => 0,
				// dont check if already have content installed.
			);

			$content['settings'] = array(
				'title'            => esc_html__( 'Settings' ,'vibe'),
				'description'      => esc_html__( 'Configure default settings (menus locations, widget connections, set home page, link course units, quiz questions etc).' ,'vibe'),
				'pending'          => esc_html__( 'Pending.','vibe' ),
				'installing'       => esc_html__( 'Installing Default Settings.','vibe' ),
				'success'          => esc_html__( 'Success.','vibe' ),
				'install_callback' => array( $this, '_content_install_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);

			$content['users'] = array(
				'title'            => esc_html__( 'Users','vibe' ),
				'description'      => esc_html__( 'Configure sample users & profile fields.','vibe' ),
				'pending'          => esc_html__( 'Pending.','vibe' ),
				'installing'       => esc_html__( 'Installing user settings.','vibe' ),
				'success'          => esc_html__( 'Success.','vibe' ),
				'install_callback' => array( $this, '_content_setup_users' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			
			$content = apply_filters( $this->theme_name . '_theme_setup_wizard_content', $content );

			return $content;

		}

		/**
		 * Page setup
		 */
		public function envato_setup_default_content() {
			?>
			<h1><?php esc_html_e( 'Import Demo Content','vibe' ); ?></h1>
			<form method="post">
					<p>It's time to insert some default content for your new WordPress website. Choose what you would like inserted below and click Continue. It is recommended to leave everything selected. Once inserted, this content can be managed from the WordPress admin dashboard.
				<hr><p><strong>Note</strong>&nbsp;&nbsp;If you do not see "Posts", "Pages" in import items section. Make sure to reload this page. Make sure you are connected to the internet  before content re-import. See <a href="https://www.youtube.com/watch?v=xXMg6VjYoWw" target="_blank">video</a> or <a href="https://wplms.io/support/knowledge-base/wplms-setup-wizard-import-content-not-loading/" target="_blank">document</a> help</a></p>
				<p style="font-size:80%;opacity:0.8;">Re-installing content from another demo, <a class="clear_imported_posts" data-security="<?php  echo wp_create_nonce('wplms_clear_imported_posts'); ?>"> clear cache </a></p>


				<table class="envato-setup-pages" cellspacing="0">
					<thead>
					<tr>
						<td class="check"></td>
						<th class="item"><?php esc_html_e( 'Item' ,'vibe'); ?></th>
						<th class="description"><?php esc_html_e( 'Description','vibe' ); ?></th>
						<th class="status"><?php esc_html_e( 'Status' ,'vibe'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php 

					foreach ( $this->_content_default_get() as $slug => $default ) { ?>
						<tr class="envato_default_content" data-content="<?php echo esc_attr( $slug ); ?>">
							<td>
								<input type="checkbox" name="default_content[<?php echo esc_attr( $slug ); ?>]"
								       class="envato_default_content"
								       id="default_content_<?php echo esc_attr( $slug ); ?>"
								       value="1" <?php echo ( ! isset( $default['checked'] ) || $default['checked'] ) ? ' checked' : ''; ?> <?php echo (  isset( $default['disabled'] ) && $default['disabled'] ) ? ' disabled' : ''; ?>>
							</td>
							<td><label
									for="default_content_<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $default['title'] ); ?></label>
							</td>
							<td class="description"><?php echo esc_html( $default['description'] ); ?></td>
							<td class="status"><span><?php echo esc_html( $default['pending'] ); ?></span>
								<div class="spinner"></div>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>

				<div class="envato-setup-actions step">
					<div><a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="large_next_button button-next"
					   data-callback="install_content"><?php esc_html_e( 'Import Content' ,'vibe'); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   ><?php esc_html_e( 'Skip this step','vibe' ); ?></a>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous step','vibe' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}

		public function ajax_content() { //here

			//cehck
			$content = $this->_content_default_get();
			if ( ! check_ajax_referer( 'envato_setup_nonce', 'wpnonce' ) || empty( $_POST['content'] ) && isset( $content[ $_POST['content'] ] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No content Found','vibe' ) ) );
			}

			$json         = false;
			$this_content = $content[ $_POST['content'] ];

			if ( isset( $_POST['proceed'] ) ) {
				// install the content!

				$this->log( ' -!! STARTING SECTION for ' . $_POST['content'] );

				// init delayed posts from transient.
				$this->delay_posts = get_transient( 'delayed_posts' );
				if ( ! is_array( $this->delay_posts ) ) {
					$this->delay_posts = array();
				}

				if ( ! empty( $this_content['install_callback'] ) ) {
					if ( $result = call_user_func( $this_content['install_callback'] ) ) {

						$this->log( ' -- FINISH. Writing ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts to transient ' );
						set_transient( 'delayed_posts', $this->delay_posts, 60 * 60 * 24 );

						if ( is_array( $result ) && isset( $result['retry'] ) ) {
							// we split the stuff up again.
							$json = array(
								'url'         => admin_url( 'admin-ajax.php' ),
								'action'      => 'envato_setup_content',
								'proceed'     => 'true',
								'retry'       => time(),
								'retry_count' => $result['retry_count'],
								'content'     => $_POST['content'],
								'_wpnonce'    => wp_create_nonce( 'envato_setup_nonce' ),
								'message'     => $this_content['installing'],
								'logs'        => $this->logs,
								'errors'      => $this->errors,
							);
						} else {
							$json = array(
								'done'    => 1,
								'message' => $this_content['success'],
								'debug'   => $result,
								'logs'    => $this->logs,
								'errors'  => $this->errors,
							);
						}
					}
				}
			} else {

				$json = array(
					'url'      => admin_url( 'admin-ajax.php' ),
					'action'   => 'envato_setup_content',
					'proceed'  => 'true',
					'content'  => $_POST['content'],
					'_wpnonce' => wp_create_nonce( 'envato_setup_nonce' ),
					'message'  => $this_content['installing'],
					'logs'     => $this->logs,
					'errors'   => $this->errors,
				);
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );

			} else {
				wp_send_json( array(
					'error'   => 1,
					'message' => esc_html__( 'Error','vibe' ),
					'logs'    => $this->logs,
					'errors'  => $this->errors,
				) );
			}

			exit;

		}


		private function _imported_term_id( $original_term_id, $new_term_id = false ) {
			$terms = get_transient( 'importtermids' );
			if ( ! is_array( $terms ) ) {
				$terms = array();
			}
			if ( $new_term_id ) {
				if ( ! isset( $terms[ $original_term_id ] ) ) {
					$this->log( 'Insert old TERM ID ' . $original_term_id . ' as new TERM ID: ' . $new_term_id );
				} else if ( $terms[ $original_term_id ] != $new_term_id ) {
					$this->error( 'Replacement OLD TERM ID ' . $original_term_id . ' overwritten by new TERM ID: ' . $new_term_id );
				}
				$terms[ $original_term_id ] = $new_term_id;
				set_transient( 'importtermids', $terms, 60 * 60 * 24 );
			} else if ( $original_term_id && isset( $terms[ $original_term_id ] ) ) {
				return $terms[ $original_term_id ];
			}

			return false;
		}


		public function vc_post( $post_id = false ) {

			$vc_post_ids = get_transient( 'import_vc_posts' );
			if ( ! is_array( $vc_post_ids ) ) {
				$vc_post_ids = array();
			}
			if ( $post_id ) {
				$vc_post_ids[ $post_id ] = $post_id;
				set_transient( 'import_vc_posts', $vc_post_ids, 60 * 60 * 24 );
			} else {

				$this->log( 'Processing vc pages 2: ' );

				return;
				if ( class_exists( 'Vc_Manager' ) && class_exists( 'Vc_Post_Admin' ) ) {
					$this->log( $vc_post_ids );
					$vc_manager = Vc_Manager::getInstance();
					$vc_base    = $vc_manager->vc();
					$post_admin = new Vc_Post_Admin();
					foreach ( $vc_post_ids as $vc_post_id ) {
						$this->log( 'Save ' . $vc_post_id );
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
						//twice? bug?
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
					}
				}
			}

		}

		public function elementor_post( $post_id = false ) {

			// regenrate the CSS for this Elementor post
			if( class_exists( 'Elementor\Post_CSS_File' ) ) {
                $post_css = new Elementor\Post_CSS_File($post_id);
				$post_css->update();
			}
			if(class_exists('Elementor\Core\Files\CSS\Post')){
				$post_css = new Elementor\Core\Files\CSS\Post($post_id);
				$post_css->update();
			}
		}

		private function _imported_post_id( $original_id = false, $new_id = false ) {
			if ( is_array( $original_id ) || is_object( $original_id ) ) {
				return false;
			}
			$post_ids = get_transient( 'importpostids' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $new_id ) {
				if ( ! isset( $post_ids[ $original_id ] ) ) {
					$this->log( 'Insert old ID ' . $original_id . ' as new ID: ' . $new_id );
				} else if ( $post_ids[ $original_id ] != $new_id ) {
					$this->error( 'Replacement OLD ID ' . $original_id . ' overwritten by new ID: ' . $new_id );
				}
				$post_ids[ $original_id ] = $new_id;
				set_transient( 'importpostids', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _post_orphans( $original_id = false, $missing_parent_id = false ) {
			$post_ids = get_transient( 'postorphans' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $missing_parent_id ) {
				$post_ids[ $original_id ] = $missing_parent_id;
				set_transient( 'postorphans', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _cleanup_imported_ids() {
			// loop over all attachments and assign the correct post ids to those attachments.

		}

		private $delay_posts = array();

		private function _delay_post_process( $post_type, $post_data ) {
			if ( ! isset( $this->delay_posts[ $post_type ] ) ) {
				$this->delay_posts[ $post_type ] = array();
			}
			$this->delay_posts[ $post_type ][ $post_data['post_id'] ] = $post_data;

		}

		// return the difference in length between two strings
		public function cmpr_strlen( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}

		private function _process_post_data( $post_type, $post_data, $delayed = 0, $debug = false ) {

			$this->log( " Processing $post_type " . $post_data['post_id'] );

			$original_post_data = $post_data;

			if ( $debug ) {
				echo "HERE\n";
			}
			if ( ! post_type_exists( $post_type ) ) {
				return false;
			}
			if ( ! $debug && $this->_imported_post_id( $post_data['post_id'] ) ) {
				return true; // already done :)
			}

			if ( empty( $post_data['post_title'] ) && empty( $post_data['post_name'] ) ) {
				// this is menu items
				$post_data['post_name'] = $post_data['post_id'];
			}

			$post_data['post_type'] = $post_type;

			$post_parent = (int) $post_data['post_parent'];
			if ( $post_parent ) {
				// if we already know the parent, map it to the new local ID
				if ( $this->_imported_post_id( $post_parent ) ) {
					$post_data['post_parent'] = $this->_imported_post_id( $post_parent );
					// otherwise record the parent for later
				} else {
					$this->_post_orphans( intval( $post_data['post_id'] ), $post_parent );
					$post_data['post_parent'] = 0;
				}
			}

			// check if already exists
			if ( ! $debug ) {
				if ( empty( $post_data['post_title'] ) && ! empty( $post_data['post_name'] ) ) {
					global $wpdb;
					$sql     = "
					SELECT ID, post_name, post_parent, post_type
					FROM $wpdb->posts
					WHERE post_name = %s
					AND post_type = %s
				";
					$pages   = $wpdb->get_results( $wpdb->prepare( $sql, array(
						$post_data['post_name'],
						$post_type,
					) ), OBJECT_K );
					$foundid = 0;
					foreach ( (array) $pages as $page ) {
						if ( $page->post_name == $post_data['post_name'] && empty( $page->post_title ) ) {
							$foundid = $page->ID;
						}
					}
					if ( $foundid ) {
						$this->_imported_post_id( $post_data['post_id'], $foundid );

						return true;
					}
				}
				// dont use post_exists because it will dupe up on media with same name but different slug
				if ( ! empty( $post_data['post_title'] ) && ! empty( $post_data['post_name'] ) ) {
					global $wpdb;
					$sql     = "
					SELECT ID, post_name, post_parent, post_type
					FROM $wpdb->posts
					WHERE post_name = %s
					AND post_title = %s
					AND post_type = %s
					";
					$pages   = $wpdb->get_results( $wpdb->prepare( $sql, array(
						$post_data['post_name'],
						$post_data['post_title'],
						$post_type,
					) ), OBJECT_K );
					$foundid = 0;
					foreach ( (array) $pages as $page ) {
						if ( $page->post_name == $post_data['post_name'] ) {
							$foundid = $page->ID;
						}
					}
					if ( $foundid ) {
						$this->_imported_post_id( $post_data['post_id'], $foundid );

						return true;
					}
				}
			}

			switch ( $post_type ) {
				case 'attachment':
					// import media via url
					if ( ! empty( $post_data['guid'] ) ) {

						// check if this has already been imported.
						$old_guid = $post_data['guid'];
						if ( $this->_imported_post_id( $old_guid ) ) {
							return true; // alrady done;
						}
						// ignore post parent, we haven't imported those yet.
						// $file_data = wp_remote_get($post_data['guid']);
						$remote_url = $post_data['guid'];

						$post_data['upload_date'] = date( 'Y/m', strtotime( $post_data['post_date_gmt'] ) );
						if ( isset( $post_data['meta'] ) ) {
							foreach ( $post_data['meta'] as $key => $meta ) {
								if ( $key == '_wp_attached_file' ) {
									foreach ( (array) $meta as $meta_val ) {
										if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta_val, $matches ) ) {
											$post_data['upload_date'] = $matches[0];
										}
									}
								}
							}
						}

						$upload = $this->_fetch_remote_file( $remote_url, $post_data );

						if ( ! is_array( $upload ) || is_wp_error( $upload ) ) {
							// todo: error
							return false;
						}

						if ( $info = wp_check_filetype( $upload['file'] ) ) {
							$post['post_mime_type'] = $info['type'];
						} else {
							return false;
						}

						$post_data['guid'] = $upload['url'];

						// as per wp-admin/includes/upload.php
						$post_id = wp_insert_attachment( $post_data, $upload['file'] );
						if($post_id) {

							if ( ! empty( $post_data['meta'] ) ) {
								foreach ( $post_data['meta'] as $meta_key => $meta_val ) {
									if($meta_key != '_wp_attached_file' && !empty($meta_val)) {
										update_post_meta( $post_id, $meta_key, $meta_val );
									}
								}
							}

							wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

							// remap resized image URLs, works by stripping the extension and remapping the URL stub.
							if ( preg_match( '!^image/!', $info['type'] ) ) {
								$parts = pathinfo( $remote_url );
								$name  = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

								$parts_new = pathinfo( $upload['url'] );
								$name_new  = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

								$this->_imported_post_id( $parts['dirname'] . '/' . $name, $parts_new['dirname'] . '/' . $name_new );
							}
							$this->_imported_post_id( $post_data['post_id'], $post_id );
							//$this->_imported_post_id( $old_guid, $post_id );
						}

					}
					break;	
				default:
					// work out if we have to delay this post insertion

					$replace_meta_vals = array(
						/*'_vc_post_settings'                                => array(
							'posts'      => array( 'item' ),
							'taxonomies' => array( 'taxonomies' ),
						),
						'_menu_item_object_id|_menu_item_menu_item_parent' => array(
							'post' => true,
						),*/
					);

					if ( ! empty( $post_data['meta'] ) && is_array( $post_data['meta'] ) ) {

						// replace any elementor post data:

						// fix for double json encoded stuff:
						foreach ( $post_data['meta'] as $meta_key => $meta_val ) {
							if ( is_string( $meta_val ) && strlen( $meta_val ) && $meta_val[0] == '[' ) {
								$test_json = @json_decode( $meta_val, true );
								if ( is_array( $test_json ) ) {
									$post_data['meta'][ $meta_key ] = $test_json;
								}
							}
						}

						array_walk_recursive( $post_data['meta'], array( $this, '_elementor_id_import' ) );

						// replace menu data:
						// work out what we're replacing. a tax, page, term etc..

						if(!empty($post_data['meta']['_menu_item_menu_item_parent'])) {
							$this->log[]='finding id for ...'.$post_data['meta']['_menu_item_menu_item_parent']. '##';
							$new_parent_id = $this->_imported_post_id( $post_data['meta']['_menu_item_menu_item_parent'] );
							if(!$new_parent_id) {
								if ( $delayed ) {
									// already delayed, unable to find this meta value, skip inserting it
									$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
								} else {
									$this->error( 'Unable to find replacement. Delaying.... ' );
									$this->_delay_post_process( $post_type, $original_post_data );
									return false;
								}
							}
							$post_data['meta']['_menu_item_menu_item_parent'] = $new_parent_id;
						}
						if(isset($post_data['meta'][ '_menu_item_type' ])){

							switch($post_data['meta'][ '_menu_item_type' ]){
								case 'post_type':
									if(!empty($post_data['meta']['_menu_item_object_id'])) {
										$new_parent_id = $this->_imported_post_id( $post_data['meta']['_menu_item_object_id'] );

										$this->log(' #3 FOUND id '.$post_data['meta']['_menu_item_object_id'].' - '.$new_parent_id);

										if(!$new_parent_id) {
											if ( $delayed ) {
												// already delayed, unable to find this meta value, skip inserting it
												$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
											} else {
												$this->error( 'Unable to find replacement. Delaying.... ' );
												$this->_delay_post_process( $post_type, $original_post_data );
												return false;
											}
										}
										$post_data['meta']['_menu_item_object_id'] = $new_parent_id;
									}
									break;
								case 'taxonomy':
									if(!empty($post_data['meta']['_menu_item_object_id'])) {
										$new_parent_id = $this->_imported_term_id( $post_data['meta']['_menu_item_object_id'] );
										if(!$new_parent_id) {
											if ( $delayed ) {
												// already delayed, unable to find this meta value, skip inserting it
												$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
											} else {
												$this->error( 'Unable to find replacement. Delaying.... ' );
												$this->_delay_post_process( $post_type, $original_post_data );
												return false;
											}
										}
										$post_data['meta']['_menu_item_object_id'] = $new_parent_id;
									}
									break;
							}
						}

						// please ignore this horrible loop below:
						// it was an attempt to automate different visual composer meta key replacements
						// but I'm not using visual composer any more, so ignoring it.
						foreach ( $replace_meta_vals as $meta_key_to_replace => $meta_values_to_replace ) {

							$meta_keys_to_replace   = explode( '|', $meta_key_to_replace );
							$success                = false;
							$trying_to_find_replace = false;
							foreach ( $meta_keys_to_replace as $meta_key ) {

								if ( ! empty( $post_data['meta'][ $meta_key ] ) ) {

									$meta_val = $post_data['meta'][ $meta_key ];

									if ( $debug ) {
										echo "Meta key: $meta_key \n";
										var_dump( $meta_val );
									}

									// if we're replacing a single post/tax value.
									if ( isset( $meta_values_to_replace['post'] ) && $meta_values_to_replace['post'] && (int) $meta_val > 0 ) {
										$trying_to_find_replace = true;
										$new_meta_val           = $this->_imported_post_id( $meta_val );
										if ( $new_meta_val ) {
											$post_data['meta'][ $meta_key ] = $new_meta_val;
											$success                        = true;
										} else {
											$success = false;
											break;
										}
									}
									if ( isset( $meta_values_to_replace['taxonomy'] ) && $meta_values_to_replace['taxonomy'] && (int) $meta_val > 0 ) {
										$trying_to_find_replace = true;
										$new_meta_val           = $this->_imported_term_id( $meta_val );
										if ( $new_meta_val ) {
											$post_data['meta'][ $meta_key ] = $new_meta_val;
											$success                        = true;
										} else {
											$success = false;
											break;
										}
									}
									if ( is_array( $meta_val ) && isset( $meta_values_to_replace['posts'] ) ) {

										foreach ( $meta_values_to_replace['posts'] as $post_array_key ) {

											$this->log( 'Trying to find/replace "' . $post_array_key . '"" in the ' . $meta_key . ' sub array:' );
											//$this->log(var_export($meta_val,true));

											$this_success = false;
											array_walk_recursive( $meta_val, function ( &$item, $key ) use ( &$trying_to_find_replace, $post_array_key, &$success, &$this_success, $post_type, $original_post_data, $meta_key, $delayed ) {
												if ( $key == $post_array_key && (int) $item > 0 ) {
													$trying_to_find_replace = true;
													$new_insert_id          = $this->_imported_post_id( $item );
													if ( $new_insert_id ) {
														$success      = true;
														$this_success = true;
														$this->log( 'Found' . $meta_key . ' -> ' . $post_array_key . ' replacement POST ID insert for ' . $item . ' ( as ' . $new_insert_id . ' ) ' );
														$item = $new_insert_id;
													} else {
														$this->error( 'Unable to find ' . $meta_key . ' -> ' . $post_array_key . ' POST ID insert for ' . $item . ' ' );
													}
												}
											} );
											if ( $this_success ) {
												$post_data['meta'][ $meta_key ] = $meta_val;
											}
										}
										foreach ( $meta_values_to_replace['taxonomies'] as $post_array_key ) {

											$this->log( 'Trying to find/replace "' . $post_array_key . '"" TAXONOMY in the ' . $meta_key . ' sub array:' );
											//$this->log(var_export($meta_val,true));

											$this_success = false;
											array_walk_recursive( $meta_val, function ( &$item, $key ) use ( &$trying_to_find_replace, $post_array_key, &$success, &$this_success, $post_type, $original_post_data, $meta_key, $delayed ) {
												if ( $key == $post_array_key && (int) $item > 0 ) {
													$trying_to_find_replace = true;
													$new_insert_id          = $this->_imported_term_id( $item );
													if ( $new_insert_id ) {
														$success      = true;
														$this_success = true;
														$this->log( 'Found' . $meta_key . ' -> ' . $post_array_key . ' replacement TAX ID insert for ' . $item . ' ( as ' . $new_insert_id . ' ) ' );
														$item = $new_insert_id;
													} else {
														$this->error( 'Unable to find ' . $meta_key . ' -> ' . $post_array_key . ' TAX ID insert for ' . $item . ' ' );
													}
												}
											} );

											if ( $this_success ) {
												$post_data['meta'][ $meta_key ] = $meta_val;
											}
										}
									}

									if ( $success ) {
										if ( $debug ) {
											echo "Meta key AFTER REPLACE: $meta_key \n";
											//print_r( $post_data['meta'] );
										}
									}
								}
							}
							if ( $trying_to_find_replace ) {
								$this->log( 'Trying to find/replace postmeta "' . $meta_key_to_replace . '" ' );
								if ( ! $success ) {
									// failed to find a replacement.
									if ( $delayed ) {
										// already delayed, unable to find this meta value, skip inserting it
										$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
									} else {
										$this->error( 'Unable to find replacement. Delaying.... ' );
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								} else {
									$this->log( 'SUCCESSSS ' );
								}
							}
						}
					}

					$post_data['post_content'] = $this->_parse_gallery_shortcode_content($post_data['post_content']);

					// we have to fix up all the visual composer inserted image ids
					$replace_post_id_keys = array(
						'parallax_image',
						'image',
						'item', // vc grid
						'post_id',
					);
					foreach ( $replace_post_id_keys as $replace_key ) {
						if ( preg_match_all( '# ' . $replace_key . '="(\d+)"#', $post_data['post_content'], $matches ) ) {
							foreach ( $matches[0] as $match_id => $string ) {
								$new_id = $this->_imported_post_id( $matches[1][ $match_id ] );
								if ( $new_id ) {
									$post_data['post_content'] = str_replace( $string, ' ' . $replace_key . '="' . $new_id . '"', $post_data['post_content'] );
								} else {
									$this->error( 'Unable to find POST replacement for ' . $replace_key . '="' . $matches[1][ $match_id ] . '" in content.' );
									if ( $delayed ) {
										// already delayed, unable to find this meta value, insert it anyway.

									} else {

										$this->error( 'Adding ' . $post_data['post_id'] . ' to delay listing.' );
										//                                      echo "Delaying post id ".$post_data['post_id']."... \n\n";
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								}
							}
						}
					}
					$replace_tax_id_keys = array(
						'taxonomies',
					);
					foreach ( $replace_tax_id_keys as $replace_key ) {
						if ( preg_match_all( '# ' . $replace_key . '="(\d+)"#', $post_data['post_content'], $matches ) ) {
							foreach ( $matches[0] as $match_id => $string ) {
								$new_id = $this->_imported_term_id( $matches[1][ $match_id ] );
								if ( $new_id ) {
									$post_data['post_content'] = str_replace( $string, ' ' . $replace_key . '="' . $new_id . '"', $post_data['post_content'] );
								} else {
									$this->error( 'Unable to find TAXONOMY replacement for ' . $replace_key . '="' . $matches[1][ $match_id ] . '" in content.' );
									if ( $delayed ) {
										// already delayed, unable to find this meta value, insert it anyway.
									} else {
										//                                      echo "Delaying post id ".$post_data['post_id']."... \n\n";
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								}
							}
						}
					}

					$post_id = wp_insert_post( $post_data, true );

					if ( ! is_wp_error( $post_id ) ) {
						$this->_imported_post_id( $post_data['post_id'], $post_id );
						// add/update post meta
						if ( ! empty( $post_data['meta'] ) ) {
							foreach ( $post_data['meta'] as $meta_key => $meta_val ) {

								// if the post has a featured image, take note of this in case of remap
								if ( '_thumbnail_id' == $meta_key ) {
									/// find this inserted id and use that instead.
									$inserted_id = $this->_imported_post_id( intval( $meta_val ) );
									if ( $inserted_id ) {
										$meta_val = $inserted_id;
									}
								}

								if(!is_numeric($meta_key)){
									update_post_meta( $post_id, $meta_key, $meta_val );
								}

							}
						}
						if ( ! empty( $post_data['terms'] ) ) {
							$terms_to_set = array();
							foreach ( $post_data['terms'] as $term_slug => $terms ) {
								foreach ( $terms as $term ) {
									$taxonomy = $term['taxonomy'];
									if ( taxonomy_exists( $taxonomy ) ) {
										$term_exists = term_exists( $term['slug'], $taxonomy );
										$term_id     = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
										if ( ! $term_id ) {
											if ( ! empty( $term['parent'] ) ) {
												// see if we have imported this yet?
												$term['parent'] = $this->_imported_term_id( $term['parent'] );
											}
											$t = wp_insert_term( $term['name'], $taxonomy, $term );
											if ( ! is_wp_error( $t ) ) {
												$term_id = $t['term_id'];
											} else {
												// todo - error
												continue;
											}
										}
										$this->_imported_term_id( $term['term_id'], $term_id );
										// add the term meta.
										if($term_id && !empty($term['meta']) && is_array($term['meta'])){
											foreach($term['meta'] as $meta_key => $meta_val){
											    // we have to replace certain meta_key/meta_val
                                                // e.g. thumbnail id from woocommerce product categories.
                                                switch($meta_key){
                                                    case 'thumbnail_id':
                                                        if( $new_meta_val = $this->_imported_post_id($meta_val) ){
                                                            // use this new id.
                                                            $meta_val = $new_meta_val;
                                                        }
                                                        break;
                                                    case 'course_cat_thumbnail_id':
                                                    	 if( $new_meta_val = $this->_imported_post_id($meta_val) ){
                                                            // use this new id.
                                                            $meta_val = $new_meta_val;
                                                        }
                                                    break;
                                                }
												update_term_meta( $term_id, $meta_key, $meta_val );
											}
										}
										$terms_to_set[ $taxonomy ][] = intval( $term_id );
									}
								}
							}
							foreach ( $terms_to_set as $tax => $ids ) {
								wp_set_post_terms( $post_id, $ids, $tax );
							}
						}

						// procses visual composer just to be sure.
						if ( strpos( $post_data['post_content'], '[vc_' ) !== false ) {
							$this->vc_post( $post_id );
						}
						if ( !empty($post_data['meta']['_elementor_data']) || !!empty($post_data['meta']['_elementor_css']) ) {
							$this->elementor_post( $post_id );
						}
					}

					break;
			}

			return true;
		}

		private function _parse_gallery_shortcode_content($content){
			// we have to format the post content. rewriting images and gallery stuff
			$replace      = $this->_imported_post_id();
			$urls_replace = array();
			foreach ( $replace as $key => $val ) {
				if ( $key && $val && ! is_numeric( $key ) && ! is_numeric( $val ) ) {
					$urls_replace[ $key ] = $val;
				}
			}
			if ( $urls_replace ) {
				uksort( $urls_replace, array( &$this, 'cmpr_strlen' ) );
				foreach ( $urls_replace as $from_url => $to_url ) {
					$content = str_replace( $from_url, $to_url, $content );
				}
			}
			if ( preg_match_all( '#\[gallery[^\]]*\]#', $content, $matches ) ) {
				foreach ( $matches[0] as $match_id => $string ) {
					if ( preg_match( '#ids="([^"]+)"#', $string, $ids_matches ) ) {
						$ids = explode( ',', $ids_matches[1] );
						foreach ( $ids as $key => $val ) {
							$new_id = $val ? $this->_imported_post_id( $val ) : false;
							if ( ! $new_id ) {
								unset( $ids[ $key ] );
							} else {
								$ids[ $key ] = $new_id;
							}
						}
						$new_ids                   = implode( ',', $ids );
						$content = str_replace( $ids_matches[0], 'ids="' . $new_ids . '"', $content );
					}
				}
			}
			return $content;
		}

		public function _elementor_id_import( &$item, $key ) {
			
			if ( $key == 'id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'page' && ! empty( $item ) ) {

				if ( false !== strpos( $item, 'p.' ) ) {
					$new_id = str_replace('p.', '', $item);
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $new_id );
					if ( $new_meta_val ) {
						$item = 'p.' . $new_meta_val;
					}
				}else if(is_numeric($item)){
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $item );
					if ( $new_meta_val ) {
						$item = $new_meta_val;
					}
				}
			}
			if ( $key == 'post_id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'url' && ! empty( $item ) && strstr( $item, 'ocalhost' ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( ($key == 'shortcode' || $key == 'editor') && ! empty( $item ) ) {
				// we have to fix the [contact-form-7 id=133] shortcode issue.
				$item = $this->_parse_gallery_shortcode_content($item);

			}
		}

		public function _content_install_type($type=null,$index=null) {
			$post_type = ! empty( $_POST['content'] ) ? $_POST['content'] : false;
			if(!empty($type)){
				$post_type= $type;
			}
			$all_data  = $this->_get_json( 'default.json' );
			if ( ! $post_type || ! isset( $all_data[ $post_type ] ) ) {
				return false;
			}
			$limit = 10 + ( isset( $_REQUEST['retry_count'] ) ? (int) $_REQUEST['retry_count'] : 0 );
			if(!isset($_REQUEST['retry_count']) && !empty($index)){
				$limit = 5 + ( isset( $index) ? (int) $index : 0 );
			}
			$x  = 0;
			
			$this->logs[]='#1 - Inside the Nav menu item - '.$post_type;
			if($post_type == 'nav_menu_item'){
				$style = vibe_get_site_style();
				if(empty($style)){$style = $this->get_default_theme_style();}
				
				if($style == 'demo1'){
					$x = $this->_imported_post_id(2218);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2218, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2216, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2237, $member_directory->ID );

						$this->logs[]='#2 - Sample post ids Imported - demo1';
					}
				}else if($style == 'demo2'){
					$x = $this->_imported_post_id(2108);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2108, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2121, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2122, $member_directory->ID );

						$this->logs[]='#2 - Sample post ids Imported - demo2';
					}
				}else if($style == 'demo3'){
					$x = $this->_imported_post_id(2140);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2140, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2158, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2159, $member_directory->ID );

						$this->logs[]='#2 - Sample post ids Imported - demo2';
					}
				}else if( $style == 'demo4'){
					$x = $this->_imported_post_id(2172);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2172, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2186, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2187, $member_directory->ID );
					}
				}else if($style == 'demo6'){
					$x = $this->_imported_post_id(2140);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2140, $course_directory->ID );

					}
				}else if($style == 'demo7'){
					$x = $this->_imported_post_id(2140);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2140, $course_directory->ID );

					}
				}else if($style == 'demo8'){
					$x = $this->_imported_post_id(25);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 25, $course_directory->ID );

					}
				}else if($style == 'default'){
					$x = $this->_imported_post_id(1994);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 1994, $course_directory->ID );

					}
				}
			}

			foreach ( $all_data[ $post_type ] as $post_data ) {

				$this->_process_post_data( $post_type, $post_data );

				if ( $x ++ > $limit ) {
					return array( 'retry' => 1, 'retry_count' => $limit );
				}
			}

			$this->_handle_delayed_posts();
			$this->_handle_post_orphans();

			return true;

		}

		private function _handle_post_orphans() {
			$orphans = $this->_post_orphans();
			foreach ( $orphans as $original_post_id => $original_post_parent_id ) {
				if ( $original_post_parent_id ) {
					if ( $this->_imported_post_id( $original_post_id ) && $this->_imported_post_id( $original_post_parent_id ) ) {
						$post_data                = array();
						$post_data['ID']          = $this->_imported_post_id( $original_post_id );
						$post_data['post_parent'] = $this->_imported_post_id( $original_post_parent_id );
						wp_update_post( $post_data );
						$this->_post_orphans( $original_post_id, 0 ); // ignore future
					}
				}
			}
		}

		private function _handle_delayed_posts( $last_delay = false ) {

			$this->log( ' ---- Processing ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts' );
			for ( $x = 1; $x < 4; $x ++ ) {
				foreach ( $this->delay_posts as $delayed_post_type => $delayed_post_datas ) {
					foreach ( $delayed_post_datas as $delayed_post_id => $delayed_post_data ) {
						if ( $this->_imported_post_id( $delayed_post_data['post_id'] ) ) {
							$this->log( $x . ' - Successfully processed ' . $delayed_post_type . ' ID ' . $delayed_post_data['post_id'] . ' previously.' );
							unset( $this->delay_posts[ $delayed_post_type ][ $delayed_post_id ] );
							$this->log( ' ( ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts remain ) ' );
						} else if ( $this->_process_post_data( $delayed_post_type, $delayed_post_data, $last_delay ) ) {
							$this->log( $x . ' - Successfully found delayed replacement for ' . $delayed_post_type . ' ID ' . $delayed_post_data['post_id'] . '.' );
							// successfully inserted! don't try again.
							unset( $this->delay_posts[ $delayed_post_type ][ $delayed_post_id ] );
							$this->log( ' ( ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts remain ) ' );
						}
					}
				}
			}
		}

		private function _fetch_remote_file( $url, $post ) {
			// extract the file name and extension from the url
			$file_name  = basename( $url );
			$upload     = false;

			if ( ! $upload || $upload['error'] ) {
				// get placeholder file in the upload dir with a unique, sanitized filename
				$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
				if ( $upload['error'] ) {
					return new WP_Error( 'upload_dir_error', $upload['error'] );
				}

				$max_size = (int) apply_filters( 'import_attachment_size_limit', 0 );

				if ( empty( $this->debug ) ) {

					//Change to Uploaded file path if uploaded
					$path = get_option('wplms_export_import_content_path');
					if( !empty($path) ){
						$vibe_url = site_url().'/wp-content/uploads/upload_demos/'.basename($path).'/images/'.$file_name;
					}else{
						if(strpos($url, 'http://local.wordpress.dev') !== false || strpos($url, 'htt://themes.vibethemes.com') !== false){
							//No problem reported in images so far
							//$vibe_url = 'https://s3.console.aws.amazon.com/s3/buckets/wplmsdownloads/demodata/images/';
							$vibe_url = 'https://demos.wplms.io/demos/demodata/content/images/'.$file_name;
						}else{
							$vibe_url = $url;
						}
					}
				}

				$response = wp_remote_get( $vibe_url ,array('timeout' => 120));
				if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
					//
				}else{
					$local_file = trailingslashit( get_template_directory() ) . 'assets/images/title_bg.png';
					
					if ( is_file( $local_file ) && filesize( $local_file ) > 0 ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
						WP_Filesystem();
						global $wp_filesystem;
						$file_data = $wp_filesystem->get_contents( $local_file );
						$upload    = wp_upload_bits( $file_name, 0, $file_data, $post['upload_date'] );
						if ( $upload['error'] ) {
							return new WP_Error( 'upload_dir_error', $upload['error'] );
						}
					}
				}

				if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					$headers = $response['headers'];
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $upload['file'], $response['body'] );
					//
				} else {
					// required to download file failed.
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Remote server did not respond','vibe' ) );
				}

				$filesize = filesize( $upload['file'] );

				if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Remote file is incorrect size','vibe' ) );
				}

				if ( 0 == $filesize ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Zero size file downloaded','vibe' ) );
				}

				if ( ! empty( $max_size ) && $filesize > $max_size ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', sprintf( esc_html__( 'Remote file is too large, limit is %s','vibe' ), size_format( $max_size ) ) );
				}
			}

			// keep track of the old and new urls so we can substitute them later
			$this->_imported_post_id( $url, $upload['url'] );
			$this->_imported_post_id( $post['guid'], $upload['url'] );
			// keep track of the destination if the remote url is redirected somewhere else
			if ( isset( $headers['x-final-location'] ) && $headers['x-final-location'] != $url ) {
				$this->_imported_post_id( $headers['x-final-location'], $upload['url'] );
			}

			return $upload;
		}

		public function _content_install_widgets() {
			// todo: pump these out into the 'content/' folder along with the XML so it's a little nicer to play with
			$import_widget_positions = $this->_get_json( 'widget_positions.json' );
			$import_widget_options   = $this->_get_json( 'widget_options.json' );

			// importing.
			$widget_positions = get_option( 'sidebars_widgets' );
			if ( ! is_array( $widget_positions ) ) {
				$widget_positions = array();
			}

			foreach ( $import_widget_options as $widget_name => $widget_options ) {
				// replace certain elements with updated imported entries.
				foreach ( $widget_options as $widget_option_id => $widget_option ) {

					// replace TERM ids in widget settings.
					foreach ( array( 'nav_menu' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_term_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
					// replace POST ids in widget settings.
					foreach ( array( 'image_id', 'post_id' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_post_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
				}
				$existing_options = get_option( 'widget_' . $widget_name, array() );
				if ( ! is_array( $existing_options ) ) {
					$existing_options = array();
				}
				$new_options = $existing_options + $widget_options;
				update_option( 'widget_' . $widget_name, $new_options );
			}
			update_option( 'sidebars_widgets', array_merge( $widget_positions, $import_widget_positions ) );

			return true;

		}

		public function _content_options_settings(){

			$this->logs[] = 'inside options panel';
			$custom_options = $this->_get_json( 'options.json' );

			foreach ( $custom_options as $option => $value ) {
				if($option == 'wplms' ){
					$ops = get_option($option);
					if(empty($ops) || !is_array($ops)){$ops = array();}
					foreach($value as $key => $val){
						$ops[$key] = $val;
					}

					update_option( $option, $ops );

					break;
				}
				
			}

			return true;
		}

		public function _content_install_customizer(){

			$this->logs[] = 'inside customizer settings';
			$custom_options = $this->_get_json( 'options.json' );
			foreach ( $custom_options as $option => $value ) {
				
				if($option == 'vibe_customizer'){
					$ops = get_option('vibe_customizer');
					if(empty($ops) || !is_array($ops)){$ops = array();}
					
					foreach($value as $key => $val){
						$ops[$key] = $val;
					}

					update_option( $option, $ops );
					break;
				}
			}

			return true;

		}

		public function _content_install_settings() {

			$this->_handle_delayed_posts( true ); // final wrap up of delayed posts.
			$this->vc_post(); // final wrap of vc posts.
			$this->logs[] = 'inside settings';
			$custom_options = $this->_get_json( 'options.json' );

			// we also want to update the widget area manager options.
			foreach ( $custom_options as $option => $value ) {
				// we have to update widget page numbers with imported page numbers.
				if (
					preg_match( '#(wam__position_)(\d+)_#', $option, $matches ) ||
					preg_match( '#(wam__area_)(\d+)_#', $option, $matches )
				) {
					$new_page_id = $this->_imported_post_id( $matches[2] );
					if ( $new_page_id ) {
						// we have a new page id for this one. import the new setting value.
						$option = str_replace( $matches[1] . $matches[2] . '_', $matches[1] . $new_page_id . '_', $option );
					}
				}

				if($option != 'wplms' && $option != 'vibe_customizer'){
					update_option( $option, $value );	
				}
			}

			
			$style = vibe_get_site_style();
			if(empty($style)){$style = $this->get_default_theme_style();}

			//Create a default menu
			$create=0;
			$main_menu = wp_get_nav_menu_object( 'MainMenu');
			if(!empty($main_menu)){
				$menu_id = $main_menu->term_id;
			}else{
				$create=1;
				$menu_id = wp_create_nav_menu('simplemenu_'.rand(0,999));
			}

            $i=0;
            if($create){
	            $pages = get_option('bp-pages');
	            foreach($pages as $key=>$p){
	            	$page = get_post($p);
	            	if($key != 'activity' && $key != 'activate'){
		                wp_update_nav_menu_item($menu_id, 0, array(
		                    'menu-item-title' =>  html_entity_decode( $page->post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
		                    'menu-item-url' => get_permalink($page->ID), 
		                    'menu-item-status' => 'publish')
		                );
		            }
	            }
	        }
            $locations = get_theme_mod('nav_menu_locations');
            $loggedin_menu = wp_get_nav_menu_object( 'VibeBP Loggedin Menu' );
            if(isset($loggedin_menu->term_id))
            	$locations['loggedin']=$loggedin_menu->term_id;
            $profile_menu = wp_get_nav_menu_object( 'VibeBP Profile Menu');
            if(isset($profile_menu->term_id))
            	$locations['profile']=$profile_menu->term_id;
            
            $locations['main-menu'] = $menu_id;
            $locations['mobile-menu'] = $menu_id;
            $locations['footer-menu'] = $menu_id;
            $locations['top-menu'] = $menu_id;
            set_theme_mod( 'nav_menu_locations', $locations );


            //print_r($vibebp_settings);$vibebp_settings[$tab]
            $vibebp_settings=get_option('vibebp_settings');
            if(empty($vibebp_settings) || !is_Array($vibebp_settings)){
            	$name = get_bloginfo('name');
            	$vibebp_settings = array(
				    'general' => array(
				            'client_id' => 'Ce93kLNBfvCIBhsC',
				            'sync_login' => 'on',
				            'token_duration' => 604800,
				            'global_login' => 'on',
				            'login_heading' => 'Welcome back',
				            'login_message' => 'Sign in to experience the next generation of '.$name,
				            'login_terms' => 'To make' .$name. 'work, we log user data and share it with service providers. Click “Sign in” above to accept VibeThemes’s Terms of Service & Privacy Policy.',
				            'signin_email_heading' => 'Sign in with email',
				            'signin_email_description' => 'To login enter the email address associated with your account, and the password.',
				            'forgot_password' => 'Enter the email address associated with your account, and we’ll send a magic link to your inbox.',
				            'register_account_heading' => 'Join VibeThemes',
				            'register_account_description' => 'Login to connect and check your account, personalize your dashboard, and follow people and chat with them.',
				            'tab' => 'general'
				        )
				);
				update_option('vibebp_settings',$vibebp_settings);
            }

			// set the blog page and the home page.
			$shoppage = get_page_by_title( 'Shop' );
			if ( $shoppage ) {
				update_option( 'woocommerce_shop_page_id', $shoppage->ID );
			}
			$shoppage = get_page_by_title( 'Cart' );
			if ( $shoppage ) {
				update_option( 'woocommerce_cart_page_id', $shoppage->ID );
			}
			$shoppage = get_page_by_title( 'Checkout' );
			if ( $shoppage ) {
				update_option( 'woocommerce_checkout_page_id', $shoppage->ID );
			}
			$shoppage = get_page_by_title( 'My Account' );
			if ( $shoppage ) {
				update_option( 'woocommerce_myaccount_page_id', $shoppage->ID );
			}
			
			$homepage = get_page_by_title( 'Home' );
			if ( $homepage ) { 
				update_option( 'page_on_front', $homepage->ID );
				update_option( 'show_on_front', 'page' );
				update_post_meta($homepage->ID,'_wp_page_template','notitle.php');
				update_post_meta($homepage->ID,'_add_content','no');
			}

			$blogpage = get_page_by_title( 'Blog' );
			if ( $blogpage ) {
				update_option( 'page_for_posts', $blogpage->ID );
				update_option( 'show_on_front', 'page' );
			}

			$post_ids = get_transient( 'importpostids' );
			
			if(!empty($post_ids)){
				
				$meta_keys = array('vibe_product','vibe_quiz_course','vibe_courses','vibe_course_curriculum','vibe_quiz_questions','vibe_forum','vibe_pre_course','vibe_assignment','vibe_assignment_course','_menu_item_object_id');

				foreach($post_ids as $i=>$d){
					if(is_numeric($i) && is_numeric($d)){
						$ids[$i] = $d;
					}
				}
				$ids = implode(',',$ids);
				foreach($meta_keys as $meta_key){
					global $wpdb;

					$results = $wpdb->get_results($wpdb->prepare("SELECT post_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN ($ids)",$meta_key));

					if(!empty($results)){
						foreach($results as $result){
							
							if(is_numeric($result->meta_value)){
								if(isset($post_ids[$result->meta_value])){
									update_post_meta($result->post_id,$meta_key,$post_ids[$result->meta_value]);
								}
							}else{
								if(is_string($result->meta_value)){
									$result->meta_value = unserialize($result->meta_value);	
								}
							 	if(is_array($result->meta_value)){
									$changed = 0;
									
									foreach($result->meta_value as $k=>$v){
										
										if(is_numeric($v) && isset($post_ids[$v])){
											$changed = 1;
											$result->meta_value[$k] = $post_ids[$v];
										}else{
											if(is_string($v)){
												$v = @unserialize($v);
											}
										} 
										
										if(is_array($v) && $k == 'ques'){ //Quiz questions use case
											foreach($v as $i => $q){
												if(is_numeric($q) && isset($post_ids[$q])){
													$changed = 1;
													$result->meta_value[$k][$i] = $post_ids[$q];
												}
											}
										}
									}
									if($changed){update_post_meta($result->post_id,$meta_key,$result->meta_value);}
								}
							}
						}
					}
				}
			}

			update_option('default_role','student');			
			
			

			update_option('vibebp_setup_complete',1);

			if(function_exists('vibe_get_customizer')){
				$customizer = get_option('vibe_customizer');
				if(!$customizer['profile_layout'] != 'blank'){
					$customizer['profile_layout'] = 'blank';
					update_option('vibe_customizer',$customizer);	
				}
			}

			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
			update_option( 'rewrite_rules', false );
			$wp_rewrite->flush_rules( true );

			return true;
		}

		function _content_install_slider(){

			$style = vibe_get_site_style();

			if(empty($style)){$style = $this->get_default_theme_style();}

			$slider_array = array();
			$ls_slider_array = array();


			$url = 'https://s3.amazonaws.com/wplmsdownloads/demodata/'.$style;
			if(!empty($_GET['force'])){
				$url = 'https://demos.wplms.io/demos/demodata/content/'.$style;
			}
			

			if(in_array($style,array('demo1'))){
				$slider_array = array($url."/classicslider1.zip");
			}
			if(in_array($style,array('demo2'))){
				$slider_array = array($url."/search-form-hero2.zip",$url."/news-hero4.zip",$url."/about1.zip");
			}
			if(in_array($style,array('demo3'))){
				$slider_array = array($url."/highlight-showcase4.zip");
			}

			if(in_array($style,array('demo4'))){
				$slider_array = array($url."/homeslider.zip",$url."/categories.zip");
			}

			if(in_array($style,array('demo5'))){
				$slider_array = array($url."/demo5.zip");
			}

			if(in_array($style,array('demo6'))){
				$slider_array = array($url."/homeslider.zip");
			}

			if(in_array($style,array('demo7'))){
				$slider_array = array($url."/demo7.zip");
			}

			if(in_array($style,array('demo8'))){
				$slider_array = array($url."/demo8.zip");
			}
 
			if(in_array($style,array('demo9'))){
				$slider_array = array($url."/demo9.zip",$url."/demo9_parallax.zip");
			}
			
			if(in_array($style,array('default'))){
				$ls_slider_array = array($url."/lsslider.zip");
			}

	        
	        if(!empty($ls_slider_array)){
	        	include LS_ROOT_PATH.'/classes/class.ls.importutil.php';
	        	if(class_exists('LS_ImportUtil')){
	        		foreach($ls_slider_array as $url) {
		        		$filepath = $this->_download_slider($url);
						$import = new LS_ImportUtil($filepath);
					}
	        	}
	        }


			if(class_exists('RevSlider') && !empty($slider_array)){
				$slider = new RevSlider();
				foreach($slider_array as $url){
					$filepath = $this->_download_slider($url);
					$slider->importSliderFromPost(true,true,$filepath);  
				}	

			}


			return true;
		}

		function _download_slider($url){

			$file_name = basename( $url );
			$upload_dir = wp_upload_dir();
			$full_path = $upload_dir['path'].'/'.$file_name;
			if(file_exists($full_path)){
				@unlink($full_path);
			}

			$upload = wp_upload_bits( $file_name, 0, '');

			if ( $upload['error'] ) { // File already imported
				@unlink( $upload['file'] );

				$upload = wp_upload_bits( $file_name, 0, '');

				if ( $upload['error'] ) {
					return $upload['file'];
				}
				//new WP_Error( 'upload_dir_error', $upload['error'] );
			}

			// we check if this file is uploaded locally in the source folder.
			$response = wp_remote_get( $url ,array('timeout' => 200));


			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->put_contents( $upload['file'], $response['body'] );
				
			if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$headers = $response['headers'];
				WP_Filesystem();
				global $wp_filesystem;
				$wp_filesystem->put_contents( $upload['file'], $response['body'] );
			} else {
				// required to download file failed.
				@unlink( $upload['file'] );

				return new WP_Error( 'import_file_error', esc_html__( 'Remote server did not respond' ,'vibe') );
			}

			return $upload['file'];
		}

		public function _get_json( $file ) {

			//Change to Uploaded file path if uploaded
			$path = get_option('wplms_export_import_content_path');

			if( !empty($path) ){
				$style = basename($path);
				$theme_style = $path.'/';
			}else{
				$style = vibe_get_site_style();
				$style = vibe_get_site_style();
				if(empty($style)){$style = $this->get_default_theme_style();}


				$theme_style = 'https://wplms.s3.ap-south-1.amazonaws.com/demodata/' . basename($style) .'/';

				if(!empty($_GET['force'])){
					$theme_style = 'https://demos.wplms.io/demos/demodata/' . basename($style) .'/';
				}
				//$theme_style = __DIR__ . '/content/' . basename($style) .'/';
			}

			if(!empty($_GET['capture']) && current_user_can('manage_options')){
				$theme_style = $_GET['capture'];
			}

            if($file == 'options.json'){
                
                $file_name = $theme_style . basename( $file );  
                
                $loaded = get_transient($style.'_'.$file);
                if(empty($loaded)) {
                	$request = wp_remote_get($file_name,array('timeout' => 120));	
                	if( !is_wp_error( $request ) ) {
						$loaded = json_decode(wp_remote_retrieve_body($request), true );
						set_transient($style.'_'.$file,$loaded,HOUR_IN_SECONDS);
						return $loaded;
					}
                }
                
            }

        	$file_name = $theme_style . basename( $file );   

        	$loaded = get_transient($style.'_'.$file);
            if(empty($loaded)) {
            	$request = wp_remote_get($file_name);	
            	if( !is_wp_error( $request ) ) {
            		$request = wp_remote_get(esc_url_raw($file_name),array('timeout' => 120));
           
		            if( !is_wp_error( $request ) ) {
						$loaded = json_decode(wp_remote_retrieve_body($request), true );
						set_transient($style.'_'.$file,$loaded,HOUR_IN_SECONDS);
						return $loaded;
					}
				}
            }else{
            	return $loaded;
            }
            
            
            return array();
        }
        

		public function _content_setup_users(){

			$current_style = vibe_get_site_style();
			if($current_style  == 'demo_4_academy'){

				$file_name = 'https://wplms.s3.ap-south-1.amazonaws.com/demodata/' . basename($current_style) .'/users.csv';

				$request = wp_remote_get(esc_url_raw($file_name),array('timeout' => 120));


				$file_data = wp_remote_retrieve_body($request);

				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				WP_Filesystem();
				global $wp_filesystem;
				$upload    = wp_upload_bits( basename($file_name), null, $file_data);
				if(!empty($upload['file'])){
					$import = new wplms_import();
					$import->process_csv($upload['file']);
				}
			}
			return true;
		}

		public $logs = array();

		public function log( $message ) {
			$this->logs[] = $message;
		}

		public $errors = array();

		public function error( $message ) {
			$this->logs[] = 'ERROR!!!! ' . $message;
		}

		public function envato_setup_demo_style() {

			$installation_type = '';
			if(!empty($_GET['installation_type'])){
				$installation_type=$_GET['installation_type'];
			}
			?>
            <h1><?php esc_html_e( 'Theme Style','vibe' ); ?></h1>
            <form method="post">
                <p>'Please click on theme style to select the style for your site from below options. You can switch or mix and match demos post setup as well using the demo switcher.</p>

                <div class="theme-presets">
                    <ul>
	                    <?php

	                    $current_style = vibe_get_site_style();
	                    
						if(empty($current_style)){$current_style = $this->get_default_theme_style();}
	                    foreach ( $this->site_styles as $style_name => $style_data ) {
	                    	if(empty($installation_type) || in_array($installation_type,$style_data['installation_type'])){
		                    ?>
                            <li<?php echo vibe_sanitizer($style_name == $current_style ? ' class="current" ' : ''); ?>>
                                <a href="#" class="sitestyle" data-style="<?php echo esc_attr( $style_name ); ?>"><img
                                            src="<?php echo esc_url($style_data['src']);?>"></a><a href="<?php echo vibe_sanitizer($style_data['link'],'url'); ?>" target="_blank" class="link"></a>
                            </li>
	                    <?php 
	                    	}
	                	} ?>
                    </ul>
                </div>

                <input type="hidden" name="demo_style" id="demo_style" value="<?php echo vibe_sanitizer($current_style,'text'); ?>">

                <hr>
                <div class="custom_upload_block">
                <h3 class="hide_next">* OR Import your exported code from another WPLMS site. (<a href="http://vibethemes.com/documentation/wplms/knowledge-base/wplms-site-import-and-export/" title="wplms site exporter" target="_blank">?</a>)</h3>
                <div class="hide">
	                <?php wp_enqueue_script('plupload'); ?>

	                <div  class="plupload_error_notices notice notice-error is-dismissible"></div>
	                <div id="plupload-upload-ui" class="hide-if-no-js">
	                    <div id="drag-drop-area">
	                        <div class="drag-drop-inside">
	                            <p class="drag-drop-info"><?php _e('Drop files here','vibe'); ?></p>
	                            <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files','vibe'); ?></p>
	                            <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php _e('Select Files','vibe'); ?>" class="button" /></p>
	                        </div>
	                    </div>
	                </div>

	                <div class="pl_wplms_progress">
	                	<div class="warning_plupload" style="display:none;padding:15px;padding-bottom:1px;margin:10px 0;background:#d8d8d8;">
		                    <h3><?php echo __("Please do not close the window until process is completed","vibe") ?></h3>
		                </div>
	                </div>
                </div>

                <?php
                    if ( function_exists( 'ini_get' ) )
                        $post_size = ini_get('post_max_size') ;
                    $post_size = preg_replace('/[^0-9\.]/', '', $post_size);
                    $post_size = intval($post_size);
                    if($post_size != 1){
                        $post_size = $post_size-1;
                    }

                 $plupload_init = array(
                    'runtimes'            => 'html5,silverlight,flash,html4',
                    'chunk_size'          =>  (($post_size*1024) - 100).'kb',
                    'max_retries'         => 3,
                    'browse_button'       => 'plupload-browse-button',
                    'container'           => 'plupload-upload-ui',
                    'drop_element'        => 'drag-drop-area',
                    'multiple_queues'     => false,
                    'multi_selection'     => false,
                    'filters'             => array( array( 'extensions' => implode( ',', array('zip') ) ) ),
                    'url'                 => admin_url('admin-ajax.php'),
                    'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
                    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                    'multipart'           => true,
                    'urlstream_upload'    => true,

                    // additional post data to send to our ajax hook
                    'multipart_params'    => array(
                      '_ajax_nonce' => wp_create_nonce('wplms_exported_content_plupload'),
                      'action'      => 'wplms_exported_content_plupload'
                    ),
                  );

                $plupload_init = apply_filters('plupload_init', $plupload_init);
                
                ?>
				<script>
					jQuery(document).ready(function($){
						var temp = <?php echo json_encode($plupload_init,JSON_UNESCAPED_SLASHES); ?>;
						// create the uploader and pass the config from above
						var uploader = new plupload.Uploader(temp);
						// checks if browser supports drag and drop upload, makes some css adjustments if necessary
						uploader.bind('Init', function(up){
							var uploaddiv = $('#plupload-upload-ui');
							uploaddiv.css({'display':'block','margin-bottom':'10px'});
							if(up.features.dragdrop){
                				uploaddiv.addClass('drag-drop');
                				$('#drag-drop-area')
                					.bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                					.bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); })
                					.css('height', 'auto');
                			}else{
                				uploaddiv.removeClass('drag-drop');
                				$('#drag-drop-area').unbind('.wp-uploader');
                			}
                		});

                		uploader.init();

                		// a file was added in the queue
                        uploader.bind('FilesAdded', function(up, files){
                            
                            var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
                            plupload.each(files, function(file){
                                if (file.size > max && up.runtime != 'html5'){
                                    console.log('call "upload_to_amazon" not sent');
                                }else{
                                     $('.pl_wplms_progress').addClass('visible');
                                    var clone = $('.pl_wplms_progress').append('<div class="'+file.id+'">'+file.name+'<i></i><strong><span></span></strong></div>');
                                    $('.pl_wplms_progress').append(clone);
                                    $('.warning_plupload').show(300);
                                }
                               
                            });

                            up.refresh();
                            up.start();
                        });

                		uploader.bind('Error', function(up, args){
                			console.log(up);
                			$('.plupload_error_notices').show();
                			$('.plupload_error_notices').html('<div class="message text-danger danger tada animate load">'+args.message+' for '+args.file.name+'</div>');
                			setTimeout(function(){
                				$('.plupload_error_notices').hide();
                			}, 5000);
                			up.refresh();
                			up.start();
                		});
                		uploader.bind('UploadProgress', function(up, file){
                			if(file.percent < 100 && file.percent >= 1){
                				$('.pl_wplms_progress div.'+file.id+' strong span').css('width', (file.percent)+'%');
                				$('.pl_wplms_progress div.'+file.id+' i').html( (file.percent)+'%');
                			}
                			up.refresh();
                			up.start();
                		});
                		// a file was uploaded
                		uploader.bind('FileUploaded', function(up, file, response) {

                            //$('.stop_s3_plupload_upload').addClass('disabled');
                             $.ajax({
                              type: "POST",
                              url: 'admin-ajax.php',
                              data: { action: 'insert_export_content_final', 
                                      security: '<?php echo wp_create_nonce("wplms_export_content_final"); ?>',
                                      name:file.name,
                                      type:file.type,
                                      size:file.origSize,
                                    },
                              cache: false,
                              success: function (html) {
                                if(html){
                                    if(html == '0'){
                                        $('.pl_wplms_progress div.'+file.id+' strong span').css('width', '0%');
                                        $('.pl_wplms_progress div.'+file.id+' strong').html("<i class='error'><?php echo __("File couldn't be unzipped properly","vibe"); ?><i>");
                                        setTimeout(function(){
                                            $('.pl_wplms_progress div.'+file.id).fadeOut(600);
                                            $('.pl_wplms_progress div.'+file.id).remove();
                                        }, 2500);
                                        $('.warning_plupload').hide(300);
                                        return false;
                                    }else{

                                        $('.pl_wplms_progress div.'+file.id+' strong span').css('width', '100%');
                                        $('.pl_wplms_progress div.'+file.id+' i').html('100%');

                                            setTimeout(function(){
                                              $('.pl_wplms_progress div.'+file.id+' strong').fadeOut(500);
                                            }, 1200);

                                            $('.pl_wplms_progress div.'+file.id).html(html);
                                            $('.pl_wplms_progress div.success.message').css({'border-left':'4px solid #a0da13','background-color':'#f4fbea','margin-bottom':'10px'});
                                            setTimeout(function(){
                                                if($('.pl_wplms_progress strong').length < 1){
                                                    $('.warning_plupload').hide(300);
                                                }
											}, 1750);
                                    }
                                }

                              }
                            });
                        });

                	});
                </script>
                <?php
                ?>

                <div class="envato-setup-actions step">
                	
                    <input type="submit" class="large_next_button button-next"
                           value="<?php _e( 'Continue','vibe' ); ?>" name="save_step"/>
                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
                       class="large_skip_button"><?php esc_html_e( 'Skip this step','vibe' ); ?></a>
                   

					<?php wp_nonce_field( 'envato-setup' ); ?>
                </div>
                <p><em>Please Note: Advanced changes to website graphics/colors may require extensive PhotoShop and Web
                        Development knowledge. We recommend hiring an expert from <a
                                href="http://studio.envato.com/"
                                target="_blank">Envato Studio</a> to assist with any advanced website changes.</em></p>
            </form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function envato_setup_demo_style_save($demo=null) {
			if(empty($demo)){
				check_admin_referer( 'envato-setup' );
			}

			$demo_style = isset( $_POST['demo_style'] ) ? $_POST['demo_style'] : false;
			if(!empty($demo)){
				$demo_style=$demo;
			}
			if ( $demo_style ) {
				update_option( 'wplms_site_style', $demo_style );
			}
			if(empty($demo)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}
			
		}

		/**
		 * Logo & Design
		 */
		public function envato_setup_design() {
			/*Delete option for uploaded content to avoid conflicts when setup wizard runs again*/
			delete_option( 'wplms_export_import_content_path' );

			?>
			<h1><?php esc_html_e( 'Design and Layouts','vibe' ); ?></h1>
			<form method="post">
				<p><?php printf( esc_html__( 'Please add your logo below. For best results, the logo should be a transparent PNG ( 466 by 277 pixels). The logo can be changed at any time from the Appearance > Customize area in your dashboard. Try %sEnvato Studio%s if you need a new logo designed.','vibe' ), '<a href="http://studiotracking.envato.com/aff_c?offer_id=4&aff_id=1564&source=DemoInstall" target="_blank">', '</a>' ); ?></p>

				<table>
					<tr>
						<td>
							LOGO
						</td>
						<td>
							<div id="wplms-logo">
								<?php
								$image_url = vibe_get_option('logo');
								if(empty($image_url)){
									$image_url = VIBE_URL.'/assets/images/logo.png';
								}
								
								if ( $image_url ) {
									$image = '<img class="site-logo" style="max-width:466px;" id="current-logo" src="%s" alt="%s" />';
									printf(
										$image,
										$image_url,
										get_bloginfo( 'name' )
									);
								} ?>
							</div>
							<input type="hidden" name="logo_url" id="logo_url" value="<?php echo vibe_sanitizer($image_url,'url'); ?>">
						</td>
						<td>
							<a href="#" class="button button-upload" data-title="Upload a logo" data-text="select a logo" data-target="#current-logo" data-save="#logo_url"><?php esc_html_e( 'Upload New Logo' ,'vibe'); ?></a>
						</td>
					</tr>
					<tr>
						<td>
							Theme Skin
						</td>
						<?php
							$theme_skin = vibe_get_customizer('theme_skin');
						?>
						<td>
							<select name="theme_skin">
								<option value="" <?php echo (empty($theme_skin)?'selected':''); ?>>Default</option>
								<option value="minimal"  <?php echo (($theme_skin == 'minimal')?'selected':''); ?>>Minimal</option>
								<option value="elegant" <?php echo (($theme_skin == 'elegant')?'selected':''); ?>>Elegant</option>
								<option value="modern" <?php echo (($theme_skin == 'modern')?'selected':''); ?>>Modern</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Primary color
							<?php 
							$primary_bg=vibe_get_customizer('primary_bg');
							if(Empty($primary_bg)){$primary_bg= '#009dd8';}
							?>
						</td>
						<td>
							<input id="primary_bg" class="jscolor {hash:true}" name="primary_bg" type="text" value="<?php echo vibe_sanitizer($primary_bg); ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Primary text color
							<?php 
							$primary_color=vibe_get_customizer('primary_color');
							if(Empty($primary_color)){$primary_color= '#ffffff';}
							?>
						</td>
						<td>
							<input id="primary_color" class="jscolor {hash:true}" name="primary_color" type="text" value="#ffffff" />
						</td>
					</tr>
				</table>
				<br>
				<hr>
				<p><em>Please Note: WPLMS has live support at Facebook.com/VibeThemes. Also a free installation service at <a
							href="https://wplms.io/support"
							target="_blank">WPLMS Support</a>.</em></p>
				

				<div class="envato-setup-actions step">
					<input type="submit" class="large_next_button button-next"
					       value="<?php _e( 'Continue','vibe' ); ?>" name="save_step"/>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class=" button-next"><?php esc_html_e( 'Skip this step','vibe' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function envato_setup_design_save($theme=null) {
			if(empty($theme)){
				check_admin_referer( 'envato-setup' );
			}

			$logo_url = $_POST['logo_url'];
			vibe_update_option('logo',$logo_url);

			$theme_skin =  isset( $_POST['theme_skin'] ) ? $_POST['theme_skin'] : false;
			if(!empty($theme)){
				$theme_skin = $theme;
			}
			if ( $theme_skin ) {
				vibe_update_customizer('theme_skin',$theme_skin);
				if(function_exists('wplms_get_theme_color_config')){
					$new_option = wplms_get_theme_color_config($theme_skin);
					$option = get_option('vibe_customizer');
					if(!empty($new_option)){
				        foreach($new_option as $k=>$v){
				            $option[$k] = $v;
				        }
				    }
				    update_option('vibe_customizer',$option);
				}
			}
			$primary_bg = isset( $_POST['primary_bg'] ) ? $_POST['primary_bg'] : false;
			if ( $primary_bg ) {
				vibe_update_customizer('primary_bg',$primary_bg);
			}

			$primary_color = isset( $_POST['primary_color'] ) ? $_POST['primary_color'] : false;
			if ( $primary_color ) {
				vibe_update_customizer('primary_color',$primary_color);
			}

			do_action('wplms_envato_setup_design_save',$theme);

			if(empty($theme)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}
		}
		
		/**
		 * Final step
		 */
		public function envato_setup_ready() {

			update_option( 'envato_setup_complete', time() );
			?>
			<a href="https://twitter.com/share" class="twitter-share-button"
			   data-url="http://themeforest.net/user/vibethemes/portfolio?ref=vibethemes"
			   data-text="<?php echo esc_attr( 'I just installed the ' . wp_get_theme() . ' #WordPress theme from #ThemeForest' ); ?>"
			   data-via="EnvatoMarket" data-size="large">Tweet</a>
			<script>!function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (!d.getElementById(id)) {
						js = d.createElement(s);
						js.id = id;
						js.src = "//platform.twitter.com/widgets.js";
						fjs.parentNode.insertBefore(js, fjs);
					}
				}(document, "script", "twitter-wjs");</script>

			<h1><?php esc_html_e( 'Your Website is Ready!','vibe' ); ?></h1>

			<p>Congratulations! The theme has been activated and your website is ready. Login to your WordPress
				dashboard to make changes and modify any of the default content to suit your needs.</p>
			<p>Please come back and <a href="http://themeforest.net/downloads" target="_blank">leave a 5-star rating</a>
				if you are happy with this theme. <br/>Follow <a href="https://twitter.com/vibethemes" target="_blank">@vibethemes</a>
				on Twitter to see updates. Thanks! </p>
			<?php flush_rewrite_rules(); ?>
			<div class="envato-setup-next-steps">
				<div class="envato-setup-next-steps-first">
					<h2><?php esc_html_e( 'Next Steps','vibe' ); ?></h2>
					<ul>
						<li class="setup-product"><a class="button button-primary button-large" style="color:#fff;" href="https://www.youtube.com/watch?v=Nz2lQQLZ-OQ">Watch Post Setup Configuration <br>[ Recommended Video ]
	                         </a>
						</li>
						<li class="setup-product"><a class="button button-next button-large"
						                             href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'View your new website!','vibe' ); ?></a>
						</li>
					</ul>
				</div>
				<div class="envato-setup-next-steps-last">
					<h2><?php esc_html_e( 'More Resources','vibe' ); ?></h2>
					<ul>
						<li class="documentation"><a href="https://wplms.io/support/"
						                             target="_blank"><?php esc_html_e( 'Read the Theme Documentation','vibe' ); ?></a>
						</li>

						<li class="howto"><a href="https://wordpress.org/support/"
						                     target="_blank"><?php esc_html_e( 'Learn how to use WordPress','vibe' ); ?></a>
						</li>
						<li class="rating"><a href="http://themeforest.net/downloads"
						                      target="_blank"><?php esc_html_e( 'Leave an Item Rating','vibe' ); ?></a></li>
						<li class="support"><a href="https://wplms.io/support/"
						                       target="_blank"><?php esc_html_e( 'Get Help and Support','vibe' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}

		public function envato_market_admin_init() {

			if ( ! function_exists( 'envato_market' ) ) {
				return;
			}

			global $wp_settings_sections;
			if ( ! isset( $wp_settings_sections[ envato_market()->get_slug() ] ) ) {
				// means we're running the admin_init hook before envato market gets to setup settings area.
				// good - this means our oauth prompt will appear first in the list of settings blocks
				register_setting( envato_market()->get_slug(), envato_market()->get_option_name() );
			}

			// pull our custom options across to envato.
			$option         = get_option( 'envato_setup_wizard', array() );
			$envato_options = envato_market()->get_options();
			$envato_options = $this->_array_merge_recursive_distinct( $envato_options, $option );
			update_option( envato_market()->get_option_name(), $envato_options );

			//add_thickbox();

			if ( ! empty( $_POST['oauth_session'] ) && ! empty( $_POST['bounce_nonce'] ) && wp_verify_nonce( $_POST['bounce_nonce'], 'envato_oauth_bounce_' . $this->envato_username ) ) {
				// request the token from our bounce url.
				$my_theme    = wp_get_theme();
				$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
				if ( ! $oauth_nonce ) {
					// this is our 'private key' that is used to request a token from our api bounce server.
					// only hosts with this key are allowed to request a token and a refresh token
					// the first time this key is used, it is set and locked on the server.
					$oauth_nonce = wp_create_nonce( 'envato_oauth_nonce_' . $this->envato_username );
					update_option( 'envato_oauth_' . $this->envato_username, $oauth_nonce );
				}
				$response = wp_remote_post( $this->oauth_script, array(
						'method'      => 'POST',
						'timeout'     => 15,
						'redirection' => 1,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => array(),
						'body'        => array(
							'oauth_session' => $_POST['oauth_session'],
							'oauth_nonce'   => $oauth_nonce,
							'get_token'     => 'yes',
							'url'           => home_url(),
							'theme'         => $my_theme->get( 'Name' ),
							'version'       => $my_theme->get( 'Version' ),
						),
						'cookies'     => array(),
					)
				);
				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					$class         = 'error';
					echo "<div class=\"$class\"><p>" . sprintf( esc_html__( 'Something went wrong while trying to retrieve oauth token: %s' ,'vibe'), $error_message ) . '</p></div>';
				} else {
					$token  = @json_decode( wp_remote_retrieve_body( $response ), true );
					$result = false;
					if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
						$token['oauth_session'] = $_POST['oauth_session'];
						$result                 = $this->_manage_oauth_token( $token );
					}
					if ( $result !== true ) {
						echo 'Failed to get oAuth token. Please go back and try again';
						exit;
					}
				}
			}

			add_settings_section(
				envato_market()->get_option_name() . '_' . $this->envato_username . '_oauth_login',
				sprintf( esc_html__( 'Login for %s updates','vibe' ), $this->envato_username ),
				array( $this, 'render_oauth_login_description_callback' ),
				envato_market()->get_slug()
			);
			// Items setting.
			add_settings_field(
				$this->envato_username . 'oauth_keys',
				esc_html__( 'oAuth Login','vibe' ),
				array( $this, 'render_oauth_login_fields_callback' ),
				envato_market()->get_slug(),
				envato_market()->get_option_name() . '_' . $this->envato_username . '_oauth_login'
			);
		}

		private static $_current_manage_token = false;

		private function _manage_oauth_token( $token ) {
			if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
				if ( self::$_current_manage_token == $token['access_token'] ) {
					return false; // stop loops when refresh auth fails.
				}
				self::$_current_manage_token = $token['access_token'];
				// yes! we have an access token. store this in our options so we can get a list of items using it.
				$option = get_option( 'envato_setup_wizard', array() );
				if ( ! is_array( $option ) ) {
					$option = array();
				}
				if ( empty( $option['items'] ) ) {
					$option['items'] = array();
				}
				// check if token is expired.
				if ( empty( $token['expires'] ) ) {
					$token['expires'] = time() + 3600;
				}
				if ( $token['expires'] < time() + 120 && ! empty( $token['oauth_session'] ) ) {
					// time to renew this token!
					$my_theme    = wp_get_theme();
					$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
					$response    = wp_remote_post( $this->oauth_script, array(
							'method'      => 'POST',
							'timeout'     => 10,
							'redirection' => 1,
							'httpversion' => '1.0',
							'blocking'    => true,
							'headers'     => array(),
							'body'        => array(
								'oauth_session' => $token['oauth_session'],
								'oauth_nonce'   => $oauth_nonce,
								'refresh_token' => 'yes',
								'url'           => home_url(),
								'theme'         => $my_theme->get( 'Name' ),
								'version'       => $my_theme->get( 'Version' ),
							),
							'cookies'     => array(),
						)
					);
					if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						echo "Something went wrong while trying to retrieve oauth token: $error_message";
					} else {
						$new_token = @json_decode( wp_remote_retrieve_body( $response ), true );
						$result    = false;
						if ( is_array( $new_token ) && ! empty( $new_token['new_token'] ) ) {
							$token['access_token'] = $new_token['new_token'];
							$token['expires']      = time() + 3600;
						}
					}
				}
				// use this token to get a list of purchased items
				// add this to our items array.
				$response                    = envato_market()->api()->request( 'https://api.envato.com/v3/market/buyer/purchases', array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $token['access_token'],
					),
				) );
				self::$_current_manage_token = false;
				if ( is_array( $response ) && is_array( $response['purchases'] ) ) {
					// up to here, add to items array
					foreach ( $response['purchases'] as $purchase ) {
						// check if this item already exists in the items array.
						$exists = false;
						foreach ( $option['items'] as $id => $item ) {
							if ( ! empty( $item['id'] ) && $item['id'] == $purchase['item']['id'] ) {
								$exists = true;
								// update token.
								$option['items'][ $id ]['token']      = $token['access_token'];
								$option['items'][ $id ]['token_data'] = $token;
								$option['items'][ $id ]['oauth']      = $this->envato_username;
								if ( ! empty( $purchase['code'] ) ) {
									$option['items'][ $id ]['purchase_code'] = $purchase['code'];
								}
							}
						}
						if ( ! $exists ) {
							$option['items'][] = array(
								'id'            => '' . $purchase['item']['id'],
								// item id needs to be a string for market download to work correctly.
								'name'          => $purchase['item']['name'],
								'token'         => $token['access_token'],
								'token_data'    => $token,
								'oauth'         => $this->envato_username,
								'type'          => ! empty( $purchase['item']['wordpress_theme_metadata'] ) ? 'theme' : 'plugin',
								'purchase_code' => ! empty( $purchase['code'] ) ? $purchase['code'] : '',
							);
						}
					}
				} else {
					return false;
				}
				if ( ! isset( $option['oauth'] ) ) {
					$option['oauth'] = array();
				}
				// store our 1 hour long token here. we can refresh this token when it comes time to use it again (i.e. during an update)
				$option['oauth'][ $this->envato_username ] = $token;
				update_option( 'envato_setup_wizard', $option );

				$envato_options = envato_market()->get_options();
				$envato_options = $this->_array_merge_recursive_distinct( $envato_options, $option );
				update_option( envato_market()->get_option_name(), $envato_options );
				envato_market()->items()->set_themes( true );
				envato_market()->items()->set_plugins( true );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * @param $array1
		 * @param $array2
		 *
		 * @return mixed
		 *
		 *
		 * @since    1.1.4
		 */
		private function _array_merge_recursive_distinct( $array1, $array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
					$merged [ $key ] = $this->_array_merge_recursive_distinct( $merged [ $key ], $value );
				} else {
					$merged [ $key ] = $value;
				}
			}

			return $merged;
		}

		/**
		 * @param $args
		 * @param $url
		 *
		 * @return mixed
		 *
		 * Filter the WordPress HTTP call args.
		 * We do this to find any queries that are using an expired token from an oAuth bounce login.
		 * Since these oAuth tokens only last 1 hour we have to hit up our server again for a refresh of that token before using it on the Envato API.
		 * Hacky, but only way to do it.
		 */
		public function envato_market_http_request_args( $args, $url ) {
			if ( strpos( $url, 'api.envato.com' ) && function_exists( 'envato_market' ) ) {
				// we have an API request.
				// check if it's using an expired token.
				if ( ! empty( $args['headers']['Authorization'] ) ) {
					$token = str_replace( 'Bearer ', '', $args['headers']['Authorization'] );
					if ( $token ) {
						// check our options for a list of active oauth tokens and see if one matches, for this envato username.
						$option = envato_market()->get_options();
						if ( $option && ! empty( $option['oauth'][ $this->envato_username ] ) && $option['oauth'][ $this->envato_username ]['access_token'] == $token && $option['oauth'][ $this->envato_username ]['expires'] < time() + 120 ) {
							// we've found an expired token for this oauth user!
							// time to hit up our bounce server for a refresh of this token and update associated data.
							$this->_manage_oauth_token( $option['oauth'][ $this->envato_username ] );
							$updated_option = envato_market()->get_options();
							if ( $updated_option && ! empty( $updated_option['oauth'][ $this->envato_username ]['access_token'] ) ) {
								// hopefully this means we have an updated access token to deal with.
								$args['headers']['Authorization'] = 'Bearer ' . $updated_option['oauth'][ $this->envato_username ]['access_token'];
							}
						}
					}
				}
			}

			return $args;
		}

		public function render_oauth_login_description_callback() {
			echo 'If you have purchased items from ' . esc_html( $this->envato_username ) . ' on ThemeForest or CodeCanyon please login here for quick and easy updates.';

		}

		public function render_oauth_login_fields_callback() {
			$option = envato_market()->get_options();
			?>
			<div class="oauth-login" data-username="<?php echo esc_attr( $this->envato_username ); ?>">
				<a href="<?php echo esc_url( $this->get_oauth_login_url( admin_url( 'admin.php?page=' . envato_market()->get_slug() . '#settings' ) ) ); ?>"
				   class="oauth-login-button button button-primary">Login with Envato to activate updates</a>
			</div>
			<?php
		}

		/// a better filter would be on the post-option get filter for the items array.
		// we can update the token there.

		public function get_oauth_login_url( $return ) {
			return $this->oauth_script . '?bounce_nonce=' . wp_create_nonce( 'envato_oauth_bounce_' . $this->envato_username ) . '&wp_return=' . urlencode( $return );
		}

		/**
		 * Helper function
		 * Take a path and return it clean
		 *
		 * @param string $path
		 *
		 * @since    1.1.2
		 */
		public static function cleanFilePath( $path ) {
			$path = str_replace( '', '', str_replace( array( '\\', '\\\\', '//' ), '/', $path ) );
			if ( $path[ strlen( $path ) - 1 ] === '/' ) {
				$path = rtrim( $path, '/' );
			}

			return $path;
		}

		public function is_submenu_page() {
			return ( $this->parent_slug == '' ) ? false : true;
		}

		function wplms_exported_content_plupload(){

			check_ajax_referer('wplms_exported_content_plupload');
			if( !is_user_logged_in() )
				die('user not logged in');

			if( empty($_FILES) || $_FILES['file']['error'] )
				die('{"OK": 0, "info": "Failed to move uploaded file."}');

			$chunk 	  = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
			$chunks   = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
			$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];

			$upload_dir_base = wp_upload_dir();
			$folderPath = $upload_dir_base['basedir'].'/upload_demos/';
			if ( function_exists('is_dir') && !is_dir( $folderPath ) ) {
				if( function_exists('wp_mkdir_p') ){
					wp_mkdir_p($folderPath);
				}
			}
			$filePath = $folderPath."/$fileName";

			if($chunk == 0)
				$perm = "wb";
			else
				$perm = "ab";

			$out = @fopen("{$filePath}.part",$perm );
			if($out){
				// Read binary input stream and append it to temp file
				$in = @fopen($_FILES['file']['tmp_name'], "rb");
				if($in){
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				}else{
					die('{"OK": 0, "info": "Failed to open input stream."}');
				}
				@fclose($in);
				@fclose($out);
				@unlink($_FILES['file']['tmp_name']);
			}else{
				die('{"OK": 0, "info": "Failed to open output stream."}');
			}

			// Check if file has been uploaded
			if( !$chunks || $chunk == $chunks - 1 ){
				// Strip the temp .part suffix off
				rename("{$filePath}.part", $filePath);
			}

			die('{"OK": 1, "info": "Upload successful."}');
			exit;
		}

		function insert_export_content_final(){
			if( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_export_content_final') || !is_user_logged_in() ){
				wp_die( __('Security check failed contact administrator','vibe') );
				die();
			}

			$filename 		 = $_POST['name'];
			$upload_dir_base = wp_upload_dir();
			$folderPath 	 = $upload_dir_base['basedir'].'/upload_demos';
			$filePath = $folderPath.'/'.$filename;

			$zip = new ZipArchive;
			$response = $zip->open( $filePath );
			if( $response ){
				$zip->extractTo($folderPath);
				$zip->close();

				//Update option for importing content from uploads folder
				$temp_folder_path = $folderPath.'/'.basename($filePath,'.zip');
				update_option( 'wplms_export_import_content_path', $temp_folder_path );

				//Delete file after uploading
				unlink($filePath);
				echo '<div class="success message">'.__('File uploaded and unzipped successfully','vibe').'<div>';
			}else{
				echo '0';
			}

			die();
		}
	}

}// if !class_exists

/**
 * Loads the main instance of Envato_Theme_Setup_Wizard to have
 * ability extend class functionality
 *
 * @since 1.1.1
 * @return object Envato_Theme_Setup_Wizard
 */
add_action( 'after_setup_theme', 'envato_theme_setup_wizard', 10 );
if ( ! function_exists( 'envato_theme_setup_wizard' ) ) :
	function envato_theme_setup_wizard() {
		Envato_Theme_Setup_Wizard::get_instance();
	}
endif;

add_filter('wplms_theme_setup_wizard_username', 'wplms_set_theme_setup_wizard_username', 10);
if( ! function_exists('wplms_set_theme_setup_wizard_username') ){
    function wplms_set_theme_setup_wizard_username($username){
        return 'vibethemes';
    }
}
