<?php
//Header File
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<?php
    wp_head();
?>
</head>
<body <?php body_class(); ?>>
<div id="global" class="global">
    <?php
        get_template_part('mobile','sidebar');
    ?>
    <div class="pusher">
        <?php
            $fix=vibe_get_option('header_fix');
        ?>
        <div id="headertop" class="<?php if(isset($fix) && $fix){echo 'fix';} ?>">
            <div class="<?php echo vibe_get_container(); ?>">
                <div class="row">
                    <div class="col-md-4 col-sm-3 col-xs-4  col-4">
                        <?php

                        $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','headertop');
                        if(!empty($url)){
                            ?>
                            <a href="<?php echo vibe_site_url('','logo'); ?>" class="homeicon"><img src="<?php  echo vibe_sanitizer($url,'url'); ?>" width="100" height="48" alt="<?php echo get_bloginfo('name'); ?>" /></a> 
                            <?php
                        }

                        ?>
                    </div>
                    <div class="col-md-8 col-sm-9 col-xs-8  col-8">
                    <ul class="topmenu">
                        <?php
                        if(function_exists('is_wplms_4_0') && is_wplms_4_0()){
                            echo '<li>'.apply_filters('wplms_login_trigger','<a href="#login" rel="nofollow" class=" vibebp-login"><span>'.__('LOGIN','vibe').'</span></a>').'</li>';
                            do_action('wp_head_wplms_login');
                        }else{

                        
                        if ( function_exists('bp_loggedin_user_link') && is_user_logged_in() ) :
                            ?>
                                <li><a href="<?php bp_loggedin_user_link(); ?>" class="smallimg vbplogin"><?php $n=vbp_current_user_notification_count(); echo ((isset($n) && $n)?'<em></em>':''); bp_loggedin_user_avatar( 'type=full' ); ?><?php bp_loggedin_user_fullname(); ?></a></li>
                                <?php do_action('wplms_header_top_login'); ?>
                            
                        <?php
                        else :
                            ?>
                                <li><a href="#login" class="vbplogin"><?php _e('Login','vibe'); ?></a></li>
                                <li><?php 
                                    $enable_signup = apply_filters('wplms_enable_signup',0);
                                    if ( $enable_signup ) : 
                                    $registration_link = apply_filters('wplms_buddypress_registration_link',site_url( BP_REGISTER_SLUG . '/' ));
                                    printf( __( '<a href="%s" class="vbpregister" title="'.__('Create an account','vibe').'">'.__('Sign Up','vibe').'</a> ', 'vibe' ), $registration_link );
                                endif; ?>
                                </li>
                        <?php
                        endif;
                    }
                    ?>
                    </ul>
                    <?php
                            $args = apply_filters('wplms-top-menu',array(
                                'theme_location'  => 'top-menu',
                                'container'       => '',
                                'depth'           => 1,
                                'menu_class'      => 'topmenu',
                                'fallback_cb'     => 'vibe_set_menu',
                            ));

                        wp_nav_menu( $args );
                        ?>
                    </div>
                    <?php
                    if(!function_exists('is_wplms_4_0') || !is_wplms_4_0()){
                         $style = vibe_get_login_style();
                        if(empty($style)){
                            $style='default_login';
                        }
                    ?>
                    <div id="vibe_bp_login" class="<?php echo vibe_sanitizer($style,'text'); ?>">
                    <?php
                        vibe_include_template("login/$style.php");
                     ?>
                   </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <header>
            <div class="<?php echo vibe_get_container(); ?>">
                <div class="row">
                    <div class="col-md-3 col-sm-3 col-xs-4 col-4">
                        <?php

                            if(is_front_page()){
                                echo '<h1 id="logo">';
                            }else{
                                echo '<h2 id="logo">';
                            }

                            $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','header');
                            if(!empty($url)){
                        ?>
                            <a href="<?php echo vibe_site_url(); ?>"><img src="<?php  echo vibe_sanitizer($url,'url'); ?>" width="100" height="48" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                        <?php
                            }
                            if(is_front_page()){
                                echo '</h1>';
                            }else{
                                echo '</h2>';
                            }
                        ?>
                    </div>
                    <div class="col-md-9 col-sm-9 col-xs-8 col-8">
                        <?php
                            $args = apply_filters('wplms-main-menu',array(
                                 'theme_location'  => 'main-menu',
                                 'container'       => 'nav',
                                 'menu_class'      => 'menu',
                                 'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s<li><a id="new_searchicon"><i class="vicon vicon-search"></i></a></li></ul>',
                                 'walker'          => new vibe_walker,
                                 'fallback_cb'     => 'vibe_set_menu'
                             ));
                            wp_nav_menu( $args ); 
                        ?>
                        <a id="trigger">
                            <span class="lines"></span>
                        </a> 
                    </div>
                </div>
            </div>
        </header>
