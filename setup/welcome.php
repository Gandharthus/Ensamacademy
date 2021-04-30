<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPLMS_Admin_Welcome {

	private $plugin;
	public $major_version = WPLMS_VERSION;
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Bail if user cannot moderate
		if ( ! current_user_can( 'manage_options' ) )
			return;
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
		add_action('wp_ajax_wplms_demo_data_download_install_activate_plugin',array($this,'wplms_demo_data_download_install_activate_plugin'));

	}

	/**
	 * Add admin menus/screens
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menus() {	

		
		$welcome_page_title = __( 'Welcome to WPLMS', 'vibe' );
		
		$about_page_name = __( 'About WPLMS', 'vibe' );
		add_dashboard_page( $welcome_page_title, $about_page_name, 'manage_options', 'wplms-about', array( $this, 'about_screen' ) );
		
		if ( empty( $_GET['page'] ) ) {
			return;
		}

		$welcome_page_name  = __( 'About WPLMS', 'vibe' );
		$welcome_page_title = __( 'Welcome to WPLMS', 'vibe' );
		
		$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'wplms-about', array( $this, 'about_screen' ) );
		add_action( 'admin_print_styles-'. $page, array( $this, 'admin_css' ) );
			
	}

	/**
	 * admin_css function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_css() {
		wp_enqueue_style( 'intro_font',"https://fonts.googleapis.com/css2?family=Mulish:wght@200;300;400;700;900&display=swap" );
		wp_enqueue_style( 'vibe-activation', VIBE_URL.'/setup/installer/css/envato-setup.css',array(),rand(0,999));
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_head() {
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'wplms-about'){
			remove_submenu_page( 'index.php', 'wplms-about' );		
		}

		?>
		<style type="text/css">
			/*<![CDATA[*/
			.wplms-wrap .wplms-badge {
				<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
			}
			.wplms-wrap .feature-rest div {
				float:<?php echo is_rtl() ? 'right':'left' ; ?>;
			}
			.wplms-wrap .feature-rest div.last-feature {
				padding-<?php echo is_rtl() ? 'right' : 'left'; ?>: 50px !important;
				padding-<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
			}
			.three-col > div{
				float:<?php echo is_rtl() ? 'right':'left' ; ?>;
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Into text/links shown on all about pages.
	 *
	 * @access private
	 * @return void
	 */
	private function intro() {

		// Flush after upgrades
		if ( ! empty( $_GET['wplms-updated'] ) || ! empty( $_GET['wplms-installed'] ) )
			flush_rewrite_rules();
		?>
		<h1><?php printf( __( 'WPLMS %s', 'vibe' ), $this->major_version ); ?></h1>

		<?php 

		
		$this->purchase_code = get_option('wplms_purchase_code');
		if(empty($this->purchase_code)){
			?>
				<a href="<?php echo admin_url('/themes.php?page=wplms-setup&step=updates&referrer=about'); ?>" class="important_notice"> <?php wp_nonce_field(); ?>
					Plugin auto-updates not configured. Click here to Setup Plugin auto-updates.
				</a>

			<?php
		}
		?>
		

		<div class="about-text wplms-about-text">
			Thank you for installing <strong>WPLMS Theme</strong>. This is the about page for WPLMS.<br><span style="opacity:0.6;font-size:11px"> It has three sections. The first section tells you about what new has been added in the theme. The second section tells the system status and configurations in your site. The third section is the changelog for advance users for bug tracking and resolution.</span>
		</div>
		<a href="https://docs.wplms.io/" target="_blank">Latest Documentation ›</a>
		<br><br>
		<?php
	}

	
	
	/**
	 * Output the about screen.
	 */
	public function about_screen() {
		?>
		<div class="setup_wizard_wrapper_wrapper">
		<div class="setup_wizard_wrapper">
			<div class="onboarding introduction">
				<div class="onboarding_header">
					<span><img id="wplms_logo" class="site-logo" src="http://localhost/wplms/wp-content/themes/wplms/assets/images/logo.png" alt="wplms">
						<span>The WordPress LMS</span>
					</span>
					
				</div>
				<div class="onboarding_introduction">
					<h2>Maintenance Update.</h2>
					<span><a>40 Changes</a>. 0 Features. 12 Enhancements . 20 Bug Fixes.</span>
				</div>
				<a href="https://wplms.io/support/article-categories/update-log/" target="_blank">View full update log ›</a>
			</div>
			<div class="setup_wizard_main">
				<div class="setup_wizard_main_header">
					<span></span>
					<span>Having Troubles ? <a href="https://wplms.io/support">Get Help</a></span>
				</div>
				<div class="envato-setup-content">				
					<?php $this->intro(); ?>

				<div class="wplms_about_tabs">
					
					<input type="radio" id="wplms_whats_new" name="wplms_about_active_tab" checked />
					<label for="wplms_whats_new">Whats New</label>
					
					<input type="radio" id="wplms_support" name="wplms_about_active_tab" />
					<label for="wplms_support">Support</label>

					<input type="radio" id="wplms_system_status" name="wplms_about_active_tab"  />
					<label for="wplms_system_status">System Status</label>
				
					<input type="radio" id="wplms_changelog" name="wplms_about_active_tab" />
					<label for="wplms_changelog">Changelog</label>
					<hr>
					
					<div class="wplms_about_tab wplms_whats_new"> 
						<?php 
						$this->welcome_screen();
						?>
					</div>
					<div class="wplms_about_tab wplms_support"> 
						<?php 
						$this->support_screen();
						?>
					</div>
					<div class="wplms_about_tab wplms_system_status"> 
						<?php 
						$this->system_screen();
						?>
					</div>
					<div class="wplms_about_tab wplms_changelog"> 
						<?php 
						$this->changelog_screen();
						?>
					</div>
				</div>
				<?php

				?>
				<p class="envato-setup-actions step">
					<div class="return-to-dashboard">
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wplms_options' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to WPLMS Options panel', 'vibe' ); ?></a>

					</div>
				</p>
				</div>
				

			</div>
			
		</div>	
		</div>
		<?php
	}

	public function welcome_screen(){
		?>
		<div class="welcome_slider">
  
		  	<div class="slides">
			    <div id="slide-1">
		    		<img src="https://vt-tfimages.s3.amazonaws.com/theme_welcome/quiz_question_bookmark.png" />
			    </div>
			    <div id="slide-2">
			    	<img src="https://vt-tfimages.s3.amazonaws.com/4.09-1.png" />
			    </div>
			    <div id="slide-3">
			    	<img src="https://vt-tfimages.s3.amazonaws.com/4.09-3.png" />
			    </div>
		  	</div>
		  	<div class="slider_dots">
		  		<a href="#slide-1"></a>
		  		<a href="#slide-2"></a>
		  		<a href="#slide-3"></a>
			</div>
		</div>
		<?php
	}

	public function support_screen(){
		?>
		<div class="wplms_support_wrapper">
			<a href="https://facebook.com/vibethemes">Get Live Support. Just drop a message.</a>
			<a href="https://wplms.io/support">Create a Support Topic</a>
			<strong>Your Active topics</strong>
			<?php if(empty($this->purchase_code)){?>Purchase code not found !<?php
			}else{
				$user_id = get_option('wplms_support_user_id');
				$args = array('purchase_code' => $this->purchase_code);
				if(!empty($user_id)){$args['user_id'] = $user_id; }

				$response = wp_remote_post('https://wplms.io/support/wp-json/wplmssupport/v1/get_topics',array(
			    'method'      => 'POST',
			    'timeout'     => 120,
			    'body'        => wp_json_encode($args)));

				$code = wp_remote_retrieve_response_code($response);

				if($code == 200){
				    $body = json_decode(wp_remote_retrieve_body($response),true);
				    
				    if(empty($body)){
				    	echo '<span>No topics found.</span>';
				    }else{
				    	if(!empty($body['user_id'])){
				    		update_option('wplms_support_user_id',$body['user_id']);
				    	}
				    	if($body['status']){
				    		if(!empty($body['topics'])){
				    			echo '<ul class="topic_list">';
				    			foreach($body['topics'] as $topic){
				    				echo '<li><a href="'.$topic['link'].'" target="_blank">'.$topic['title'].'<span>'.round((time()-strtotime($topic['date']))/86400,0).' Days</span></a><span class="'.($topic['status']['code'] == 0?'pending':'success').'">'.$topic['status']['label'].'</span></li>';
				    			}
				    			echo '</ul>';
				    		}else{
				    			echo '<span>No active topics found.</span>';
				    		}
				    	}else{
				    		echo '<span>No topics found.</span>';
				    	}
				    } 
			    }else{
			    	echo '<span class="error">Purchase Code Invalid Or Support Period over.</span>';
			    }
				?>

			<?php } ?>
		</div>
		<?php
	}

	public function system_screen(){
		?>
		<table class="wplms_status_table widefat" cellspacing="0" id="status">
				<thead>
					<tr>
						<th colspan="2"><h4><?php _e( 'Environment', 'vibe' ); ?></h4></th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<td><?php _e( 'Home URL', 'vibe' ); ?>:</td>
						<td><?php echo home_url(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Site URL', 'vibe' ); ?>:</td>
						<td><?php echo site_url(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Version', 'vibe' ); ?>:</td>
						<td><?php bloginfo('version'); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Multisite Enabled', 'vibe' ); ?>:</td>
						<td><?php if ( is_multisite() ) echo __( 'Yes', 'vibe' ); else echo __( 'No', 'vibe' ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Web Server Info', 'vibe' ); ?>:</td>
						<td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'PHP Version', 'vibe' ); ?>:</td>
						<td><?php if ( function_exists( 'phpversion' ) ) echo esc_html( phpversion() ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'MySQL Version', 'vibe' ); ?>:</td>
						<td>
							<?php
							/** @global wpdb $wpdb */
							global $wpdb;
							echo vibe_sanitizer($wpdb->db_version(),'text');
							?>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'WP Active Plugins', 'vibe' ); ?>:</td>
						<td><?php echo count( (array) get_option( 'active_plugins' ) ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Memory Limit', 'vibe' ); ?>:</td>
						<td><?php
							$memory = $this->wplms_let_to_num( WP_MEMORY_LIMIT );
							if ( $memory < 134217728 ) {
								echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 128MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'vibe' ), size_format( $memory ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
							} else {
								echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
							}
						?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Debug Mode', 'vibe' ); ?>:</td>
						<td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="no">' . __( 'Yes', 'vibe' ) . '</mark>'; else echo '<mark class="yes">' . __( 'No', 'vibe' ) . '</mark>'; ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Language', 'vibe' ); ?>:</td>
						<td><?php echo get_locale(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Max Upload Size', 'vibe' ); ?>:</td>
						<td><?php echo size_format( wp_max_upload_size() ); ?></td>
					</tr>
					<?php if ( function_exists( 'ini_get' ) ) : ?>
						<tr>
							<td><?php _e('PHP Post Max Size', 'vibe' ); ?>:</td>
							<td><?php echo size_format($this->wplms_let_to_num( ini_get('post_max_size') ) ); ?></td>
						</tr>
						<tr>
							<td><?php _e('PHP Time Limit', 'vibe' ); ?>:</td>
							<td><?php echo ini_get('max_execution_time'); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'PHP Max Input Vars', 'vibe' ); ?>:</td>
							<td><?php echo ini_get('max_input_vars'); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td><?php _e( 'Default Timezone', 'vibe' ); ?>:</td>
						<td><?php
							$default_timezone = date_default_timezone_get();
							if ( 'UTC' !== $default_timezone ) {
								echo '<mark class="error">' . sprintf( __( 'Default timezone is %s - it should be UTC', 'vibe' ), $default_timezone ) . '</mark>';
							} else {
								echo '<mark class="yes">' . sprintf( __( 'Default timezone is %s', 'vibe' ), $default_timezone ) . '</mark>';
							} ?>
						</td>
					</tr>
				</tbody>


				<thead>
					<tr>
						<th colspan="2"><h4><?php _e( 'Settings', 'vibe' ); ?></h4></th>
					</tr>
				</thead>

				<thead>
					<tr>
						<th colspan="2"><?php _e( 'WPLMS Pages', 'vibe' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php
						$check_pages = array(
							_x( 'All Course page', 'Page setting', 'vibe' ) => array(
									'option' => 'bp-pages.course'
								),
							_x( 'Default Certificate Page', 'Page setting', 'vibe' ) => array(
									'option' => 'certificate_page',
								)
						);

						$alt = 1;

						foreach ( $check_pages as $page_name => $values ) {

							if ( $alt == 1 ) echo '<tr>'; else echo '<tr>';

							echo '<td>' . esc_html( $page_name ) . ':</td><td>';

							$error = false;

							switch($values['option']){
								case 'bp-pages.course':
									$pages=get_option('bp-pages');
									if(isset($pages) && is_array($pages) && isset($pages['course']))
										$page_id=$pages['course'];
								break;
								default:
									$page_id = vibe_get_option($values['option']);
								break;
							}
							// Page ID check
							if ( ! isset($page_id ) ){
								echo '<mark class="error">' . __( 'Page not set', 'vibe' ) . '</mark>';
								$error = true;
							} else {
								$error = false;
							}

							if ( ! $error ) echo '<mark class="yes">#' . absint( $page_id ) . ' - ' . str_replace( home_url(), '', get_permalink( $page_id ) ) . '</mark>';

							echo '</td></tr>';

							$alt = $alt * -1;
						}
					?>
				</tbody>

			</table>
		<?php
	}

	/**
	 * Output the changelog screen
	 */
	public function changelog_screen() {
		?>
		<div class="wrap wplms-wrap about-wrap">

			
			<div class="changelog-description">
			<p><?php printf( __( 'Full Changelog of WPLMS Theme', 'vibe' ), 'vibe' ); ?></p>

			<?php
				$file = VIBE_PATH.'/changelog.txt';
				$myfile = fopen($file, "r") or die("Unable to open file!".$file);
				while(!feof($myfile)) {
					$string = fgets($myfile);
					if(strpos($string, '* version') === 0){
						echo '<br />---------------------- * * * ----------------------<br /><br />';
					}
				  echo vibe_sanitizer($string) . "<br>";
				}
				fclose($myfile);
			?>
			</div>
		</div>
		<?php
	}

	function scan_template_files( $template_path ) {
		
		$files         = scandir( $template_path );
		$result        = array();
		if ( $files ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( ".",".." ) ) ) {
					if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
						$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
						foreach ( $sub_files as $sub_file ) {
							$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
						}
					} else {
						$result[] = $value;
					}
				}
			}
		}
		return $result;
	}
	function get_file_version( $file ) {
		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' );

		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );
		$version   = '';

		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] )
			$version = _cleanup_header_comment( $match[1] );

		return $version ;
	} 
	/**
	 * Sends user to the welcome page on first activation
	 */
	public function welcome() {
		// Bail if no activation redirect transient is set
	    if ( ! get_transient( '_wplms_activation_redirect' ) ) {
			return;
	    }

		
		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || defined( 'IFRAME_REQUEST' ) ) {
			return;
		}

		if(!$this->check_installed()){
			wp_redirect( admin_url( 'themes.php?page=wplms-setup' ) );
		}else{
			wp_redirect( admin_url( 'index.php?page=wplms-about' ) );
		}
		exit;
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
	function check_installed(){


		$check = get_transient( '_wplms_activation_redirect' );
		if(!empty($check) && $check == 1){
			delete_transient( '_wplms_activation_redirect' );
			return false;
		}
		if(!empty($check) && $check == 2){
			delete_transient( '_wplms_activation_redirect' );
			return true;
		}

		return false;
	}
}

add_action('init','wplms_welcome_user');
function wplms_welcome_user(){
	new WPLMS_Admin_Welcome();	
}
