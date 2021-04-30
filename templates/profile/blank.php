<?php
$header_style =  vibe_get_customizer('header_style');
if($header_style == 'transparent' || $header_style == 'generic'){ 
	echo '<section id="title">';
	do_action('wplms_before_title');
	echo '</section>';
}

$layout = new WP_Query(apply_filters('vibebp_public_profile_layout_query',array(
	'post_type'=>'member-profile',
	'post_name'=>bp_get_member_type(bp_displayed_user_id()),
	'posts_per_page'=>1,
	'meta_query'=>array(
		'relation'=>'AND',
		array(
			'key'=>'member_type',
			'compare'=>'NOT EXISTS'
		)
	)
)));

if ( !$layout->have_posts() ){

	$layout = new WP_Query(array(
		'post_type'=>'member-profile',
		'orderby'=>'date',
		'order'=>'ASC',
		'posts_per_page'=>1,
		'meta_query'=>array(
			'relation'=>'AND',
			array(
				'key'=>'member_type',
				'compare'=>'NOT EXISTS'
			)
		)	
	));
}

if ( !$layout->have_posts() ){
	wp_die(__('Create a Member Profile layout in WP admin - VibeBp - Member Profiles','vibebp'));
}

?>

<div id="vibebp_member">
	<div id="primary" class="content-area">
		<div class="container">
			<main id="user_<?php echo bp_displayed_user_id(); ?>" <?php (function_exists('vibebp_member_class'))?vibebp_member_class():''; ?>>
			<?php
			if ( $layout->have_posts() ) :
				
				/* Start the Loop */
				while ( $layout->have_posts() ) :
					$layout->the_post();
					the_content();
					if(class_exists('\Elementor\Frontend')){
						
					 	$elementorFrontend = new \Elementor\Frontend();
	                    $elementorFrontend->enqueue_scripts();
	                    $elementorFrontend->enqueue_styles();
	                }
					break;
				endwhile;
			
				
			endif;
			?>

			</main><!-- #main -->
		</div>
	</div><!-- #primary -->
</div>