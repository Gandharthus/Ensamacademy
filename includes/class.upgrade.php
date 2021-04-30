<?php
/**
 * Upgrade functions for WPLMS
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     2.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;

class WPLMS_Upgrade{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Upgrade();

        return self::$instance;
    }

    private function __construct(){

    	add_filter('wplms_4_0_course',array($this,'check_templates'));
    }

    function check_templates($check){
    	$course_layout = vibe_get_customizer('course_layout');
    	if($course_layout != 'blank'){
    		return false;
    	}
    	return $check;
    }
}

WPLMS_Upgrade::init();