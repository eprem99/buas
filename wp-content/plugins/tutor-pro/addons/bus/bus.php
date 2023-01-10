<?php
/*
Plugin Name: Tutor Bus
Plugin URI: https://www.themeum.com/product/tutor-bus
Description: Shows student progress from assignment and quiz
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-bus
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_BUS_VERSION', '1.0.0');
define('TUTOR_BUS_FILE', __FILE__);

/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_bus_config');
function tutor_bus_config($config){
	$newConfig = array(
		'name'          => __('Bus', 'tutor-multi-instructors'),
		'description'   => __('Shows student progress from assignment and quiz', 'tutor-pro'),
	);
	$basicConfig = (array) TUTOR_BUS();
	$newConfig = array_merge($newConfig, $basicConfig);

	$config[plugin_basename( TUTOR_BUS_FILE )] = $newConfig;
	return $config;
}

if ( ! function_exists('TUTOR_BUS')) {
	function TUTOR_BUS() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_BUS_FILE ),
			'url'               => plugin_dir_url( TUTOR_BUS_FILE ),
			'basename'          => plugin_basename( TUTOR_BUS_FILE ),
			'version'           => TUTOR_BUS_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_BUS\init();
$tutor->run();