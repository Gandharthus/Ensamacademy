<?php

if ( ! defined( 'ABSPATH' ) ) exit;

update_option( 'wplms_purchase_code', 'NULLED' );

if(!defined('WPLMS_THEME_FILE_INCLUDE_PATH')){
	define('WPLMS_THEME_FILE_INCLUDE_PATH',get_template_directory());
	//use this if you want to overwrite core functions from includes directory with your child theme
	//copy includes and _inc folder into your child them and define path constant to child theme
	
	//define('WPLMS_THEME_FILE_INCLUDE_PATH',get_stylesheet_directory());
}

if(defined('WPLMS_THEME_FILE_INCLUDE_PATH')){
	// Essentials
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/config.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/init.php';

	// Register & Functions
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/register.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/actions.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/filters.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/class.upgrade.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/func.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/ratings.php'; 
	// Customizer
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/customizer/customizer.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/customizer/css.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/vibe-menu.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/notes-discussions.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/wplms-woocommerce-checkout.php';

	if ( function_exists('bp_get_signup_allowed')) {
	    include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/bp-custom.php';
	}

	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/_inc/ajax.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/buddydrive.php';
	//Widgets
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/widgets/custom_widgets.php';
	if ( function_exists('bp_get_signup_allowed')) {
	 include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/widgets/custom_bp_widgets.php';
	}
	if (function_exists('pmpro_hasMembershipLevel')) {
	    include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/pmpro-connect.php';
	}
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/widgets/advanced_woocommerce_widgets.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/widgets/twitter.php';
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/widgets/flickr.php';

	//Misc
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/includes/extras.php';

	//SETUP
	include_once WPLMS_THEME_FILE_INCLUDE_PATH.'/setup/wplms-install.php';
}


// Options Panel
get_template_part('vibe','options');