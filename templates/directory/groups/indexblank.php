<?php

do_action('wplms_before_groups_directory');
$id = vibe_get_bp_page_id('groups');
get_header( vibe_get_header() ); 
?>
<?php do_action( 'bp_before_directory_groups_page' ); ?>

<section id="title">
	<?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <h1><?php echo get_the_title($id); ?></h1>
                    <?php the_sub_title($id); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<?php
	
	global $post;
	
	$q = new WP_Query(array('p'=>$id,'post_type'=>'page'));
	
	if($q->have_posts()){
		while($q->have_posts()){
			$q->the_post();
			global $post;
			
			the_content();
		}
	}
	
	?>
</section><!-- #primary -->
<?php

get_footer( vibe_get_footer() );  
?>