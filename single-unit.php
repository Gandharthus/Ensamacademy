<?php
if ( !defined( 'ABSPATH' ) ) exit;
get_header(vibe_get_header());
$unit_comments = vibe_get_option('unit_comments');
 
if ( have_posts() ) : while ( have_posts() ) : the_post();
$id = get_the_ID();
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                    <h1 id="unit" data-unit="<?php echo get_the_ID(); ?>"><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
            <div class="col-md-3 col-sm-4">
                <?php
                if(isset($_GET['id']) && is_numeric($_GET['id'])){
                
                  global $wpdb;
                  $uid=get_the_ID();
                  $course_id = $_GET['id'];
                  if(function_exists('bp_course_get_unit_course_id') && empty($course_id)){
                    $course_id = bp_course_get_unit_course_id($uid);
                  }
                  if(is_numeric($course_id) && get_post_type($course_id) == 'course'){
                    $extension = '';
                    
                    echo '<input id="course_id" type="hidden" value="'.$course_id.'"><a href="'.get_permalink($course_id).'" class="course_button button full">'.__('Back to Course','vibe').'</a>';
                  }else{
                    vibe_breadcrumbs();
                  }
                }
                ?>
            </div>
        </div>
    </div>
</section>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="unit_wrap <?php if(isset($unit_comments) && is_numeric($unit_comments)){echo 'enable_comments';} ?>">
                    <div id="unit_content" class="unit_content">
                    <?php
                    $unit_class = 'unit_class single_unit_content';
                    $unit_class=apply_filters('wplms_unit_classes',$unit_class,$id);
                    ?>
                    <div class="main_unit_content <?php echo vibe_sanitizer($unit_class,'text');?>">
                    <?php if(has_post_thumbnail()){ ?>
                    <div class="featured">
                        <?php the_post_thumbnail(get_the_ID(),'full'); ?>
                    </div>
                    <?php
                    }
                        the_content();
                    ?>
                    <?php wp_link_pages('before=<div class="unit-page-links page-links"><div class="page-link">&link_before=<span>&link_after=</span>&after=</div></div>'); 
                    ?>
                    </div> 
                    <div class="unit_tags">
                    <?php 
                    if(function_exists('the_unit_tags'))
                      the_unit_tags($id); 
                    ?>
                    </div>   
                    <?php
                    
                    $attachments = apply_filters('wplms_unit_attachments',1,$id);
                    if($attachments){
                        if(function_exists('bp_course_get_unit_attachments')){
                            echo bp_course_get_unit_attachments($id);
                        }
                    }
                    
                    do_action('wplms_after_every_unit',get_the_ID());
                    
                     ?>
                    </div>
                  
                </div>
                <?php

                endwhile;
                endif;
                ?>
                <?php
                if(isset($unit_comments) && is_numeric($unit_comments)){
                    echo "<script>jQuery(document).ready(function($){ $('.unit_content').trigger('load_comments'); });</script>
                    <style>.note.user$post->post_author img{border: 2px solid #70c989;}</style><div class='unit_prevnext'>&nbsp;</div>";
                }
                wp_nonce_field('security','hash');
                do_action('wplms_unit_end_front_end_controls');
                ?>
            </div>
            <div class="col-md-3 col-sm-4">
                <?php
                global $wp_query;
                if(isset($_GET['edit']) || isset($wp_query->query_vars['edit'])){
                    do_action('wplms_front_end_unit_controls');
                }else{
                    $sidebar = apply_filters('wplms_sidebar','coursesidebar',get_the_ID());
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) {}
                }
                ?>
            </div>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());