<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$header_style =  vibe_get_customizer('header_style');
if($header_style == 'transparent' || $header_style == 'generic'){ 
	echo '<section id="title">';
	do_action('wplms_before_title');
	echo '</section>';
}

$layout = new WP_Query(apply_filters('vibebp_group_layout_query',array(
	'post_type'=>'group-layout',
	'post_name'=>bp_get_group_type(bp_get_group_id()),
	'posts_per_page'=>1,
	'meta_query'=>array(
		'relation'=>'AND',
		array(
			'key'=>'group_type',
			'compare'=>'NOT EXISTS'
		)
	)
)));
if ( !$layout->have_posts() ){

	$layout = new WP_Query(array(
		'post_type'=>'group-layout',
		'orderby'=>'date',
		'order'=>'ASC',
		'posts_per_page'=>1,
	));
}

if ( !$layout->have_posts() ){
	wp_die(__('Create a new group layout.','vibebp'));
}

?>
<div id="vibebp_group"> 
<div id="primary" class="content-area">
	<div class="container">
		<main id="group_<?php echo bp_get_group_id(); ?>">
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
<?php
