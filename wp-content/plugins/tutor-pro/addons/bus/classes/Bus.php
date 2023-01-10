<?php
/**
 * Tutor Multi Instructor
 */

namespace TUTOR_BUS;

use TUTOR\Backend_Page_Trait;

class Bus {

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */

	use Backend_Page_Trait;
	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'tutor_admin_register', array( $this, 'register_menu' ) );

		add_action( 'wp_ajax_add_new_bus', array( $this, 'add_new_bus' ) );
		add_action( 'wp_ajax_update_bus', array( $this, 'update_bus' ) );
		add_action( 'tutor_action_delete_bus', array( $this, 'delete_bus' ) );

		add_action( 'tutor_quiz/attempt_ended', array( $this, 'quiz_attempt_ended' ) );
		add_filter( 'tutor_assignment/single/results/after', array( $this, 'filter_assignment_result' ), 10, 3 );
		add_filter( 'tutor_course/single/nav_items', array( $this, 'add_course_nav_item' ), 10, 2 );

		add_action( 'tutor_action_bus_result_list_bulk_actions', array( $this, 'bus_result_list_bulk_actions' ), 10, 0 );
		add_action( 'delete_tutor_course_progress', array( $this, 'delete_bus_on_retake' ), 11, 2 );

		// Install Sample Grade Data
		add_action( 'wp_ajax_import_bus_sample_data', array( $this, 'import_bus_sample_data' ) );

		// Provide final bus
		add_action( 'tutor_bus_get_final_stats', array( $this, 'final_bus' ), 10, 2 );

		// Generate bus on various events
		add_action( 'tutor_quiz/attempt_ended', array( $this, 'bus_generator_wrapper' ), 10, 3 );
		add_action( 'tutor_assignment/evaluate/after', array( $this, 'generate_grade' ) );
		add_action( 'tutor_assignment/evaluate/after', array( $this, 'bus_generator_wrapper' ), 10, 3 );
		add_action( 'tutor_quiz/answer/review/after', array( $this, 'bus_generator_wrapper' ), 10, 3 );
		add_action( 'delete_tutor_course_progress', array( $this, 'bus_generate' ), 10, 2 );

		$this->page_title = __( 'Bus', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_bus_bulk_action', array( $this, 'bus_bulk_action' ) );
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}

	/**
	 * Load Tab
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public function tabs_key_value( $course_id = '' ): array {
		$bus = get_generated_bus(
			array(
				'course_id' => $course_id,
			)
		);

		$tabs = array(
			array(
				'key'   => 'overview',
				'title' => __( 'Overview', 'tutor-pro' ),
				'value' => is_array( $bus->res ) ? count( $bus->res ) : 0,
				'url'   => '?page=tutor_bus&data=overview',
			),
			array(
				'key'     => 'grade-settings',
				'title'   => __( 'Grade Settings', 'tutor' ),
				'value'   => $this->get_grade_settings_count(),
				'url'     => '?page=tutor_bus&sub_page=buss&data=grade-settings',
			),
		);
		return $tabs;
	}

	public function bus_generator_wrapper( $variable_id, $course_id, $student_id ) {
		$this->bus_generate( $course_id, $student_id );
	}

	public function final_bus( $response, $course_id ) {
		$grade = get_generated_bus( 'final', $course_id );
		return tutor_bus_get_stats( $grade );
	}

	public function admin_scripts( $page ) {
		if ( $page === 'tutor-lms-pro_page_tutor_bus' ) {
			wp_enqueue_script( 'tutor-bus', TUTOR_BUS()->url . 'assets/js/bus.js', array(), TUTOR_BUS()->version, true );
		}
	}

	public function register_menu() {
		add_submenu_page( 'tutor', __( 'Bus', 'tutor-pro' ), __( 'Bus', 'tutor-pro' ), 'manage_tutor', 'tutor_bus', array( $this, 'tutor_bus' ) );
	}

	public function tutor_bus() {
		include TUTOR_BUS()->path . 'views/pages/bus_tut.php';
	}

	public function add_new_bus() {
		global $wpdb;

		// Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters(
			'tutor_bus_required_fields',
			array(
				'grade_name'   => __( 'Grade name field is required', 'tutor-pro' ),
				'percent_from' => __( 'Minimum percentile is required', 'tutor-pro' ),
				'percent_to'   => __( 'Maximum percentile is required', 'tutor-pro' ),
			)
		);

		$validation_errors = array();
		// foreach ( $required_fields as $required_key => $required_value ) {
		// 	if ( empty( $_POST[ $required_key ] ) ) {
		// 		$validation_errors[ $required_key ] = $required_value;
		// 	}
		// }

		if ( tutor_utils()->count( $validation_errors ) ) {
			wp_send_json_error( $validation_errors );
		}

		$percent_from = (int) sanitize_text_field( tutor_utils()->array_get( 'percent_from', $_POST ) );
		$data         = array(
			'grade_name'   => sanitize_text_field( tutor_utils()->array_get( 'grade_name', $_POST ) ),
			'grade_point'  => sanitize_text_field( tutor_utils()->array_get( 'grade_point', $_POST ) ),
			'percent_from' => $percent_from,
			'percent_to'   => sanitize_text_field( tutor_utils()->array_get( 'percent_to', $_POST ) ),
			'grade_config' => maybe_serialize( tutor_utils()->array_get( 'grade_config', $_POST ) ),
		);

		$wpdb->insert( $wpdb->tutor_buss, $data );
		$bus_id = (int) $wpdb->insert_id;
		if ( $bus_id ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		exit();
	}

	public function update_bus() {
		global $wpdb;
		// Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters(
			'tutor_bus_required_fields',
			array(
				'grade_name'   => __( 'Grade name field is required', 'tutor-pro' ),
				'percent_from' => __( 'Minimum grade percentile is required', 'tutor-pro' ),
				'percent_to'   => __( 'Maximum grade percentile is required', 'tutor-pro' ),
			)
		);

		$validation_errors = array();
		// foreach ( $required_fields as $required_key => $required_value ) {
		// 	if ( empty( $_POST[ $required_key ] ) ) {
		// 		$validation_errors[ $required_key ] = $required_value;
		// 	}
		// }

		if ( tutor_utils()->count( $validation_errors ) ) {
			wp_send_json_error( $validation_errors );
			exit();
		}

		$bus_id = sanitize_text_field( $_POST['bus_id'] );
		$percent_from = (int) sanitize_text_field( tutor_utils()->array_get( 'percent_from', $_POST ) );
		$data         = array(
			'grade_name'   => sanitize_text_field( tutor_utils()->array_get( 'grade_name', $_POST ) ),
			'grade_point'  => sanitize_text_field( tutor_utils()->array_get( 'grade_point', $_POST ) ),
			'percent_from' => $percent_from,
			'percent_to'   => sanitize_text_field( tutor_utils()->array_get( 'percent_to', $_POST ) ),
			'grade_config' => maybe_serialize( tutor_utils()->array_get( 'grade_config', $_POST ) ),
		);

		$update = $wpdb->update( $wpdb->tutor_buss, $data, array( 'bus_id' => $bus_id ) );
		if ( $update ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		exit();
	}

	public function delete_bus() {
		global $wpdb;
		$bus_id = (int) sanitize_text_field( tutor_utils()->array_get( 'bus_id', $_GET ) );
		$wpdb->delete( $wpdb->tutor_buss, array( 'bus_id' => $bus_id ) );

		tutor_flash_set( 'success', __( 'The grade has been deleted successfully.', 'tutor-pro' ) );
		wp_redirect( admin_url( 'admin.php?page=tutor_bus&sub_page=buss' ) );
		exit();
	}
	
	private function get_grade_settings_count() {
		global $wpdb;

		$settings_count = $wpdb->get_var(
			"SELECT COUNT(*) AS settings_count 
			FROM {$wpdb->tutor_buss}"
		);

		return $settings_count ? $settings_count : 0;
	}

	private function get_max_gradepoint(){
		global $wpdb;

		$point = $wpdb->get_var(
			"SELECT grade_point 
			FROM {$wpdb->tutor_buss} 
			GROUP BY percent_to 
			HAVING MAX(percent_to) 
			ORDER BY percent_to DESC LIMIT 1"
		);

		return $point ? $point : 0;
	}

	/**
	 * @param $attempt_id
	 *
	 * Generate Quiz Result
	 * @since v.1.4.2
	 */

	public function quiz_attempt_ended( $attempt_id ) {
		global $wpdb;

		$attempt           = tutor_utils()->get_attempt( $attempt_id );
		$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;

		$bus = $wpdb->get_row(
			"SELECT * FROM {$wpdb->tutor_buss} 
			WHERE percent_from <= {$earned_percentage} 
				AND percent_to >= {$earned_percentage} 
			ORDER BY bus_id ASC LIMIT 1"
		);

		if ( ! $bus ) {
			return;
		}

		$bus_data = array(
			'user_id'            => $attempt->user_id,
			'course_id'          => $attempt->course_id,
			'quiz_id'            => $attempt->quiz_id,
			'bus_id'       => $bus->bus_id,
			'result_for'         => 'quiz',
			'grade_name'         => $bus->grade_name,
			'grade_point'        => $this->get_max_gradepoint(),
			'earned_grade_point' => $bus->grade_point,
			'generate_date'      => date( 'Y-m-d H:i:s' ),
			'update_date'        => date( 'Y-m-d H:i:s' ),
		);

		$bus_result_id = 0;
		$bus_result    = $wpdb->get_row(
			"SELECT * FROM {$wpdb->tutor_buss_results} 
			WHERE result_for = 'quiz' 
			AND user_id = {$attempt->user_id} 
			AND course_id = {$attempt->course_id} 
			AND quiz_id = {$attempt->quiz_id} "
		);

		if ( $bus_result ) {
			$bus_result_id = $bus_result->bus_result_id;
			// Update Bus Result
			unset( $bus_data['generate_date'] );
			$wpdb->update( $wpdb->tutor_buss_results, $bus_data, array( 'bus_result_id' => $bus_result->bus_result_id ) );
		} else {
			$wpdb->insert( $wpdb->tutor_buss_results, $bus_data );
			$bus_result_id = (int) $wpdb->insert_id;
		}

		do_action( 'tutor_bus/quiz_result/after', $bus_result_id );
	}

	public function generate_grade( $submitted_id ) {
		global $wpdb;

		do_action( 'tutor_bus/assignment_generate/before', $submitted_id );
		do_action( 'tutor_bus/generate/before' );

		$submitted_info = tutor_utils()->get_assignment_submit_info( $submitted_id );
		if ( $submitted_info ) {
			$max_mark   = tutor_utils()->get_assignment_option( $submitted_info->comment_post_ID, 'total_mark', 10 );
			$given_mark = get_comment_meta( $submitted_id, 'assignment_mark', true );

			$earned_percentage = ( $given_mark > 0 ) ? ( number_format( ( $given_mark * 100 ) / $max_mark ) ) : 0;

			$bus = $wpdb->get_row(
				"SELECT * FROM {$wpdb->tutor_buss} 
			WHERE percent_from <= {$earned_percentage} 
			AND percent_to >= {$earned_percentage} ORDER BY bus_id ASC LIMIT 1  "
			);

			$bus_data = apply_filters(
				'tutor_bus_data',
				array(
					'user_id'            => $submitted_info->user_id,
					'course_id'          => $submitted_info->comment_parent,
					'assignment_id'      => $submitted_info->comment_post_ID,
					'bus_id'       => $bus->bus_id,
					'result_for'         => 'assignment',
					'grade_name'         => $bus->grade_name,
					'grade_point'        => $this->get_max_gradepoint(),
					'earned_grade_point' => $bus->grade_point,
					'earned_percent' 	 => $earned_percentage,
					'generate_date'      => date( 'Y-m-d H:i:s' ),
					'update_date'        => date( 'Y-m-d H:i:s' ),
				)
			);

			$bus_result_id = 0;
			$bus_result    = $wpdb->get_row(
				"SELECT * FROM {$wpdb->tutor_buss_results} 
				WHERE result_for = 'assignment' 
					AND user_id = {$submitted_info->user_id} 
					AND course_id = {$submitted_info->comment_parent} 
					AND assignment_id = {$submitted_info->comment_post_ID} "
			);

			if ( $bus_result ) {
				$bus_result_id = (int) $bus_result->bus_result_id;
				// Update Bus Result
				unset( $bus_data['generate_date'] );
				$wpdb->update( 
					$wpdb->tutor_buss_results, 
					$bus_data, 
					array( 'bus_result_id' => $bus_result->bus_result_id ) 
				);
			} else {
				$wpdb->insert( $wpdb->tutor_buss_results, $bus_data );
				$bus_result_id = (int) $wpdb->insert_id;
			}

			do_action( 'tutor_bus/assignment_generate/after', $bus_result_id );
			do_action( 'tutor_bus/generate/after', $bus_result_id );
		}
	}

	public function filter_assignment_result( $content, $submit_id, $assignment_id ) {

		$max_mark   = tutor_utils()->get_assignment_option( $assignment_id, 'total_mark' );
		$pass_mark  = tutor_utils()->get_assignment_option( $assignment_id, 'pass_mark' );
		$given_mark = get_comment_meta( $submit_id, 'assignment_mark', true );
		$grade      = get_generated_bus( 'assignment', $assignment_id );

		ob_start();
		?>

		<div class="assignment-result-wrap">
			<h4><?php echo sprintf( __( 'You received %1$s points out of %2$s', 'tutor-pro' ), "<span class='received-marks'>{$given_mark}</span>", "<span class='out-of-marks'>{$max_mark}</span>" ); ?></h4>
			<h4 class="submitted-assignment-grade">
				<?php
				_e( 'Your grade is ', 'tutor-pro' );

				echo tutor_generate_grade_html( $grade );
				echo $given_mark >= $pass_mark ? "<span class='submitted-assignment-grade-pass'> (" . __( 'Passed', 'tutor-pro' ) . ')</span>' : "<span class='submitted-assignment-grade-failed'> (" . __( 'Failed', 'tutor-pro' ) . ')</span>';
				?>
			</h4>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * @param $course_ID
	 * @param int       $user_id
	 *
	 * Generate / update bus result by course id and user id
	 */

	public function bus_generate( $course_ID, $user_id ) {
		global $wpdb;

		$course_contents = tutor_utils()->get_course_contents_by_id( $course_ID );
		$previous_gen_item = get_generated_bus( 'all', $course_ID );

		$require_gradding = array();
		
		// Prepare the posts that requires grading
		if ( tutor_utils()->count( $course_contents ) ) {
			foreach ( $course_contents as $content ) {
				if ( $content->post_type === 'tutor_quiz' || $content->post_type === 'tutor_assignments' ) {
					$require_gradding[] = $content;
				}
			}
		}

		/**
		 * Delete if not exists
		 */
		if ( tutor_utils()->count( $previous_gen_item ) ) {
			$quiz_assignment_ids = wp_list_pluck( $require_gradding, 'ID' );

			if ( tutor_utils()->count( $quiz_assignment_ids ) ) {

				foreach ( $previous_gen_item as $previous_item ) {
					if ( $previous_item->quiz_id && ! in_array( $previous_item->quiz_id, $quiz_assignment_ids ) ) {
						$wpdb->delete( $wpdb->tutor_buss_results, array( 'quiz_id' => $previous_item->quiz_id ) );
					}
					if ( $previous_item->assignment_id && ! in_array( $previous_item->assignment_id, $quiz_assignment_ids ) ) {
						$wpdb->delete( $wpdb->tutor_buss_results, array( 'assignment_id' => $previous_item->assignment_id ) );
					}
				}
			} else {
				$wpdb->delete( $wpdb->tutor_buss_results, array( 'course_id' => $course_ID ) );
			}
		}

		// Check if there is anything to generate grade for
		if ( ! tutor_utils()->count( $require_gradding ) ) {
			return;
		}

		// Regenerate grading
		if ( tutor_utils()->count( $require_gradding ) ) {

			// Strip array indexes
			$require_graddings = array_values( $require_gradding );

			// Loop through posts that needs grading
			foreach ( $require_graddings as $course_item ) {
				$earned_percentage = 'pending';

				if ( $course_item->post_type === 'tutor_quiz' ) {
					// Grading for quiz
					// Get Attempt by grading method
					$attempt = tutor_utils()->get_quiz_attempt( $course_item->ID, $user_id );
					if ( $attempt ) {
						$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
					}
				} elseif ( $course_item->post_type === 'tutor_assignments' ) {
					// Grading for assignment
					$submitted_info = tutor_utils()->is_assignment_submitted( $course_item->ID, $user_id );
					if ( ! $submitted_info || ! get_post_meta( $submitted_info->comment_ID, 'evaluate_time', true ) ) {
						// Skip if the assignment is not yet evaluated
						continue;
					}
					if ( $submitted_info ) {
						$submitted_id      = $submitted_info->comment_ID;
						$max_mark          = tutor_utils()->get_assignment_option( $submitted_info->comment_post_ID, 'total_mark', 10 );

						$given_mark        = get_comment_meta( $submitted_id, 'assignment_mark', true );
						if ( $given_mark ) {
							$earned_percentage = $given_mark > 0 ? ( number_format( ( $given_mark * 100 ) / $max_mark ) ) : 0;
						}
					}
				}

				if ( $earned_percentage > 100 ) {
					$earned_percentage = 100;
				}

				if ( 'pending' === $earned_percentage ) {
					$bus = false;
				} else {
					$bus = $wpdb->get_row( "SELECT * FROM {$wpdb->tutor_buss} WHERE percent_from <= {$earned_percentage} AND percent_to >= {$earned_percentage} ORDER BY bus_id ASC LIMIT 1  " );
				}

				if ( ! $bus ) {
					continue;
				}

				$bus_data = array(
					'user_id'            => $user_id,
					'course_id'          => $course_ID,
					'bus_id'       => $bus->bus_id,
					'grade_name'         => $bus->grade_name,
					'grade_point'        => $this->get_max_gradepoint(),
					'earned_grade_point' => $bus->grade_point,
					'earned_percent'     => $earned_percentage,
					'generate_date'      => date( 'Y-m-d H:i:s' ),
					'update_date'        => date( 'Y-m-d H:i:s' ),
				);
				$bus_result = false;

				if ( $course_item->post_type === 'tutor_quiz' ) {
					$bus_data['quiz_id']    = $course_item->ID;
					$bus_data['result_for'] = 'quiz';

					$bus_result = $wpdb->get_row(
						"SELECT * FROM {$wpdb->tutor_buss_results} 
							WHERE result_for = 'quiz' 
							AND user_id = {$user_id} 
							AND course_id = {$course_ID} 
							AND quiz_id = {$course_item->ID} "
					);

				} elseif ( $course_item->post_type === 'tutor_assignments' ) {
					$bus_data['assignment_id'] = $course_item->ID;
					$bus_data['result_for']    = 'assignment';

					$bus_result = $wpdb->get_row(
						"SELECT * FROM {$wpdb->tutor_buss_results} 
							WHERE result_for = 'assignment' 
							AND user_id = {$user_id} 
							AND course_id = {$course_ID} 
							AND assignment_id = {$course_item->ID} "
					);
				}

				if ( $bus_result ) {
					// Update Bus Result
					unset( $bus_data['generate_date'] );
					$wpdb->update( $wpdb->tutor_buss_results, $bus_data, array( 'bus_result_id' => $bus_result->bus_result_id ) );
				} else {
					$wpdb->insert( $wpdb->tutor_buss_results, $bus_data );
				}
			}

			$results = $wpdb->get_row(
				"SELECT AVG(earned_percent) as earned_percent,
                AVG(earned_grade_point) as earned_grade_point
                FROM {$wpdb->tutor_buss_results} 
                WHERE user_id = {$user_id} 
                AND course_id = {$course_ID} 
                AND result_for !='final' "
			);

			if ( $results && $bus = get_bus_by_percent( $results->earned_percent ) ) {
				$bus_data = array(
					'user_id'            => $user_id,
					'course_id'          => $course_ID,
					'bus_id'       => $bus->bus_id,
					'result_for'         => 'final',
					'grade_name'         => $bus->grade_name,
					'grade_point'        => $this->get_max_gradepoint(),
					'earned_grade_point' => number_format( $results->earned_grade_point, 2 ),
					'earned_percent'     => $results->earned_percent,
					'generate_date'      => date( 'Y-m-d H:i:s' ),
					'update_date'        => date( 'Y-m-d H:i:s' ),
				);

				$generated_final = $wpdb->get_row(
					"SELECT * FROM {$wpdb->tutor_buss_results} 
                    WHERE user_id = {$user_id} AND course_id = {$course_ID} AND result_for ='final' "
				);

				if ( $generated_final ) {
					unset( $bus_data['generate_date'], $bus_data['result_for'] );

					$wpdb->update( 
						$wpdb->tutor_buss_results, 
						$bus_data, 
						array( 'bus_result_id' => $generated_final->bus_result_id ) 
					);
				} else {
					$wpdb->insert( $wpdb->tutor_buss_results, $bus_data );
				}
			}
		}
	}

	public function generate_bus_html($course_id){
		require dirname(__DIR__) . '/views/bus.php';
	}

	/**
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * Get Grade percent from quiz base on settings...
	 */
	public function get_quiz_earned_number_percent( $quiz_id = 0, $user_id = 0 ) {
		$quiz_grade_method = get_tutor_option( 'quiz_grade_method' );
		echo $quiz_grade_method;
	}

	public function add_course_nav_item($items, $course_id){
		if (is_single() && get_the_ID()){
			$gading_content = get_grading_contents_by_course_id();

			if (tutor_utils()->count($gading_content)){
				$items['bus'] = array(
					'title' => __('Bus', 'tutor-pro'), 
					'method' => array($this, 'generate_bus_html'),
					'require_enrolment' => true
				);
			}
		}

		return $items;
	}

	public function bus_result_list_bulk_actions() {
		tutor_utils()->checking_nonce( 'get' );

		$action = sanitize_text_field( tutor_utils()->array_get( 'action', $_GET ) );
		if ( $action === '-1' ) {
			return;
		}

		global $wpdb;
		$bus_result_ids = tutor_utils()->array_get( 'buss_result_ids', $_GET );

		if ( $action === 'regenerate_bus' ) {
			if ( tutor_utils()->count( $bus_result_ids ) ) {
				foreach ( $bus_result_ids as $result_id ) {
					$result = get_generated_bus( 'byID', $result_id );
					$this->bus_generate( $result->course_id, $result->user_id );
				}

				tutor_flash_set( 'success', __( 'Bus has been re-generated', 'tutor-pro' ) );
			}
		}

		if ( $action === 'trash' ) {
			if ( tutor_utils()->count( $bus_result_ids ) ) {
				foreach ( $bus_result_ids as $result_id ) {
					$result = get_generated_bus( 'byID', $result_id );
					$wpdb->delete(
						$wpdb->tutor_buss_results,
						array(
							'user_id'   => $result->user_id,
							'course_id' => $result->course_id,
						)
					);
				}
				tutor_flash_set( 'warning', __( 'Bus has been deleted', 'tutor-pro' ) );
			}
		}

		wp_redirect( tutor_utils()->referer() );
		exit();
	}

	public function delete_bus_on_retake( $course_id, $user_id ) {
		global $wpdb;
		$wpdb->delete(
			$wpdb->tutor_buss_results,
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
			)
		);
	}

	/**
	 * Import Sample Grade Data
	 */
	public function import_bus_sample_data() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$data = "INSERT INTO {$wpdb->tutor_buss} (grade_name, grade_point, grade_point_to, percent_from, percent_to, grade_config) VALUES
                ('A+', '4.0', NULL, 90, 100, 'a:1:{s:11:\"grade_color\";s:7:\"#27ae60\";}'),
                ('A', '3.50', NULL, 80, 89, 'a:1:{s:11:\"grade_color\";s:7:\"#1bbc9b\";}'),
                ('A-', '3.0', NULL, 70, 79, 'a:1:{s:11:\"grade_color\";s:7:\"#43bca4\";}'),
                ('B+', '2.50', NULL, 60, 69, 'a:1:{s:11:\"grade_color\";s:7:\"#1f3a93\";}'),
                ('B', '2.0', NULL, 50, 59, 'a:1:{s:11:\"grade_color\";s:7:\"#2574a9\";}'),
                ('B-', '1.5', NULL, 40, 49, 'a:1:{s:11:\"grade_color\";s:7:\"#19b5fe\";}'),
                ('C', '1.0', NULL, 30, 39, 'a:1:{s:11:\"grade_color\";s:7:\"#9a13b3\";}'),
                ('F', '0.0', NULL, 0, 29, 'a:1:{s:11:\"grade_color\";s:7:\"#d71830\";}');";

		$wpdb->query( $data );
		wp_send_json_success();
	}

}
