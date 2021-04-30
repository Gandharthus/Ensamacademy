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
    <div class="login_sidebar">
        <div class="login_content">
        <?php
            vibe_include_template("login/default_login.php");
         ?>
        </div>
    </div>
    <div class="pusher">
        <?php
            $fix=vibe_get_option('header_fix');
        ?>
        <header class="mooc app <?php if(isset($fix) && $fix){echo 'fix';} ?>">
            <?php
                if(function_exists('is_wplms_4_0') && is_wplms_4_0()){
                    echo apply_filters('wplms_login_trigger','<a class="vibebp-login"><span><i class="vicon vicon-user"></i></span></a>');
                    do_action('wp_head_wplms_login');
                }else{
                    if(is_user_logged_in()){
                        ?>
                        <a id="login_trigger"><?php bp_loggedin_user_avatar( 'type=full' ); ?></a>
                        <?php
                    }else{
                        ?>
                        <a id="login_trigger"><span><i class="vicon vicon-user"></i></span></a>
                        <?php
                    }
                }
            ?>
            <div class="<?php echo vibe_get_container(); ?>">
                
                <div class="col-md-5 col-sm-5">    
                    <div id="mooc_menu"> 
                          <?php
                            $args = apply_filters('wplms-main-menu',array(
                                 'theme_location'  => 'main-menu',
                                 'container'       => 'nav',
                                 'menu_class'      => 'menu',
                                 'walker'          => new vibe_walker,
                                 'fallback_cb'     => 'vibe_set_menu'
                             ));
                            wp_nav_menu( $args ); 
                        ?>
                    </div>
                </div>
                <div class="col-md-2 col-sm-2">    
                   
                        <?php

                            if(is_front_page()){
                                echo '<h1 id="logo">';
                            }else{
                                echo '<h2 id="logo">';
                            }

                            $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','header');
                            if(!empty($url)){
                        ?>
                        
                            <a href="<?php echo vibe_site_url('','logo'); ?>"><img src="<?php  echo vibe_sanitizer($url,'url'); ?>" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                        <?php
                            }
                            if(is_front_page()){
                                echo '</h1>';
                            }else{
                                echo '</h2>';
                            }
                            
                        ?>
                </div>
                <div class="col-md-5 col-sm-5">
                    <ul class="topmenu">
                        <li><a id="new_searchicon"><i class="vicon vicon-search"></i></a></li>
                        <?php
                            if ( function_exists('bp_loggedin_user_link') && is_user_logged_in() ) :
                                
                                    if(function_exists('bp_get_total_unread_messages_count')){
                                ?>

                                    <li><a href="<?php echo bp_loggedin_user_domain().'#messages'; ?>"><i class="vicon vicon-email"></i><?php $n=bp_get_total_unread_messages_count(); echo (($n)?'<span>'.$n.'</span>':''); ?></a></li>
                                <?php
                                    }
                                    
                                    if(function_exists('bp_notifications_get_unread_notification_count')){
                                
                                ?>    
                                    <li><a href="<?php echo bp_loggedin_user_domain().'#notifications'; ?>"><i class="vicon vicon-bell"></i><?php $n=bp_notifications_get_unread_notification_count( bp_loggedin_user_id() ); echo (($n)?'<span>'.$n.'</span>':''); ?></a></li>
                                <?php
                                    }
                            endif;    
                                ?>    
                                    <?php
                                    if (apply_filters('wplms_show_mini_cart',function_exists('woocommerce_mini_cart'))) { global $woocommerce;
                                    ?>
                                    <li><a class=" vbpcart"><span class="vicon vicon-shopping-cart"><?php echo (($woocommerce->cart->cart_contents_count)?'<em>'.$woocommerce->cart->cart_contents_count.'</em>':''); ?></span></a>
                                    <div class="woocart"><div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div></div>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                    <?php do_action('wplms_header_top_login'); ?>
                                </ul>
                            <?php
                        ?>
                </div>
                <a id="trigger">
                    <span class="lines"></span>
                </a>

            </div>
        </header>
