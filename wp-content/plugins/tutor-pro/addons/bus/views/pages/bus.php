<?php

/**
 * Bus List Template.
 *
 * @package Bus List
 */

if (!defined('ABSPATH')) {
	exit;
}

use TUTOR_BUS\Bus;

$bus = new Bus();


/**
 * Short able params
 */
$user_id   = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$course_id = isset($_GET['course-id']) ? $_GET['course-id'] : '';
$order     = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$date      = isset($_GET['date']) ? tutor_get_formated_date('Y-m-d', $_GET['date']) : '';
$search    = isset($_GET['search']) ? $_GET['search'] : '';

/**
 * Determine active tab
 */
$active_tab = isset($_GET['data']) && $_GET['data'] !== '' ? esc_html__($_GET['data']) : 'all';

/**
 * Navbar data to make nav menu
 */
$url                        = get_pagenum_link();
$add_bus_url          = $url . '&sub_page=add_new_bus';
$add_bus_settings_url = $url . '&sub_page=buss';
$navbar_data                = array(
	'page_title'   => $bus->page_title,
	'tabs'         => $bus->tabs_key_value($course_id),
	'active'       => $active_tab,
	'add_button'   => true,
	'button_title' => __('Add New', 'tutor'),
	'button_url'   => $add_bus_url,
	'modal_target' => 'tutor-add-new-grade',
);

$filters = array(
	'bulk_action'   => false,
	'bulk_actions'  => $bus->prpare_bulk_actions(),
	'ajax_action'   => 'tutor_bus_bulk_action',
	'filters'       => true,
	'course_filter' => true,
	'course_filter' => true,
);

?>
<div class="tutor-admin-wrap">
	<?php
	/**
	 * Load Templates with data.
	 */
	$navbar_template = tutor()->path . 'views/elements/navbar.php';
	tutor_load_template_from_custom_path($navbar_template, $navbar_data);
	$bus = tutor_utils()->get_buss();
	?>
	<div class="tutor-admin-body">
		<div class="tutor-mt-24">
			<div class="tutor_admin_bus_list tutor-table-wrapper">
				<?php tutor_alert(null, 'success'); ?>
				<?php if (tutor_utils()->count($bus)) { ?>
					<div class="tutor-table-responsive">
						<table class="tutor-table tutor-buss-lists">
							<thead>
								<tr>
									<th width="12%">
										<?php esc_html_e('Grade Badge', 'tutor-pro'); ?>
									</th>
									<th width="12%">
										<?php esc_html_e('Grade Name', 'tutor-pro'); ?>
									</th>
									<th width="12%">
										<?php esc_html_e('Grade Point', 'tutor-pro'); ?>
									</th>
									<th width="12%">
										<?php esc_html_e('Grade Range %', 'tutor-pro'); ?>
									</th>
									<th width="52%"></th>
								</tr>
							</thead>

							<tbody>
								<?php
									foreach ( $bus as $bus ) :
										$config 			= maybe_unserialize($bus->grade_config);
										$grade_delete_id 	= "bus-id-" . $bus->bus_id;
								?>
									<tr>
										<td>
											<span class="gradename-bg" style="background-color: <?php echo tutor_utils()->array_get('grade_color', $config); ?>;">
												<?php echo esc_html($bus->grade_name); ?>
											</span>
										</td>
										<td><?php echo esc_html($bus->grade_name); ?></td>
										<td><?php echo esc_html($bus->grade_point); ?></td>
										<td><?php echo esc_html($bus->percent_from . '-' . $bus->percent_to); ?></td>
										<td class="tutor-text-right">
											<div class="tutor-d-inline-flex tutor-align-center tutor-gap-1">
												<a class="bus-edit-btn tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-open-grade-update-modal" data-tutor-modal-target="tutor-update-existing-grade" data-id="<?php echo esc_attr($bus->bus_id); ?>" data-name="<?php echo esc_attr($bus->grade_name); ?>" data-point="<?php echo esc_attr($bus->grade_point); ?>" data-maximum="<?php echo esc_attr($bus->percent_to); ?>" data-minimum="<?php echo esc_attr($bus->percent_from); ?>" data-color="<?php echo esc_attr(tutor_utils()->array_get('grade_color', $config)); ?>">
													<?php esc_html_e('Edit', 'tutor-pro'); ?>
												</a>
	
												<a href="javascript:;" class="bus-delete-btn tutor-btn tutor-btn-outline-primary tutor-btn-sm" data-tutor-modal-target="<?php echo $grade_delete_id; ?>">
													<?php esc_html_e('Delete', 'tutor-pro'); ?>
												</a>
											</div>
										</td>
									</tr>
								<?php
									// Delete confirmation modal
									tutor_load_template('modal.confirm', array(
										'id' => $grade_delete_id,
										'image' => 'icon-trash.svg',
										'title' => __('Do You Want to Delete This Grade?', 'tutor-pro'),
										'content' => __('Are you sure you want to delete this grade permanently? Please confirm your choice.', 'tutor-pro'),
										'yes' => array(
											'text' => __('Yes, Delete This', 'tutor-pro'),
											'attr' => array(
												'data-delete_url="' . add_query_arg(array('tutor_action' => 'delete_bus', 'bus_id' => $bus->bus_id)) . '"'
											)
										),
									));
								endforeach;
								?>
							</tbody>
						</table>
					</div>
				<?php
				} else {
					$alert_template = tutor()->path . 'templates/global/alert.php';
					if (file_exists($alert_template) && function_exists('tutor_load_template_from_custom_path')) {
						$args = array(
							'alert_class'  => 'tutor-alert tutor-warning',
							'message'      => __('No grading system has been defined to manage student grades.'),
							'icon'         => 'tutor-icon-circle-info',
							'button_text'  => 'Import Sample Grade Data',
							'button_class' => 'tutor-btn tutor-btn-sm',
							'button_id'    => 'import-bus-sample-data',
						);
						tutor_load_template_from_custom_path($alert_template, $args);
					}
				}
				?>
			</div>
		</div>
	</div>
</div>

<!-- Add New Grade Modal -->
<div id="tutor-add-new-grade" class="tutor-modal">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<form id="tutor-add-new-bus-form" class="tutor-modal-content" method="post" autocomplete="off">
			<div class="tutor-modal-header">
				<div class="tutor-modal-title">
					<?php esc_html_e('Add New Grade', 'tutor-pro'); ?>
				</div>
				<button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close>
					<span class="tutor-icon-times" area-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<input type="hidden" name="action" value="add_new_bus">
				<?php tutor_nonce_field(); ?>
				<?php do_action('tutor_add_new_grade_form_fields_before'); ?>
				<div class="tutor-row">
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Grade', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="grade_name" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr('Grade Name', 'tutor'); ?>" required />
						</div>
					</div>
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Point', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="grade_point" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr('Grade Point', 'tutor'); ?>" required />
						</div>
					</div>
				</div>

				<div class="tutor-row">
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Maximum Percentile', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="percent_to" class="tutor-form-control tutor-mb-12" autocomplete="off" placeholder="<?php echo esc_attr('Maximum Percentile', 'tutor-pro'); ?>" required />
						</div>
					</div>
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Minimum Percentile', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="percent_from" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr('Minimum Percentile', 'tutor-pro'); ?>" required />
						</div>
					</div>
				</div>

				<div class="tutor-row">
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Color', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" class="tutor_colorpicker" name="grade_config[grade_color]">
						</div>
					</div>
				</div>
				<?php do_action('tutor_add_new_grade_form_fields_after'); ?>
				<div class="tutor-row" id="tutor-add-new-grad-form-response"></div>
			</div>

			<div class="tutor-modal-footer">
				<button type="submit" class="tutor-btn tutor-btn-primary" data-tutor-modal-submit>
					<?php esc_html_e('Add new Grade', 'tutor-pro'); ?>
				</button>
				<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
					<?php esc_html_e('Cancel', 'tutor-pro'); ?>
				</button>
			</div>
		</form>
	</div>
</div>

<!-- Update Grade Modal -->
<div id="tutor-update-existing-grade" class="tutor-modal">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<form id="tutor-update-bus-form" class="tutor-modal-content" method="post" autocomplete="off">
			<div class="tutor-modal-header">
				<div class="tutor-modal-title">
					<?php esc_html_e('Update Grade', 'tutor-pro'); ?>
				</div>
				<button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close>
					<span class="tutor-icon-times" area-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<input type="hidden" name="action" value="update_bus">
				<input type="hidden" name="bus_id" value="">
				<?php tutor_nonce_field(); ?>
				<?php do_action('tutor_upate_existing_grade_form_fields_before'); ?>
				<div class="tutor-row tutor-mx-0">
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Name', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="grade_name" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr('Enter Name', 'tutor-pro'); ?>" required />
						</div>
					</div>
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Grade Point', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="grade_point" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr('Enter Grade Point', 'tutor-pro'); ?>" required />
						</div>
					</div>
				</div>
				<div class="tutor-row tutor-mx-0">
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Maximum Percentile', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="percent_to" class="tutor-form-control tutor-mb-12" autocomplete="off" placeholder="<?php echo esc_attr('Maximum Percentile', 'tutor-pro'); ?>" required />
						</div>
					</div>
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Minimum Percentile', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" name="percent_from" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr('Minimum Percentile', 'tutor-pro'); ?>" required />
						</div>
					</div>
				</div>
				<div class="tutor-row tutor-mx-0">
					<div class="tutor-col-sm-6">
						<label class="tutor-form-label">
							<?php esc_html_e('Color', 'tutor-pro'); ?>
						</label>
						<div class="tutor-input-group tutor-mb-4">
							<input type="text" class="tutor_colorpicker" id="tutor-update-grade-color" name="grade_config[grade_color]" value="">
						</div>
					</div>
				</div>
				<?php do_action('tutor_update_existing_grade_form_fields_after'); ?>
				<div class="tutor-row tutor-mx-0" id="tutor-update-grade-form-response"></div>
			</div>

			<div class="tutor-modal-footer">
				<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
					<?php esc_html_e('Cancel', 'tutor-pro'); ?>
				</button>
				<button type="submit" class="tutor-btn tutor-btn-primary" data-tutor-modal-submit>
					<?php esc_html_e('Update Grade', 'tutor-pro'); ?>
				</button>
			</div>
		</form>
	</div>
</div>