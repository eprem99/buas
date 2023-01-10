<?php
/**
 * Social Profile Template
 *
 * @package TutorLMS/Templates
 * @version v2.0.0
 */

$user = wp_get_current_user();
$childrens = get_user_meta( $user->ID, 'children', false );
?>

<h3><?php esc_html_e( 'Settings', 'tutor' ); ?></h3>

<div class="tutor-dashboard-setting-social tutor-dashboard-content-inner">

	<div class="tutor-mb-32">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'children' ) ); ?>
		<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-32"><?php esc_html_e( 'Your children', 'tutor' ) ?></div>
	</div>
	<div class="tutor-row tutor-gx-lg-4">
	<?php
		foreach ( $childrens as $children ) :
	?>
		<div class="tutor-col-lg-6 tutor-col-xl-4 tutor-mb-16 tutor-mb-lg-32">
			<div class="tutor-card">
				<div class="tutor-d-flex tutor-flex-lg-column tutor-align-center tutor-text-lg-center tutor-px-12 tutor-px-lg-24 tutor-py-8 tutor-py-lg-32">
					<span class="tutor-round-box tutor-mr-12 tutor-mr-lg-0 tutor-mb-lg-12">
						<i class="tutor-icon-book-open" area-hidden="true"></i>
					</span>
					<div class="tutor-fs-3 tutor-fw-bold tutor-d-none tutor-d-lg-block">0</div>
					<div class="tutor-fs-7 tutor-color-secondary"><?php echo $children; ?></div>
					<div class="tutor-fs-4 tutor-fw-bold tutor-d-block tutor-d-lg-none tutor-ml-auto">0</div>
				</div>
			</div>
		</div>
		<?php endforeach; ?>

	</div>




	<form id="user_social_form" action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
		<input type="hidden" value="tutor_social_profile" name="tutor_action" />
		<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-6">
			<input class="tutor-form-control" type="text" name="children" value="" placeholder="">
		</div>
		<div class="tutor-row">
			<div class="tutor-col-12">
				<button type="submit" class="tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'Update Profile', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
