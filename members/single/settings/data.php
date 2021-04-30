<?php

/**
 * BuddyPress Member Settings
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

get_header( vibe_get_header() ); 

$profile_layout = vibe_get_customizer('profile_layout');

vibe_include_template("profile/top$profile_layout.php");  

?>
<div id="item-body">
	<?php do_action( 'bp_before_member_body' ); ?>
	
	<div class="item-list-tabs no-ajax" id="subnav">
		<ul>
	 
			<?php bp_get_options_nav(); ?>

			<?php do_action( 'bp_member_plugin_options_nav' ); ?>

		</ul>
	</div><!-- .item-list-tabs -->
	<?php do_action('wplms_after_single_item_list_tabs'); ?>
	<?php do_action('bp_before_member_settings_template'); ?>


<h2><?php _e( 'Data Export', 'vibe' );?></h2>

<?php $request = bp_settings_get_personal_data_request(); ?>

<?php if ( $request ) : ?>

	<?php if ( 'request-completed' === $request->status ) : ?>

		<?php if ( bp_settings_personal_data_export_exists( $request ) ) : ?>

			<p><?php esc_html_e( 'Your request for an export of personal data has been completed.', 'vibe' ); ?></p>
			<p><?php printf( esc_html__( 'You may download your personal data by clicking on the link below. For privacy and security, we will automatically delete the file on %s, so please download it before then.', 'vibe' ), bp_settings_get_personal_data_expiration_date( $request ) ); ?></p>

			<p><strong><?php printf( '<a href="%1$s">%2$s</a>', bp_settings_get_personal_data_export_url( $request ), esc_html__( 'Download personal data', 'vibe' ) ); ?></strong></p>

		<?php else : ?>

			<p><?php esc_html_e( 'Your previous request for an export of personal data has expired.', 'vibe' ); ?></p>
			<p><?php esc_html_e( 'Please click on the button below to make a new request.', 'vibe' ); ?></p>

			<form id="bp-data-export" method="post">
				<input type="hidden" name="bp-data-export-delete-request-nonce" value="<?php echo wp_create_nonce( 'bp-data-export-delete-request' ); ?>" />
				<button type="submit" name="bp-data-export-nonce" value="<?php echo wp_create_nonce( 'bp-data-export' ); ?>"><?php esc_html_e( 'Request new data export', 'vibe' ); ?></button>
			</form>

		<?php endif; ?>

	<?php elseif ( 'request-confirmed' === $request->status ) : ?>

		<p><?php printf( esc_html__( 'You previously requested an export of your personal data on %s.', 'vibe' ), bp_settings_get_personal_data_confirmation_date( $request ) ); ?></p>
		<p><?php esc_html_e( 'You will receive a link to download your export via email once we are able to fulfill your request.', 'vibe' ); ?></p>

	<?php endif; ?>

<?php else : ?>

	<p><?php esc_html_e( 'You can request an export of your personal data, containing the following items if applicable:', 'vibe' ); ?></p>

	<?php bp_settings_data_exporter_items(); ?>

	<p><?php esc_html_e( 'If you want to make a request, please click on the button below:', 'vibe' ); ?></p>

	<form id="bp-data-export" method="post">
		<button type="submit" name="bp-data-export-nonce" value="<?php echo wp_create_nonce( 'bp-data-export' ); ?>"><?php esc_html_e( 'Request personal data export', 'vibe' ); ?></button>
	</form>

<?php endif; ?>

<!--
<h2 class="bp-screen-reader-text"><?php
	/* translators: accessibility text */
	_e( 'Data Erase', 'vibe' );
?></h2>

<p>You can make a request to erase the following type of data from the site:</p>

<p>If you want to make a request, please click on the button below:</p>

	<form id="bp-data-erase" method="post">
		<button type="submit" name="bp-data-erase-nonce" value="<?php echo wp_create_nonce( 'bp-data-erase' ); ?>">Request data erasure</button>
	</form>
-->

<?php

/** This action is documented in bp-templates/bp-legacy/vibe/members/single/settings/profile.php */
do_action( 'bp_after_member_settings_template' );



vibe_include_template("profile/bottom.php");  

get_footer( vibe_get_footer() );  

